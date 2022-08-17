<?php

namespace hnllyrp\think;

use hnllyrp\think\console\MakeDocumentCommand;
use hnllyrp\think\console\MakeMigrationCommand;
use hnllyrp\think\console\MakeModelsCommand;
use hnllyrp\think\console\MakeSeederCommand;
use think\Service;

/**
 * Class SupportService
 * @package hnllyrp\think
 */
class SupportService extends Service
{
    public function register()
    {
        // 服务注册

        // 文件上传服务
        $this->app->register(FileSystemService::class);

        // 注册多应用服务提供者
        $services = glob(app_path('/*/*ServiceProvider.php'));
        foreach ($services as $service) {
            $slice = explode('/', $service);
            $module = $slice[count($slice) - 2];

            $this->app->register('app\\' . $module . '\\' . basename($service, '.php'));
        }
    }

    public function boot()
    {
        // 服务启动

        $this->commands([
            MakeDocumentCommand::class,
            MakeMigrationCommand::class,
            MakeModelsCommand::class,
            MakeSeederCommand::class
        ]);

    }
}
