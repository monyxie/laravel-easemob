<?php
/**
 * Created by PhpStorm.
 * User: link
 * Date: 2016/12/7
 * Time: 13:07
 */

namespace link1st\Easemob\App;

use link1st\Easemob\App\Exceptions\EasemobException;

trait EasemobGroups
{

    /**
     * 获取群信息
     *
     * @param array $group_ids [群id]
     *
     * @return mixed
     */
    public function groups($group_ids)
    {

        $url = 'chatgroups/' . implode(',', $group_ids);
        $option = [];

        return $this->client->get($url, $option);
    }


    /**
     * 创建群
     *
     * @param string $group_name [群名称]
     * @param string $group_description [群描述]
     * @param string $owner_user [群主]
     * @param array $members_users [成员]
     * @param bool $is_public [是否为公开群]
     * @param int $max_user [最大人数]
     * @param bool $is_approval [加群是否要批准]
     *
     * @return mixed
     */
    public function groupCreate(
        $group_name,
        $group_description,
        $owner_user,
        $members_users = [],
        $is_public = true,
        $max_user = 200,
        $is_approval = true)
    {
        $url = 'chatgroups';
        $option = [
            "groupname" => self::stringReplace($group_name),
            "desc" => self::stringReplace($group_description),
            "owner" => $owner_user,
            "public" => $is_public,
            "maxusers" => $max_user,
            "approval" => $is_approval,
        ];
        if (!empty($members_users)) {
            $option['members'] = $members_users;
        }

        return $this->client->post($url, $option);
    }


    /**
     * 修改群信息
     *
     * @param string $group_id
     * @param string $group_name
     * @param string $group_description
     * @param int $max_user
     *
     * @return mixed
     * @throws EasemobException
     */
    public function groupEdit($group_id, $group_name = "", $group_description = "", $max_user = 0)
    {
        $url = 'chatgroups/' . $group_id;
        $option = [
            "groupname" => self::stringReplace($group_name),
            "description" => self::stringReplace($group_description),
            "maxusers" => $max_user,
        ];
        $option = array_filter($option);
        if (empty($option)) {
            throw new EasemobException('提交修改的参数，不修改提交空！');
        }

        return $this->client->put($url, $option);
    }


    /**
     * 删除群
     *
     * @param string $group_id
     *
     * @return mixed
     */
    public function groupDel($group_id)
    {
        $url = 'chatgroups/' . $group_id;
        $option = [];

        return $this->client->delete($url, $option);
    }


    /**
     * 获取所有的群成员
     *
     * @param $group_id
     *
     * @return mixed
     */
    public function groupUsers($group_id)
    {
        $url = 'chatgroups/' . $group_id . '/users';
        $option = [];

        return $this->client->get($url, $option);
    }


    /**
     * 添加群成员——批量
     *
     * @param string $group_id
     * @param array $users [用户名称 数组]
     *
     * @return mixed
     * @throws EasemobException
     */
    public function groupAddUsers($group_id, $users)
    {
        if (count($users) >= 60 || count($users) < 1) {
            throw new EasemobException('一次最多可以添加60位成员,最少为1个');
        }

        $url = 'chatgroups/' . $group_id . '/users';
        $option = [
            'usernames' => $users
        ];

        return $this->client->post($url, $option);
    }


    /**
     * 删除群成员——批量
     * 群主删除 必须先转让群
     *
     * @param string $group_id
     * @param array $users [用户名称 数组]
     *
     * @return mixed
     * @throws EasemobException
     */
    public function groupDelUsers($group_id, $users)
    {
        if (empty($users) || !is_array($users)) {
            throw new EasemobException('删除的用户不存在，或者提交参数不为数组！');
        }

        $url = 'chatgroups/' . $group_id . '/users/' . implode(',', $users);
        $option = [];

        return $this->client->delete($url, $option);
    }


    /**
     * 获取用户所有参加的群
     *
     * @param $user
     *
     * @return mixed
     */
    public function userToGroups($user)
    {
        $url = 'users/' . $user . '/joined_chatgroups';
        $option = [];

        return $this->client->get($url, $option);
    }


    /**
     * 群转让
     *
     * @param $group_id [群Id]
     * @param $new_owner_user [新的群主]
     *
     * @return mixed
     */
    public function groupTransfer($group_id, $new_owner_user)
    {
        $url = 'chatgroups/' . $group_id;
        $option = [
            'newowner' => $new_owner_user
        ];

        return $this->client->put($url, $option);
    }

}