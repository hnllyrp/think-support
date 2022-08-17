<?php
declare (strict_types=1);

namespace hnllyrp\think\console;

use think\console\command\Make;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\facade\Db;
use think\helper\Str;


class MakeModelsCommand extends Make
{
    protected $type = "Model";

    protected function configure()
    {
        // 基于现有数据库 批量生成数据库模型文件 model , 实体 entities (不要修改的)
        $this->setName('yrp:make:models')
            ->addArgument('type', Argument::OPTIONAL, "type:model、entities", 'entities')
            ->setDescription('Batch Create models, Based on existing database');
    }

    protected function execute(Input $input, Output $output)
    {
        $type = trim($input->getArgument('type'));

        $tables = Db::query('show tables');

        $prefix = config_database('mysql', 'prefix');

        foreach ($tables as $table) {
            $table_name = current($table);
            $model_name = Str::studly(str_replace($prefix, '', $table_name));

            // Console::call('make:model', [$model_name]);
            if ($type == 'entities') {
                $this->make_entities($model_name, $table_name);
            } else {
                $this->make_model($model_name);
            }
        }

        // 指令输出
        $output->writeln('batch make ' . $type . ' success');
    }

    /**
     * make entities
     * @param string $model_name
     * @param string $table_name
     */
    protected function make_entities($model_name = '', $table_name = '')
    {
        $classname = $this->getClassNameEntities($model_name);

        $pathname = $this->getPathName($classname);

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        $schemaType = self::getSchemaColumns($table_name);

        file_put_contents($pathname, $this->buildClassEntities($classname, $schemaType));
    }

    /**
     * get filed columns
     * @param string $table
     * @return array|string|string[]|null
     */
    protected static function getSchemaColumns($table = '')
    {
        if (empty($table)) {
            return [];
        }

        $columns = \think\facade\Db::connect()->getFields($table);
        $schema = [];
        foreach ($columns as $key => $column) {
            $schema[$column['name']] = self::transform_field_type($column['type']);
        }

        // 数组原样输出 "array ()"
        $schema = var_export($schema, true);

        // 替换 array () 为 []
        $schema = preg_replace('/\barray \(/', '[', $schema);
        $schema = preg_replace('/\)/', ']', $schema);

        return $schema;
    }

    public static function transform_field_type($column_type = '')
    {
        if (empty($column_type)) {
            return '';
        }

        $column_type = strtolower($column_type);
        $parts = explode('(', $column_type);
        return trim($parts[0]);
    }

    protected function getClassNameEntities(string $name): string
    {
        if (strpos($name, '\\') !== false) {
            return $name;
        }

        if (strpos($name, '@')) {
            [$app, $name] = explode('@', $name);
        } else {
            $app = '';
        }

        if (strpos($name, '/') !== false) {
            $name = str_replace('/', '\\', $name);
        }

        return $this->getNamespaceEntities($app) . '\\' . $name;
    }

    protected function getNamespaceEntities(string $app): string
    {
        return parent::getNamespace($app) . '\\common\\entities';
    }

    protected function buildClassEntities(string $name, $schemaType = '[]')
    {
        $stub = file_get_contents($this->getStubEntities());

        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');

        $class = str_replace($namespace . '\\', '', $name);

        return str_replace(['{%className%}', '{%actionSuffix%}', '{%namespace%}', '{%app_namespace%}', '{%schemaType%}'], [
            $class,
            $this->app->config->get('route.action_suffix'),
            $namespace,
            $this->app->getNamespace(),
            $schemaType,
        ], $stub);
    }

    protected function getStubEntities(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'entities.stub';
    }

    /**
     * make model
     * @param string $model_name
     */
    protected function make_model($model_name = '')
    {
        $classname = $this->getClassName($model_name);

        $pathname = $this->getPathName($classname);

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        file_put_contents($pathname, $this->buildClass($classname));
    }

    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'model.stub';
    }

    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\common\\model';
    }


}
