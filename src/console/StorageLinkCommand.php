<?php
declare (strict_types = 1);

namespace hnllyrp\think\console;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;


class StorageLinkCommand extends Command
{
    protected function configure()
    {
        // 生成storage目录 软链至public目录 类似laravel
        $this->setName('storage:link')
            ->setDescription('Create a symbolic link from "public/storage" to "runtime/storage/public"');
    }

    protected function execute(Input $input, Output $output)
    {
        if (file_exists(public_path('storage'))) {
            $output->error('The "public/storage" directory already exists.');
            return;
        }

        if (is_link(public_path('storage'))) {
            $this->app->make('files')->delete(public_path('storage'));
        }

        // 修改 config/filesystem.php disks -> public root =>  app()->getRuntimePath() . 'storage/public'
        if (!file_exists(app()->getRuntimePath() . 'storage/public')) {
            $this->app->make('files')->makeDirectory(app()->getRuntimePath() . 'storage/public');
        }

        $this->app->make('files')->link(
            $this->app->getRuntimePath() . 'storage/public', public_path('storage')
        );

        // 指令输出
        $output->writeln('The [public/storage] directory has been linked.');
    }
}
