<?php

//Remove Codeigniter 4 Directory if it exist.
function deleteFolder($dir) {
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
function downloadCodeigniter($ci4folderName)
{
    $ci4_zip_url = 'https://github.com/codeigniter4/appstarter/archive/refs/tags/v4.3.1.zip';
    file_put_contents('ci4.zip', fopen($ci4_zip_url, 'r'));
    $zip = new ZipArchive();
    $zip->open('ci4.zip');
    $zip->extractTo('.');
    $zip->close();
    rename('appstarter-4.3.1', $ci4folderName);
    unlink('ci4.zip');
}

// Update .env File
function updateEnvFile($ci4folderName)
{
    $env = file_get_contents($ci4folderName. '/env');
    $content = str_replace('# database.default', 'database.default', $env);
    $content = str_replace('# CI_ENVIRONMENT = production', 'CI_ENVIRONMENT = development', $content);
    
    file_put_contents($ci4folderName. '/.env', $content);
}