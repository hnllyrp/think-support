<?php
declare (strict_types=1);

namespace hnllyrp\think\console;

use hnllyrp\think\console\migration\ThinkPHPMigration;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\helper\Str;


class MakeMigrationCommand extends Command
{
    protected function configure()
    {
        // 基于现有数据库 生成 migrate文件
        $this->setName('yrp:make:migration')
            ->setDescription('Batch Create migration files, Based on existing database');
    }

    protected function execute(Input $input, Output $output)
    {
        $migrationsPath = $this->app->getRootPath() . '/database/migrations/';

        if (!is_dir($migrationsPath)) {
            if (!mkdir($migrationsPath, 0777, true) && !is_dir($migrationsPath)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $migrationsPath));
            }
        }

        $migrateGenerator = new ThinkPHPMigration();

        $prefix = $migrateGenerator->getPrefix();

        $tables = $migrateGenerator->getAllTables();

        foreach ($tables as $key => $table) {

            // Compute the file path
            $table_name = str_replace($prefix, '', $table->getName());

            // Do not export the ignored tables
            if (in_array($table_name, ['migrations'])) {
                continue;
            }

            $className = ucfirst(Str::camel('create_' . $table_name . '_table'));

            $fileName = self::mapClassNameToFileName($className, $key);
            $filePath = $migrationsPath . $fileName;

            $table_out = $migrateGenerator->setTable($table)->setPrefix($prefix)->setClassName($className);

            file_put_contents($filePath, $table_out->output());

            $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', realpath($filePath)));
        }
    }


    /**
     * Turn migration names like 'CreateUserTable' into file names like
     * '12345678901234_create_user_table.php' or 'LimitResourceNamesTo30Chars' into
     * '12345678901234_limit_resource_names_to_30_chars.php'.
     *
     * @param string $className Class Name
     * @param int $key
     * @return string
     */
    protected function mapClassNameToFileName($className, $key = 0)
    {
        $arr = preg_split('/(?=[A-Z])/', $className);
        unset($arr[0]); // remove the first element ('')
        return $this->getDatePrefix($key) . '_' . strtolower(implode('_', $arr)) . '.php';
    }

    /**
     * Gets the current timestamp string, in UTC.
     *
     * @return string
     */
    protected static function getCurrentTimestamp()
    {
        $dt = new \DateTime('now');
        return $dt->format('YmdHis');
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix($key)
    {
        return date('YmdHis') + ($key + 1);
    }


}
