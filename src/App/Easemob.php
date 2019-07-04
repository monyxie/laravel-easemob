<?php

namespace link1st\Easemob\App;

use link1st\Easemob\App\Http\ApiClient;
use Psr\SimpleCache\CacheInterface;

class Easemob
{
    // 目标数组 用户，群，聊天室
    public $sendTargets = ['users', 'chatgroups', 'chatrooms'];

    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var ApiClient
     */
    private $client;
    /**
     * @var string
     */
    private $url;

    /***********************   发送消息   **********************************/
    use EasemobMessages;

    /***********************   群管理   **********************************/
    use EasemobGroups;

    /***********************   聊天室管理   **********************************/
    use EasemobRooms;

    /***********************   文件管理   **********************************/
    use EasemobChatfiles;

    /***********************   用户管理   **********************************/
    use EasemobUsers;

    public function __construct(array $config, CacheInterface $cache)
    {
        $this->url = sprintf('%s/%s/%s/', $config['domain_name'], $config['org_name'], $config['app_name']);

        $this->client = new ApiClient($config, $cache);
    }
}