<?php

namespace hnllyrp\think\console\seed;

use think\facade\Db;
use think\helper\Str;

class ThinkPHPSeeder
{
    /**
     * thanks https://github.com/nWidart/DbExporter
     */

    /**
     * Contains the ignore tables
     * @var array $ignore
     */
    public static $ignore = array('migrations');

    public $prefix = '';

    function __construct()
    {
    }

    public function getPrefix()
    {
        $this->prefix = config_database('mysql', 'prefix');

        return $this->prefix;
    }

    public function getAllTables()
    {
        return Db::connect()->getTables();
    }

    public function getTableData($table = '')
    {
        return Db::name($table)->select();
    }

    public function insertPropertyAndValue($prop, $value)
    {
        $prop = addslashes($prop);
        $value = addslashes($value);
        if (is_numeric($value)) {
            return "                '{$prop}' => {$value},\n";
        } elseif ($value == '') {
            return "                '{$prop}' => '',\n";
        } else {
            return "                '{$prop}' => '{$value}',\n";
        }
    }

    /**
     * @param $tableData
     * @return bool
     */
    public function hasTableData($tableData)
    {
        return count($tableData) >= 1;
    }

    public function compile($stub = '', $tableName = '')
    {
        // Grab the template

        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $this->getSeederStub());

        // Replace the classname
        $template = str_replace('{{className}}', Str::studly($tableName) . "TableSeeder", $template);
        $template = str_replace('{{run}}', $stub, $template);

        return $template;
    }

    public function getSeederStub(): string
    {
        return 'TpSeeder.stub';
    }

}
