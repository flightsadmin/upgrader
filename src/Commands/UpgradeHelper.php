<?php

namespace Flightsadmin\Upgrader\Commands;

use Config\Autoload;
use Config\Services;

trait UpgradeHelper
{
    public function startUpgrade() {
        // Prompt the user to enter a name
        while (true) {
            $ci4folderName = CLI::prompt('Hello, Do you want to proceed?');
    
            // Read user input
            $ci4folderName = trim(fgets(STDIN));
    
            // Check if the user provided a name
            if (!empty($ci4folderName)) {
                break;
            }
    
            // Print an error message and repeat the loop
            echo "\033[31mError: CodeIgniter 4 folder name is required.\033[0m\n";
        }
    
    
        $dir = getcwd();
        $folders = array_filter(glob($dir . '/*'), 'is_dir');
    
        // Prompt the user to a codeigniter 3 folder
        $folderList = [];
        foreach ($folders as $key => $folder) {
            $folderList[$key+1] = basename($folder);
        }
    
        echo "Select a folder:\n";
        foreach ($folderList as $key => $folder) {
            echo "$key. $folder\n";
        }
    
        while (true) {
            // Read user input
            $response = readline("Enter your choice: ");
    
            // Check if the user provided a response
            if (!empty($response)) {
                break;
            }
    
            // Print an error message and repeat the loop
            echo "\033[31mError: You must choose a codeigniter 3 project to upgrade.\033[0m\n";
        }
    
        $ci3folderName = trim($folderList[$response]);
    
        if (is_dir($ci4folderName)) {
            deleteFolder($ci4folderName);
        }
    }

    //Remove Codeigniter 4 Directory if it exist.
    public function deleteFolder($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        deleteFolder($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }

    // Download and extract CodeIgniter 4 source code
    public function downloadCodeigniter($ci4folderName)
    {
        $ci4_zip_url = 'https://github.com/codeigniter4/appstarter/archive/refs/tags/v4.4.1.zip';
        file_put_contents('ci4.zip', fopen($ci4_zip_url, 'r'));
        $zip = new ZipArchive();
        $zip->open('ci4.zip');
        $zip->extractTo('.');
        $zip->close();
        rename('appstarter-4.4.1', $ci4folderName);
        unlink('ci4.zip');
    }

    // Update .env File
    public function updateEnvFile($ci4folderName)
    {
        $env = file_get_contents($ci4folderName. '/env');
        $content = str_replace('# database.default', 'database.default', $env);
        $content = str_replace('# CI_ENVIRONMENT = production', 'CI_ENVIRONMENT = development', $content);
        
        file_put_contents($ci4folderName. '/.env', $content);
    }
}