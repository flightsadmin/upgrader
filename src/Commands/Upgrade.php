<?php

namespace Flightsadmin\Upgrader\Commands;

use Config\Database;
use CodeIgniter\CLI\CLI;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use CodeIgniter\CLI\BaseCommand;
use Flightsadmin\Upgrader\Commands\UpgradeView;
use Flightsadmin\Upgrader\Commands\UpgradeModel;
use Flightsadmin\Upgrader\Commands\UpgradeHelper;
use Flightsadmin\Upgrader\Commands\UpgradeController;

class Upgrade extends BaseCommand
{
    use UpgradeHelper, UpgradeModel, UpgradeController, UpgradeView;

	protected $group 		= 'upgraders';
	protected $name 		= 'upgrader:install';
	protected $description	= 'Upgrade CodeIgniter 3 to CodeIgniter 4';
	protected $usage 		= 'upgrader:install';

	public function run(array $params)
	{
		try {
			if (CLI::prompt('Hello, Do you want to proceed?', ['y', 'n']) == 'y')
				{				    
                    $this->startUpgrade();

                    // $this->downloadCodeigniter($ci4folderName);
                    // $this->updateEnvFile($ci4folderName);
                    // $this->upgradeModels($ci4folderName, $ci3folderName);
                    // $this->upgradeControllers($ci4folderName, $ci3folderName);
                    // $this->upgradeViews($ci4folderName, $ci3folderName);

                    echo "CodeIgniter 4 has been installed in the current directory.";


					CLI::write('Auth Module successfully installed, To use our crud upgrader run: '. CLI::color( 'php spark make:crud', 'yellow'), 'cyan');
				} else {
					CLI::write("Operation Cancelled by User, No files were affected", 'red');
				}

		} catch (\Exception  $e) {
			$this->showError($e);
		}
	}

	private function startsUpgrade()
	{
            $ci3dir = CLI::prompt('Hello, Do you want to proceed?');


            CLI::write($ci3dir);
            return;

            CLI::write("Updating Filters file",'blue');
            $filtersFile = APPPATH.'Config/Filters.php';
            $filtersContents = file_get_contents($filtersFile);
            $filtersItemStub = "\t\t'auth' 		=> \App\Filters\Auth::class, \n\t\t'noauth' 	=> \App\Filters\Noauth::class,";
            $filtersItemHook = 'public $aliases = [';

            if (!strpos($filtersContents, $filtersItemStub)) {
                $newContents = str_replace($filtersItemHook, $filtersItemHook . PHP_EOL . $filtersItemStub, $filtersContents);

                file_put_contents($filtersFile, $newContents);
            } 		
            CLI::write("Filter file updated successfully",'green');
	}

	private function updateRoute()
	{
		CLI::write("Updating route file",'blue');
			$routeFile = APPPATH.'Config/Routes.php';
			$string = file_get_contents($routeFile);

			$data_to_write ="\n //Custom Routes Added during installation \n";
			$data_to_write.="\$routes->get('/', 'Home::index', ['filter' => 'noauth']);\n";
	        $data_to_write.="\$routes->get('profile', 'UserController::profile', ['filter' => 'auth']);\n";
	        $data_to_write.="\$routes->match(['get','post'],'login', 'UserController::login', ['filter' => 'noauth']);\n";
	        $data_to_write.="\$routes->match(['get','post'],'register', 'UserController::register', ['filter' => 'noauth']);\n";	         
	        $data_to_write.="\$routes->get('logout', 'UserController::logout');\n";
	        $data_to_write.="\n //Admin Routes \n";
	        $data_to_write.="\$routes->group('admin', ['filter' => 'auth'], function(\$routes){\n\n\t";
	        $data_to_write.="\$routes->get('/', 'AdminController::index');\n});";
	        $data_to_write.="\n //Editor Routes \n";
	        $data_to_write.="\$routes->group('editor', ['filter' => 'auth'], function(\$routes){\n\n\t";
	        $data_to_write.="\$routes->get('/', 'EditorController::index');\n});";

	        if (!strpos($string, $data_to_write)) {
				file_put_contents($routeFile, $data_to_write, FILE_APPEND);
			}
        CLI::write("Route file updated successfully",'green');
	}

	private function copyResources()
	{
		CLI::write("Copying Auth Asset Files", 'blue');
		$sourcePath = realpath(__DIR__ . "/../Install");
		$destPath = realpath(ROOTPATH);

		$this->copy($sourcePath, $destPath, ["mix-manifest.json"]);
		CLI::write("Auth Asset Files copied successfully",'green');
	}

	public function copy($source, $target, $skipFiles = [])
	{
		if (!is_dir($source)) {
			return copy($source, $target);
		}

		$it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
		$ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);
		$this->ensureDirectoryExists($target);

		$result = true;
		/** @var RecursiveDirectoryIterator $ri */
		foreach ($ri as $file) {

			$fileName = $file->getFilename();

			$skip = false;
			foreach ($skipFiles as $skipFile) {
				if (strcasecmp($skipFile, $fileName) == 0) {
					$skip = true;
				}
			}

			if ($skip) {
				continue;
			}

			$targetPath = $target . DIRECTORY_SEPARATOR . $ri->getSubPathName();
			if ($file->isDir()) {
				$this->ensureDirectoryExists($targetPath);
			} else {
				$result = $result && copy($file->getPathname(), $targetPath);
			}
		}

		return $result;
	}

	public function ensureDirectoryExists($directory)
	{
		if (!is_dir($directory)) {
			if (file_exists($directory)) {
				throw new \RuntimeException(
					$directory . ' exists and is not a directory.'
				);
			}
			if (!@mkdir($directory, 0777, true)) {
				throw new \RuntimeException(
					$directory . ' does not exist and could not be created.'
				);
			}
		}
	}
}