<?php

namespace hnllyrp\think;

use hnllyrp\think\console\StorageLinkCommand;
use hnllyrp\think\filesystem\Filesystem;
use think\Service;


class FileSystemService extends Service
{
    public function register()
    {
        $this->registerNativeFilesystem();
    }

    public function boot()
    {
        $this->commands([
            StorageLinkCommand::class,
        ]);
    }

    /**
     * Register the native filesystem implementation.
     *
     * @return void
     */
    protected function registerNativeFilesystem()
    {
        $this->app->bind('files', function () {
            return new Filesystem();
        });
    }
}
