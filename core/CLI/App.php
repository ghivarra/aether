<?php 

declare(strict_types = 1);

namespace Aether\CLI;

use Aether\CLI\Command\Cache;
use Aether\CLI\Command\Encryption;
use Aether\CLI\Command\Generators;

/** 
 * The App class to run the CLI in Aether Framework
 * 
 * @class Aether\CLI
**/

class App
{
    public static array $CLIParams = [];
    private array $commandList = [
        'cache:clear',
        'db:seed',
        'migrate:start',
        'migrate:rollback',
        'migrate:refresh',
        'key:generate',
        'make:controller',
        'make:middleware',
        'make:migration',
        'make:model',
        'make:seeder',
    ];

    //=====================================================================================================

    /** 
     * Function to generate the Command Line Text using color
     * 
     * @param string $text
     * @param string $color
     * 
     * @return string
    **/
    private function styleText(string $text, string $colorKey, bool $bold = false): string
    {
        $colors = [
            'red'     => '31',
            'green'   => '32',
            'yellow'  => '33',
            'blue'    => '34',
            'magenta' => '35',
            'cyan'    => '36',
        ];

        $pre   = ($bold) ? "1;" : "";
        $style = "{$pre}{$colors[$colorKey]}m";

        // return
        return "\e[{$style}{$text}\e[0m";
    }

    //=====================================================================================================

    private function printManual(): void
    {
        echo "\n\n";

        echo $this->styleText("Cache", 'yellow') . "\n";
        echo "  " . $this->styleText("cache:clear", 'green') . "\t\tClear the current stored caches." . "\n\n";

        echo $this->styleText("Database", 'yellow') . "\n";
        echo "  " . $this->styleText("db:seed", 'green') . "\t\tRun the specified seeder to populate the database." . "\n";
        echo "  " . $this->styleText("migrate:start", 'green') . "\t\tRun the migration." . "\n";
        echo "  " . $this->styleText("migrate:rollback", 'green') . "\tRollback the current migration." . "\n";
        echo "  " . $this->styleText("migrate:refresh", 'green') . "\tDoes a rollback then run the migration." . "\n\n";

        echo $this->styleText("Encryption", 'yellow') . "\n";
        echo "  " . $this->styleText("key:generate", 'green') . "\t\tGenerate a new encryption key and store it in .env file." . "\n\n";

        echo $this->styleText("Generators", 'yellow') . "\n";
        echo "  " . $this->styleText("make:controller", 'green') . "\tGenerate a new controller file." . "\n";
        echo "  " . $this->styleText("make:middleware", 'green') . "\tGenerate a new middleware file." . "\n";
        echo "  " . $this->styleText("make:migration", 'green') . "\tGenerate a new migration file." . "\n";
        echo "  " . $this->styleText("make:model", 'green') . "\t\tGenerate a new model file." . "\n";
        echo "  " . $this->styleText("make:seeder", 'green') . "\t\tGenerate a new seeder file." . "\n";
    }

    //=====================================================================================================

    public function generator(string $generatedFile, string $path): void
    {
        $generators = new Generators;
        $path       = str_replace('\\', '/', $path);
        $path       = (substr($path, 0, 1) === '/') ? substr($path, 1) : $path;

        // check path
        if (str_contains($path, '/'))
        {
            $pathArray = explode('/', $path);
            $fileName  = array_pop($pathArray);
            $baseDir   = implode('/', $pathArray);

        } else {

            $fileName  = $path;
            $baseDir   = '';
        }

        switch ($generatedFile) {
            case 'controller':
                $command = $generators->makeController($baseDir, $fileName);
                break;

            case 'middleware':
                $command = $generators->makeMiddleware($baseDir, $fileName);
                break;

            case 'model':
                $command = $generators->makeModel($baseDir, $fileName);
                break;

            case 'migration':
                $command = $generators->makeMigration($baseDir, $fileName);
                break;

            case 'seeder':
                $command = $generators->makeSeeder($baseDir, $fileName);
                break;
            
            default:
                $command = false;
                break;
        }

        echo "\n\n";

        if (!$command)
        {
            echo $this->styleText("Operation failed! Fail to generate {$generatedFile} module: {$path}", 'red', false);

        } else {

            $generatedFile = ucfirst($generatedFile);
            echo $this->styleText("{$generatedFile} {$path} module has been generated!", 'green', true);
        }
    }

    //=====================================================================================================

    public function run(array $argv): void
    {
        // store the parameters into static
        self::$CLIParams = $argv;

        // set data
        $version = AETHER_VERSION;

        // always start with new line
        echo "\n";
        
        // echo Command Line Tool interface
        echo $this->styleText("Aether Framework v{$version} Command Line Tool", 'green', true);

        // check if second parameter is empty or help
        // show manual
        if (!isset($argv[1]) || strtolower($argv[1]) === 'help')
        {
            $this->printManual();

            // return and done
            return;
        }

        // set as commands
        $command = $argv[1];

        // check command and throw if command not found
        if (!in_array($command, $this->commandList))
        {
            echo "\n\n";
            echo $this->styleText("Wrong Command! You can only use commands below: ", 'red');
            $this->printManual();

            // return
            return;
        }

        $commands = explode(':', $command);

        // GENERATOR
        if ($commands[0] === 'make')
        {
            if (!isset($argv[2]))
            {
                echo "\n\n";
                echo $this->styleText("There is no generate parameters", 'red');

                // return
                return;
            }

            $this->generator($commands[1], $argv[2]);

            // return
            return;
        }

        // CACHE
        if ($command === 'cache:clear')
        {
            $cache = new Cache();
            $cache->clear();

            echo "\n\n";
            echo $this->styleText("Cache has been cleared", 'green');

            // return
            return;
        }

        // ENCRYPTION
        if ($command === 'key:generate')
        {
            $encryption = new Encryption();
            $encryption->generateKey();

            echo "\n\n";
            echo $this->styleText("App and Session Encryption Key has been generated!", 'green');

            // return
            return;
        }

        // echo "\n";
    }

    //=====================================================================================================
}