<?php

namespace hnllyrp\think\support;

use think\helper\Arr as Base;
use think\helper\Str;

class Arr extends Base
{
    /**
     * 二维数组 按键值条件排除不需要显示的数组
     *
     * @param array $list
     * @param string $except_key
     * @param array $except_value
     * @return array|mixed
     * @example Arr::except_multi(['0' => ['id' => 1, 'name' => 'test']], 'name', ['test']])
     */
    public static function except_multi(array $list = [], string $except_key = '', array $except_value = [])
    {
        if (empty($except_key) || empty($except_value)) {
            return $list;
        }

        $array = [];
        foreach ($list as $key => $item) {
            if (Str::contains($item[$except_key], $except_value)) {
                $array = \think\helper\Arr::except($list, $key);
            }
        }

        return $array;
    }

}
