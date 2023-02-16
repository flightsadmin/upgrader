<?php
require_once 'functions.php';
require_once 'updateModels.php';
require_once 'updateViews.php';
require_once 'updateControllers.php';

// Prompt the user to enter a name
while (true) {
    echo "Please Enter CodeIgniter 4 Folder Name: ";

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


// Run Commands
downloadCodeigniter($ci4folderName);
updateEnvFile($ci4folderName);
upgradeModels($ci4folderName, $ci3folderName);
upgradeControllers($ci4folderName, $ci3folderName);
upgradeViews($ci4folderName, $ci3folderName);

echo "CodeIgniter 4 has been installed in the current directory.";