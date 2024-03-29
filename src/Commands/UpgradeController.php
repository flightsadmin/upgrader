<?php

namespace Flightsadmin\Upgrader\Commands;

use Config\Autoload;
use Config\Services;

trait UpgradeController
{
    public function upgradeControllers($ci4folderName, $ci3folderName) {

        $src_dir = getcwd(). '/'.$ci3folderName. '/application/controllers';
        $dest_dir = getcwd() . '/' . $ci4folderName . '/app/Controllers';
        $replace = "<?php\nnamespace App\Controllers;\n\nuse App\Controllers\BaseController;\n\nclass ";
    
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src_dir));
    
        foreach ($iterator as $file) {
            // Check if file is a PHP file
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $contents = file_get_contents($file);
                // Replace text between `<?php` and `class`
                $contents = preg_replace('/<\?php\s+(.*?)\s+class\s+/s', $replace, $contents);	
                // Remove Comments
                $contents = preg_replace('/\/\*[\s\S]*?\*\/|\/\/.*(?=[\n\r])/', '', $contents);
                // Replace 'CI_Controller' with 'BaseController'
                $contents = str_replace('extends CI_Controller ', "extends BaseController\n", $contents);
                $contents = str_replace('$this->load->view(', 'return view(', $contents);
                // Replace text between `class` and `extends`
                $contents = preg_replace_callback('/class\s+(\w+)\s+extends/', function($matches){
                    return "class " . ucwords($matches[1], "_") . " extends";
                }, $contents);
    
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
}