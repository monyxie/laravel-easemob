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
 * Sends HTTP requests and handles authentication
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

        $this->cache = $cache;

        $this->baseUri = sprintf('%s/%s/%s/', $config['domain_name'], $config['org_name'], $config['app_name']);
    }

    /**
     * @param $uri
     * @param $params
     * @return string|array
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
     * @return string|array
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri, array $options)
    {
        try {
            $response = $this->getClient()->request($method, $uri, $options);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            if (!$response) {
                throw new EasemobException('请求失败！', -1, $e);
            }
        }

        $status = $response->getStatusCode();

        $result = $this->parseBody($response, $status);

        if ($status !== 200) {
            $message = '请求错误！';
            if (is_array($result)) {
                $message .= ($result['error'] ?? '') . ' ' . ($result['error_description'] ?? '');
            }
            throw new EasemobException($message, $status);
        }

        return $result;
    }

    /**
     * @return Client
     */
    protected function getClient(): Client
    {
        if (!$this->client) {
            $stack = HandlerStack::create();
            $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
                return $request->withHeader('Authorization', 'Bearer ' . $this->getToken());
            }));
            $stack->push(Middleware::retry(function (
                $retries,
                RequestInterface $req,
                ResponseInterface $res) {
                $statusCode = $res->getStatusCode();

                $isServerError = $statusCode >= 500 && $statusCode <= 599;
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
                'body' => json_encode([
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                ])
            ];
            $response = $client->post('token', $option);

            $data = json_decode($response->getBody()->getContents(), true);
            $token = $data['access_token'];

            try {
                $this->cache->set(self::CACHE_KEY_ACCESS_TOKEN, $token, intval($data['expires_in']));
            } catch (InvalidArgumentException $e) {
                // ignore
            }

        }

        return $token;
    }

    /**
     * @param $uri
     * @param $params
     * @return string|array
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($uri, $params)
    {
        return $this->request('POST', $uri, ['body' => json_encode($params)]);
    }

    /**
     * @param $uri
     * @param $params
     * @return string|array
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put($uri, $params)
    {
        return $this->request('PUT', $uri, ['body' => json_encode($params)]);
    }

    /**
     * @param $uri
     * @param $params
     * @return string|array
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($uri, $params)
    {
        return $this->request('DELETE', $uri, ['body' => json_encode($params)]);
    }

    /**
     * @param ResponseInterface $response
     * @param int $status
     * @return mixed|string|null
     * @throws EasemobException
     */
    private function parseBody(ResponseInterface $response, int $status)
    {
        $result = null;
        $body = $response->getBody()->getContents();
        if ($response->hasHeader('Content-Type')) {
            $contentType = strtolower($response->getHeader('Content-Type')[0] ?? '');
            if (substr($contentType, 0, 16) === 'application/json') {
                $result = json_decode($body, true);
                if ($result === null) {
                    $message = '请求错误！返回body不是有效的json：' . $body;
                    throw new EasemobException($message, $status);
                }
            } else {
                $result = $body;
            }
        }
        return $result;
    }
}