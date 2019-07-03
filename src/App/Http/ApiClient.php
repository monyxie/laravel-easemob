<?php
/**
 * Created by PhpStorm.
 * User: link
 * Date: 2016/12/6
 * Time: 14:56
 */

namespace link1st\Easemob\App\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use link1st\Easemob\App\Exceptions\EasemobException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Http请求类
 * Class Http
 * @package link1st\Easemob
 */
class ApiClient
{
    /**
     * 缓存的名称
     */
    const CACHE_KEY_ACCESS_TOKEN = 'easemob_access_token';

    /**
     * @var mixed
     */
    private $client_id;
    /**
     * @var mixed
     */
    private $client_secret;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var mixed
     */
    private $token_cache_time;
    /**
     * @var mixed
     */
    private $domain_name;
    /**
     * @var mixed
     */
    private $org_name;
    /**
     * @var mixed
     */
    private $app_name;
    /**
     * @var string
     */
    private $baseUri;
    /**
     * @var
     */
    private $retries = 3;

    /**
     * AuthenticationMiddleware constructor.
     * @param array $config
     * @param CacheInterface $cache
     */
    public function __construct(array $config, CacheInterface $cache)
    {
        $this->domain_name = $config['domain_name'];
        $this->org_name = $config['org_name'];
        $this->app_name = $config['app_name'];
        $this->client_id = $config['client_id'];
        $this->client_secret = $config['client_secret'];
        $this->token_cache_time = $config['token_cache_time'];

        $this->cache = $cache;

        $this->baseUri = sprintf('%s/%s/%s/', $config['domain_name'], $config['org_name'], $config['app_name']);
    }

    /**
     * @param $uri
     * @param $params
     * @return mixed|\Psr\Http\Message\StreamInterface|null
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($uri, $params)
    {
        return $this->request('GET', $uri, ['query' => $params]);
    }

    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @return mixed|\Psr\Http\Message\StreamInterface|null
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri, array $options)
    {
        $response = $this->getClient()->request($method, $uri, $options);
        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        if ($status !== 200) {
            $message = '请求错误！' . $body;
            throw new EasemobException($message, $status);
        }

        $result = null;
        if ($response->hasHeader('Content-Type')) {
            $contentType = strtolower($response->getHeader('Content-Type'));
            if (substr($contentType, 0, 16) === 'application/json') {
                try {
                    $result = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                } catch (\Exception $e) {
                    $message = '请求错误！' . $body;
                    throw new EasemobException($message, $status, $e);
                }
            } else {
                $result = $body;
            }
        }

        return $result;
    }

    /**
     * @return Client
     */
    protected function getClient(): Client
    {
        if (!$this->client) {
            $stack = new HandlerStack();
//        $stack->setHandler(new CurlHandler());
            $stack->push(function () {
                return function (callable $handler) {
                    return function (RequestInterface $request, $options) use ($handler) {
                        $request = $request->withHeader('Authorization', 'Bearer ' . $this->getToken());
                        return $handler($request, $options);
                    };
                };
            });
            $stack->push(Middleware::retry(function (
                $retries,
                RequestInterface $req,
                ResponseInterface $res) {
                $statusCode = $res->getStatusCode();

                $isServerError = $statusCode >= 500 || ($statusCode <= 599);
                $isAuthenticationError = $statusCode === 401;
                return $retries < $this->retries && ($isAuthenticationError || $isServerError);
            }));
            $this->client = new Client(['base_uri' => $this->baseUri, 'handler' => $stack]);
        }
        return $this->client;
    }

    /**
     * 获取access token
     *
     * @return mixed
     */
    private function getToken()
    {
        $token = null;
        try {
            if ($this->cache->has(self::CACHE_KEY_ACCESS_TOKEN)) {
                $token = $this->cache->get(self::CACHE_KEY_ACCESS_TOKEN);
            }
        } catch (InvalidArgumentException $e) {
            // ignore
        }

        if (!$token) {

            $client = new Client(['base_uri' => $this->baseUri]);

            $option = [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                ]
            ];
            $response = $client->post('token', $option);

            $data = json_decode($response->getBody());
            $token = $data['access_token'];

            try {
                $this->cache->set(self::CACHE_KEY_ACCESS_TOKEN, $token, intval($response['expires_in']));
            } catch (InvalidArgumentException $e) {
                // ignore
            }

        }

        return $token;
    }

    /**
     * @param $uri
     * @param $params
     * @return mixed|\Psr\Http\Message\StreamInterface|null
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($uri, $params)
    {
        return $this->request('POST', $uri, ['form_params' => $params]);
    }

    /**
     * @param $uri
     * @param $params
     * @return mixed|\Psr\Http\Message\StreamInterface|null
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put($uri, $params)
    {
        return $this->request('PUT', $uri, ['form_params' => $params]);
    }

    /**
     * @param $uri
     * @param $params
     * @return mixed|\Psr\Http\Message\StreamInterface|null
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($uri, $params)
    {
        return $this->request('DELETE', $uri, ['form_params' => $params]);
    }
}