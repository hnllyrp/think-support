<?php


namespace hnllyrp\think\support;

use think\helper\Str as Base;

class Str extends Base
{
    /**
     * json to array
     * @param string $str
     * @return mixed
     */
    public static function jsonToArr(string $str = '')
    {
        $arr = json_decode($str, true);
        return is_null($arr) ? $str : $arr;
    }

    /**
     * array to json
     * @param array $arr
     * @return false|string
     */
    public static function arrToJson(array $arr = [])
    {
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param string|array $pattern
     * @param string $value
     * @return bool
     */
    public static function is($pattern, $value)
    {
        $patterns = Arr::wrap($pattern);

        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern == $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }
}
