<?php

declare(strict_types = 1);

namespace Aether\CLI\Command;

class Encryption
{
    public function generateKey(): void
    {
        // checking
        echo "\n\n";
        echo "Checking dotenv file...\n";

        // find dotenv file
        $dotenvFile = ROOTPATH . '.env';

        // copy from env file if dotenv file not exist
        if (!file_exists($dotenvFile))
        {
            copy(ROOTPATH . 'env', $dotenvFile);
        }

        // explode new line
        $lines = explode("\n", file_get_contents($dotenvFile));

        foreach ($lines as $i => $line):

            if (str_contains($line, 'App.encryptionKey'))
            {
                echo "Generating App Encryption Key...\n";
                $lines[$i] = "App.encryptionKey = '" . random_string('alphanumeric', 32) . "'";

            } elseif (str_contains($line, 'Session.encryptionKey')) {

                echo "Generating Session Encryption Key...\n";
                $lines[$i] = "Session.encryptionKey = '" . random_string('alphanumeric', 32) . "'";
            }

        endforeach;

        // put content
        echo "Updating DotEnv file...";
        file_put_contents($dotenvFile, implode("\n", $lines));
    }

    //====================================================================================
}