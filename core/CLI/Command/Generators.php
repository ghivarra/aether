<?php

declare(strict_types = 1);

namespace Aether\CLI\Command;

class Generators
{
    protected string $masterController = SYSTEMPATH . 'CLI/Command/Master/MasterController.php';
    protected string $masterMiddleware = SYSTEMPATH . 'CLI/Command/Master/MasterMiddleware.php';
    protected string $masterMigration = SYSTEMPATH . 'CLI/Command/Master/MasterMigration.php';
    protected string $masterModel = SYSTEMPATH . 'CLI/Command/Master/MasterModel.php';
    protected string $masterSeeder = SYSTEMPATH . 'CLI/Command/Master/MasterSeeder.php';

    //===========================================================================================

    public function __construct()
    {
        echo "\n\n";
    }

    //===========================================================================================

    private function copy(string $basePath, string $dirPath, string $fileName, string $masterFile): bool
    {
        echo "Preparing File...";

        // check if writable
        if (!is_writable($basePath))
        {
            echo "\n";
            echo "Command Failed, directory is not writable by PHP CLI!";

            // return
            return false;
        }

        // create folder recursively if not exist
        if (!file_exists($dirPath) || !is_dir($dirPath))
        {
            mkdir($dirPath, 0755, true);
        }

        // full path
        $fullPath = "{$dirPath}/{$fileName}.php";

        // check if file already exist
        if (file_exists($fullPath))
        {
            echo "\n";
            echo "Command Failed, file already exist!";

            // return
            return false;
        }

        // create file
        if (!copy($masterFile, $fullPath))
        {
            echo "\n";
            echo "Generating failed, unknown error!";

            // return
            return false;
        }

        // return
        return true;
    }

    //===========================================================================================

    private function modifyContent(array $config = ['path' => '', 'oldNameSpace' => '', 'oldClassName' => '', 'newNameSpace' => '', 'newClassName' => '']): bool
    {
        // write
        $content  = file_get_contents($config['path']);

        // change
        $finalContent = str_replace([$config['oldNameSpace'], $config['oldClassName']], [$config['newNameSpace'], $config['newClassName']], $content);

        // write
        file_put_contents($config['path'], $finalContent);

        // return
        return true;
    }

    //===========================================================================================

    public function makeController(string $basedir, string $fileName): bool
    {
        // copy master
        $base = APPPATH . 'Controller';
        $dir  = ($basedir === '') ? $base : "{$base}/{$basedir}";
        $res  = $this->copy($base, $dir, $fileName, $this->masterController);

        // full
        if (!$res)
        {
            // return
            return false;
        }

        // modify
        $this->modifyContent([
            'path'         => "{$dir}/{$fileName}.php",
            'oldNameSpace' => 'Aether\\CLI\\Command\\Master',
            'oldClassName' => 'MasterController',
            'newNameSpace' => ($basedir === '') ? 'App\\Controller' : 'App\\Controller\\' . str_replace('/', '\\', $basedir),
            'newClassName' => $fileName,
        ]);

        // return
        return true;
    }

    //===========================================================================================

    public function makeMiddleware(string $basedir, string $fileName): bool
    {
        // copy master
        $base = APPPATH . 'Middleware';
        $dir  = ($basedir === '') ? $base : "{$base}/{$basedir}";
        $res  = $this->copy($base, $dir, $fileName, $this->masterMiddleware);

        // full
        if (!$res)
        {
            // return
            return false;
        }

        // modify
        $this->modifyContent([
            'path'         => "{$dir}/{$fileName}.php",
            'oldNameSpace' => 'Aether\\CLI\\Command\\Master',
            'oldClassName' => 'MasterMiddleware',
            'newNameSpace' => ($basedir === '') ? 'App\\Middleware' : 'App\\Middleware\\' . str_replace('/', '\\', $basedir),
            'newClassName' => $fileName,
        ]);

        // return
        return true;
    }

    //===========================================================================================

    public function makeMigration(string $basedir, string $fileName): bool
    {
        return true;
    }

    //===========================================================================================

    public function makeModel(string $basedir, string $fileName): bool
    {
        // copy master
        $base = APPPATH . 'Model';
        $dir  = ($basedir === '') ? $base : "{$base}/{$basedir}";
        $res  = $this->copy($base, $dir, $fileName, $this->masterModel);

        // full
        if (!$res)
        {
            // return
            return false;
        }

        // modify
        $this->modifyContent([
            'path'         => "{$dir}/{$fileName}.php",
            'oldNameSpace' => 'Aether\\CLI\\Command\\Master',
            'oldClassName' => 'MasterModel',
            'newNameSpace' => ($basedir === '') ? 'App\\Model' : 'App\\Model\\' . str_replace('/', '\\', $basedir),
            'newClassName' => $fileName,
        ]);

        // return
        return true;
    }

    //===========================================================================================

    public function makeSeeder(string $basedir, string $fileName): bool
    {
        return true;
    }

    //===========================================================================================
}