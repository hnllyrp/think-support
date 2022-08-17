<?php
declare (strict_types=1);

namespace hnllyrp\think\console;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

class MakeDocumentCommand extends Command
{
    protected $dir = [];

    protected function configure()
    {
        // 基于现有数据库，生成数据库字典、目录结构
        $this->setName('yrp:make:doc')
            ->addArgument('type', Argument::OPTIONAL, "type:dict、struct", 'dict')
            ->addOption('show_file', 's', Option::VALUE_NONE, 'struct show file')
            ->setDescription('Generate Database Document, Based on existing database');
    }

    protected function execute(Input $input, Output $output)
    {
        $type = trim($input->getArgument('type'));

        // 生成数据库字典 php think app:generate-doc
        if ($type == 'dict') {

            $this->dict();

            // 指令输出
            $output->writeln('generate database document success');
        }

        // 生成目录结构 php think app:generate-doc struct --show_file
        if ($type == 'struct') {
            $show_file = false;
            if ($input->getOption('show_file')) {
                $show_file = true;
            }

            $this->struct($show_file);

            // 指令输出
            $output->writeln('generate struct document success');
        }
    }

    /**
     * 生成数据库字典
     */
    protected function dict()
    {
        $tables = Db::query('show tables');

        $path = root_path('docs/');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $dict_base = 'dict_base/';
        if (!is_dir($path . $dict_base)) {
            mkdir($path . $dict_base, 0755, true);
        }

        $listItem = "# 数据字典\n\n";
        $listItem .= "\n| 数据表 | 描述 |";
        $listItem .= "\n|-------|------|";

        $prefix = env('database.prefix', 'ql_');
        $database = env('database.database', '');

        foreach ($tables as $table) {
            $table = current($table);
            $trueTable = str_replace($prefix, '', $table);

            // 表注释
            $sql = "SELECT * FROM information_schema.tables WHERE table_schema = '" . $database . "' AND TABLE_NAME = '" . $table . "' "; //查询表信息
            $arrTableInfo = Db::query($sql);

            // 各字段信息
            $sql = "SELECT * FROM information_schema.columns WHERE table_schema ='" . $database . "' AND TABLE_NAME = '" . $table . "' "; //查询字段信息
            $arrColumnInfo = Db::query($sql);

            // 索引信息
            $sql = "SHOW INDEX FROM {$table}";
            $rs = Db::query($sql);
            if (count($rs) > 0) {
                $arrIndexInfo = $rs;
            } else {
                $arrIndexInfo = [];
            }

            $item = [
                'TABLE' => json_decode(json_encode($arrTableInfo[0]), true),
                'COLUMN' => json_decode(json_encode($arrColumnInfo), true),
                'INDEX' => $this->getIndexInfo($arrIndexInfo)
            ];

            $content = "# " . $trueTable;
            $content .= "\n\n" . $item['TABLE']['TABLE_COMMENT'] . "\n";

            $content .= "\n| 字段名 | 类型 | 默认值 | 允许非空 | 索引/自增 | 备注(字段数:" . count($item['COLUMN']) . ") |";
            $content .= "\n|-------|:-------:|:-----:|:-------:|:--------:|:------:|";

            foreach ($item['COLUMN'] as $vo) {
                $content .= "\n|" . $vo['COLUMN_NAME'];
                $content .= '|' . $vo['COLUMN_TYPE'];
                $content .= '|' . $vo['COLUMN_DEFAULT'];
                $content .= '|' . $vo['IS_NULLABLE'];
                $content .= '|' . $vo['COLUMN_KEY'] . ' ' . $vo['EXTRA'];
                $content .= '|' . $vo['COLUMN_COMMENT'] . '|';
            }

            if (!empty($item['INDEX'])) {
                $content .= "\n\n##### 索引";
                $content .= "\n\n| 键名 | 字段 |";
                $content .= "\n|-------|--------:|";

                foreach ($item['INDEX'] as $indexName => $indexContent) {
                    $content .= "\n|" . $indexName;
                    $content .= '|' . $indexContent[0] . '|';
                }
            }

            $content .= "\n";

            // 基础表
            $listItem .= "\n| [{$trueTable}](" . $dict_base . "{$trueTable}.md) | {$item['TABLE']['TABLE_COMMENT']} |";

            file_put_contents($path . $dict_base . $trueTable . '.md', $content);
        }

        file_put_contents($path . 'dict.md', $listItem);
    }

    protected function getIndexInfo($arrIndexInfo = [])
    {
        if (empty($arrIndexInfo)) {
            return [];
        }

        $arrIndexInfo = json_decode(json_encode($arrIndexInfo), true);

        $index = [];
        foreach ($arrIndexInfo as $v) {
            $unique = ($v['Non_unique'] == 0) ? '(unique)' : '';
            $index[$v['Key_name']][] = $v['Column_name'] . $unique;
        }

        return $index;
    }


    /**
     * 生成目录结构
     *
     * @return mixed
     */
    protected function struct($show_file = false)
    {
        $path = root_path('docs/');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $this->root_dir();

        $names = array_column($this->dir, 0);
        array_multisort($names, SORT_ASC, $this->dir);

        $content = "# 目录结构\n\n";
        $content .= "| 文件路径 | 目录 | 描述 |\n";
        $content .= "| ------------ | ------------ | ------------ |\n";

        foreach ($this->dir as $item) {

            if ($show_file == true) {
                // 显示目录和文件
                $isDir = is_dir($item[0]) ? '是' : '否';

                $content .= "| " . str_replace(root_path() . '/', '', $item[0]) . " | {$isDir} | - |\n";
            } else {
                // 仅显示目录
                $isDir = is_dir($item[0]) ? 1 : 0;

                if ($isDir) {
                    $content .= "| " . str_replace(root_path() . '/', '', $item[0]) . " | 是 | - |\n";
                }
            }
        }

        file_put_contents($path . 'struct.md', $content);
    }

    /**
     * 生成目录树
     *
     * @param null $path
     * @param int $level
     */
    protected function root_dir($path = null, $level = 0)
    {
        if (is_null($path)) {
            $path = root_path();
        }

        $res = glob($path . '/*');

        // 排除的目录
        $except_tp_path = ['vendor', 'runtime', 'database', 'view', 'public', 'node_modules', 'docs', 'tests'];

        foreach ($res as $re) {
            if (in_array(basename($re), $except_tp_path)) {
                continue;
            }

            if (is_dir($re)) {
                $this->root_dir($re, $level + 1);
            }

            $this->dir[] = [$re, $level];
        }
    }
}
