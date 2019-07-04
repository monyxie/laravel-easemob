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
 * Trait EasemobChatfiles
 * @package link1st\Easemob\App
 * @property ApiClient client
 */
trait EasemobChatfiles
{

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