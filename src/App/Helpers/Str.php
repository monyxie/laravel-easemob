<?php


namespace link1st\Easemob\App\Helpers;


class Str
{
    /**
     * 字符串替换
     *
     * @param $string
     *
     * @return mixed
     */
    public static function stringReplace($string)
    {
        $string = str_replace('\\', '', $string);
        $string = str_replace(' ', '+', $string);

        return $string;
    }
}