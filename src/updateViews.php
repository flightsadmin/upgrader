<?php

function upgradeViews($ci4folderName, $ci3folderName) {

    $src_dir = getcwd(). '/'.$ci3folderName. '/application/views';
    $dest_dir = getcwd() . '/' . $ci4folderName . '/app/Views';

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src_dir));

    foreach ($iterator as $file) {
        // Check if file is a PHP file
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $contents = file_get_contents($file);
            // Remove Comments
            // $contents =  preg_replace('/\/\*(.|[\r\n])*?\*\/|\/\/.*(?=[\n\r])|\<\!\-\-(.|[\r\n])*?\-\-\>/', '', $contents);
            // Replace 'CI_Controller' with 'BaseController'
            $contents = str_replace('<?php echo html_escape', '<?= esc', $contents);

            // Write modified contents to a new file in the destination directory
            $relative_path = substr($file, strlen($src_dir));
            $new_file = $dest_dir . $relative_path;
            $new_dir = dirname($new_file);
    
            // Create the directory if it doesn't exist
            if (!is_dir($new_dir)) {
                mkdir($new_dir, 0755, true);
            }
    
            file_put_contents($new_file, $contents);
            echo $new_file. "\n";
        }
    }
}