<?php 

declare(strict_types = 1);

namespace Aether\CLI;

/** 
 * The App class to run the CLI in Aether Framework
 * 
 * @class Aether\CLI
**/

class App
{
    public static array $CLIParams = [];

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

        // check if second parameter is empty
        // show manual
        if (!isset($argv[1]))
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

            // return and done
            return;
        }

        // echo "\n";
    }

    //=====================================================================================================
}