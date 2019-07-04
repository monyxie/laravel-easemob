<?php
/**
 * Created by PhpStorm.
 * User: link
 * Date: 2016/12/7
 * Time: 13:07
 */

namespace link1st\Easemob\App;

use link1st\Easemob\App\Exceptions\EasemobException;
use link1st\Easemob\App\Http\ApiClient;

/**
 * Trait EasemobUsers
 * @package link1st\Easemob\App
 * @property ApiClient client
 */
trait EasemobUsers
{
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

    /**
     * 获取一个IM用户的黑名单
     * @param $owner_username
     *
     * @return string|array
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function showBlockedUsers($owner_username)
    {
        $url = 'users/' . $owner_username . '/block/users';
        $option = [];
        return $this->client->get($url, $option);
    }

    /**
     * 往一个 IM 用户的黑名单中加人
     * @param $owner_username
     * @param $block_usernames
     * @return string|array
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function blockUsers($owner_username, $block_usernames)
    {
        $url = 'users/' . $owner_username . '/block/users';
        $option = [
            'usernames' => $block_usernames
        ];
        return $this->client->post($url, $option);
    }

    /**
     * 将用户从黑名单移除
     * @param string $owner_username
     * @param string $blocked_username
     * @return string|array
     * @throws EasemobException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unblockUser($owner_username, $blocked_username)
    {
        $url = 'users/' . $owner_username . '/block/users/' . $blocked_username;
        $option = [];
        return $this->client->delete($url, $option);
    }
}