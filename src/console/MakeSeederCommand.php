<?php
declare (strict_types=1);

namespace hnllyrp\think\console;

use hnllyrp\think\console\seed\ThinkPHPSeeder;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\helper\Str;


class MakeSeederCommand extends Command
{

    protected function configure()
    {
        // 基于现有数据库 生成 seeder文件
        $this->setName('yrp:make:seeder')
            ->addArgument('table', Argument::OPTIONAL, "table name ", '')
            ->setDescription('Batch Create seeder files, Based on existing database');
    }

    protected function execute(Input $input, Output $output)
    {
        $seedsPath = $this->app->getRootPath() . '/database/seeds/';

        if (!is_dir($seedsPath)) {
            if (!mkdir($seedsPath, 0777, true) && !is_dir($seedsPath)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $seedsPath));
            }
        }

        $table = trim($input->getArgument('table'));

        $seederGenerator = new ThinkPHPSeeder();

        $prefix = $seederGenerator->getPrefix();

        $tables = $seederGenerator->getAllTables();

        if ($table) {
            $only_table = explode(',', $table);

            foreach ($only_table as $key => $value) {
                $only_table[$key] = $prefix . $value;
            }

            $tables = collect($tables)->intersect($only_table)->values()->toArray();
        }

        $stub = "";
        // Loop over the tables
        foreach ($tables as $key => $table) {

            $tableName = str_replace($prefix, '', $table);

            // Do not export the ignored tables
            if (in_array($tableName, $seederGenerator::$ignore)) {
                continue;
            }

            $tableData = $seederGenerator->getTableData($tableName);

            $insertStub = "";
            foreach ($tableData as $obj) {
                $insertStub .= "
            [\n";
                foreach ($obj as $prop => $value) {
                    $insertStub .= $seederGenerator->insertPropertyAndValue($prop, $value);
                }

                if (count($tableData) > 1) {
                    $insertStub .= "            ],";
                } else {
                    $insertStub .= "            ]";
                }
            }

            if ($seederGenerator->hasTableData($tableData)) {
                $stub .= "
        if (Db::name('" . $tableName . "')->count() > 0) {
            return false;
        }

        Db::name('" . $tableName . "')->insert([
            {$insertStub}
        ]);";
            }

            $seed = $seederGenerator->compile($stub, $tableName);

            $fileName = Str::studly($tableName) . 'TableSeeder.php';

            $filePath = $seedsPath . $fileName;

            file_put_contents($filePath, $seed);

            $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', realpath($filePath)));
        }

    }

}
