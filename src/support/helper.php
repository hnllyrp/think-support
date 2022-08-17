<?php

if (!function_exists('config_database')) {
    /**
     * 获取数据库配置
     * @param string $driver
     * @param string $name
     * @return array|mixed|string|null
     */
    function config_database(string $driver = '', string $name = '')
    {
        $database = \think\facade\Config::get('database');

        if ($database) {
            $default = $driver ?: $database['default'] ?? 'mysql';
            $connections = $database['connections'] ?? [];

            if ($name) {
                return $connections[$default][$name] ?? '';
            }

            return $connections[$default] ?? [];
        }

        return $database;
    }
}

if (!function_exists('windows_os')) {
    /**
     * Determine whether the current environment is Windows based.
     *
     * @return bool
     */
    function windows_os()
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}

/**
 * Get the path to the storage folder.
 *
 * @param string $path
 * @return string
 */
function storage_path(string $path = '')
{
    return root_path('runtime') . 'storage' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

/**
 * @param string $path
 * @return string
 */
function storage_public(string $path = '')
{
    return storage_path('public') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

/**
 * @param string $image
 * @return string
 */
function get_image_path(string $image = '')
{
    if (empty($image)) {
        // 默认空图片
        $image = config('shop.no_picture', '');
    } else {
        if (strpos($image, 'http://') === false && strpos($image, 'https://') === false) {
            // oss 图片
            if (config('shop.open_oss') == 1) {
                $image = get_endpoint($image);
            }
        }

        // http or https
        if (strtolower(substr($image, 0, 4)) == 'http') {
            return $image;
        }
    }

    // 默认 disk('public')
    return \think\facade\Filesystem::url($image);
}

/**
 * @param string $image
 * @return string
 */
function get_endpoint(string $image = '')
{
    // TODO oss 图片处理
    $endpoint = '';
    return $endpoint . $image;
}
