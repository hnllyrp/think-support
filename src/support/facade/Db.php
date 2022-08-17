<?php

namespace hnllyrp\think\support\facade;

use think\Facade;

/**
 * @see \think\DbManager
 * @mixin \think\DbManager
 * @method static connect(string $name = null, bool $force = false)
 * @method static raw(string $value)
 * @method static listen(callable $callback)
 * @method static event(string $event, callable $callback)
 * @method static newQuery()
 * @method static table($table)
 * @method static name($name)
 */
class Db extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'think\DbManager';
    }
}
