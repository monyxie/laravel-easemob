<?php

namespace link1st\Easemob\App;

use link1st\Easemob\App\Exceptions\EasemobException;
use link1st\Easemob\App\Http\ApiClient;
use Psr\SimpleCache\CacheInterface;

class Easemob
{
    // 目标数组 用户，群，聊天室
    public $target_array = ['users', 'chatgroups', 'chatrooms'];

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

    public function __construct(array $config, CacheInterface $cache)
    {
        $this->url = sprintf('%s/%s/%s/', $config['domain_name'], $config['org_name'], $config['app_name']);

        $this->client = new ApiClient($config, $cache);
    }

    /***********************   注册   **********************************/

    /**
     * 字符串替换
     *
     * @param $string
     *
     * @return mixed
     */
    protected static function stringReplace($string)
    {
        $string = str_replace('\\', '', $string);
        $string = str_replace(' ', '+', $string);

        return $string;
    }

    /**
     * 开放注册用户
     *
     * @param        $name [用户名]
     * @param string $password [密码]
     * @param string $nick_name [昵称]
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function publicRegistration($name, $password = '', $nick_name = "")
    {
        $option = [
            'username' => $name,
            'password' => $password,
            'nickname' => $nick_name,
        ];

        return $this->client->post('users', $option);
    }

    /**
     * 授权注册用户
     *
     * @param        $name [用户名]
     * @param string $password [密码]
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function authorizationRegistration($name, $password = '123456')
    {
        $url = 'users';
        $option = [
            'username' => $name,
            'password' => $password,
        ];
        return $this->client->post($url, $option);
    }

    /**
     * 授权注册用户——批量
     * 密码不为空
     *
     * @param array $users [用户名 包含 username,password的数组]
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function authorizationRegistrations($users)
    {
        $url = 'users';
        $option = $users;
        return $this->client->post($url, $option);
    }

    /**
     * 获取单个用户
     *
     * @param $user_name
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser($user_name)
    {
        $url = 'users/' . $user_name;
        $option = [];
        return $this->client->get($url, $option);
    }

    /**
     * 获取所有用户
     *
     * @param int $limit [显示条数]
     * @param string $cursor [光标，在此之后的数据]
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserAll($limit = 10, $cursor = '')
    {
        $url = 'users';
        $option = [
            'limit' => $limit,
            'cursor' => $cursor
        ];

        return $this->client->get($url, $option);
    }

    /**
     * 删除用户
     * 删除一个用户会删除以该用户为群主的所有群组和聊天室
     *
     * @param $user_name
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delUser($user_name)
    {
        $url = 'users/' . $user_name;
        $option = [];
        return $this->client->delete($url, $option);
    }

    /**
     * 修改密码
     *
     * @param $user_name
     * @param $new_password [新密码]
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editUserPassword($user_name, $new_password)
    {
        $url = 'users/' . $user_name . '/password';
        $option = [
            'newpassword' => $new_password
        ];
        return $this->client->put($url, $option);
    }


    /***********************   好友操作   **********************************/

    /**
     * 修改用户昵称
     * 只能在后台看到，前端无法看见这个昵称
     *
     * @param $user_name
     * @param $nickname
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editUserNickName($user_name, $nickname)
    {
        $url = 'users/' . $user_name;
        $option = [
            'nickname' => $nickname
        ];
        return $this->client->put($url, $option);
    }

    /**
     * 强制用户下线
     *
     * @param $user_name
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function disconnect($user_name)
    {
        $url = 'users/' . $user_name . '/disconnect';
        $option = [];
        return $this->client->get($url, $option);
    }

    /**
     * 给用户添加好友
     *
     * @param $owner_username [主人]
     * @param $friend_username [朋友]
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addFriend($owner_username, $friend_username)
    {
        $url = 'users/' . $owner_username . '/contacts/users/' . $friend_username;
        $option = [];
        return $this->client->post($url, $option);
    }

    /***********************   文件上传下载   **********************************/

    /**
     * 给用户删除好友
     *
     * @param $owner_username [主人]
     * @param $friend_username [朋友]
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delFriend($owner_username, $friend_username)
    {
        $url = 'users/' . $owner_username . '/contacts/users/' . $friend_username;
        $option = [];
        return $this->client->delete($url, $option);
    }

    /**
     * 查看用户所以好友
     *
     * @param $user_name
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function showFriends($user_name)
    {
        $url = 'users/' . $user_name . '/contacts/users/';
        $option = [];
        return $this->client->get($url, $option);
    }


    /***********************   token操作   **********************************/

    /**
     * 上传文件
     *
     * @param $file_path
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uploadFile($file_path)
    {
        if (!is_file($file_path)) {
            throw new EasemobException('文件不存在', 404);
        }
        $url = 'chatfiles';

        $fp = fopen($file_path, 'r');

        $option = [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $fp
                ],
            ]
        ];

        $result = $this->client->request('POST', $url, $option);

        fclose($fp);

        return $result;
    }

    /**
     * 下载文件
     *
     * @param $uuid [uuid]
     * @param $share_secret [秘钥]
     *
     * @return mixed
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function downloadFile($uuid, $share_secret)
    {
        $url = 'chatfiles/' . $uuid;

        $option = [
            'headers' => [
                'share-secret' => $share_secret
            ]
        ];

        return $this->client->request('GET', $url, $option);
    }

}