<?php

namespace SalmonDE\Updater;

use pocketmine\plugin\Plugin;
use pocketmine\Server;

class UpdateManager {
	const VERSION = '1.2.5';

	const SERVICE = 'https://salmonde.de/MCPE-Plugins/Updater/Updater.php';
	const FAILED = 0;
	const SUCCESS = 1;

	public static $instances = [];
	public static $shutdown = false;

	private $allowUpdate;
	private $plugin = null;
	private $path = null;
	private $k = null;

	public static function getNew(string $path, Plugin $plugin, $update = true): UpdateManager{
		if(!file_exists($path)){
			return;
		}
		$class = new self();
		$class->path = $path;
		$class->plugin = $plugin;
		$class->allowUpdate = $update;
		self::$instances[$plugin->getName()] = $class;

		return $class;
	}

	public function start(){
		if(file_exists($path = $this->plugin->getDataFolder() . 'delete' . $this->plugin->getDescription()->getVersion())){
			Utils::deleteFile(file_get_contents($path));
			Utils::deleteFile($path);
		}
		$this->k = \pocketmine\utils\UUID::fromRandom()->toString();
		Server::getInstance()->getScheduler()->scheduleAsyncTask(new Tasks\VerifyVersionTask($this->plugin, $this->k));
	}

	public function checkVersion(array $data, $k){
		if($k === null || $k !== $this->k){
			return;
		}
		$this->k = null;
		if($this->plugin->getDescription()->getVersion() < $data['version']){
			if(in_array(\pocketmine\API_VERSION, $data['api'])){
				if($this->allowUpdate === true){
					$this->plugin->getLogger()->notice('Updating to version ' . $data['version'] . ' ...');
					$this->update($data['downloadurl'], $data['md5'], $data['version']);
				}else{
					$this->plugin->getLogger()->notice('A new version (' . $data['version'] . ') is ready to be downloaded!');
					$this->plugin->getLogger()->notice('Please consider to download the update from:');
					$this->plugin->getLogger()->notice($data['downloadurl']);
					$this->finish();
				}
			}else{
				$supportedApis = 'Supported APIs: ' . implode(', ', $data['api']);
				$this->processError('', ['An update is available but the API version of your server isn\'t supported by it.', 'API version of the server: ' . \pocketmine\API_VERSION, $apis], true);
			}
		}else{
			$this->plugin->getLogger()->notice('No update available! :)');
			$this->finish();
		}
	}

	private function update(string $url, string $md5Hash, string $version){
		Server::getInstance(\pocketmine\utils\TextFormat::AQUA . 'The server will freeze and probably restart due to a software update!');
		$fileContent = \pocketmine\utils\Utils::getURL($url);
		if(md5($fileContent) === $md5Hash){
			$path = Server::getInstance()->getPluginPath() . $this->plugin->getName() . '_v' . $version . '.phar';
			Utils::saveFile($path, $fileContent);
			Utils::checkFile($path, $md5Hash, $valid);
			if($valid){
				Server::getInstance()->getPluginManager()->disablePlugin($this->plugin);
				Utils::deleteFile($this->path, ['path' => $this->plugin->getDataFolder(), 'version' => $version]);
				$this->shutdownServer();
			}else{
				Utils::deleteFile($path);
				$this->processError('The updated file couldn\'t be saved!', ['Please try updating manually from ' . $url]);
			}
		}else{
			$this->processError('The downloaded file failed the md5 hash check!', ['Please try updating manually from ' . $url]);
		}
	}

	private function shutdownServer(bool $finished = false){
		self::$shutdown = true;
		if(!$finished){
			$this->finish();
		}
		if(count(self::$instances) === 0){
			Server::getInstance()->shutdown();
		}
	}

	private function finish(){
		unset(self::$instances[$this->plugin->getName()]);
		if(self::$shutdown){
			$this->shutdownServer(true);
		}
	}

	public function processError(string $error, array $infos = null, $warning = false){
		if($warning){
			if($infos){
				foreach($infos as $info){
					$this->plugin->getLogger()->warning($info);
				}
			}
		}else{
			$this->plugin->getLogger()->error('An error occured while checking for an update!');
			$this->plugin->getLogger()->error('Error: ' . $error);
			if($infos){
				foreach($infos as $info){
					$this->plugin->getLogger()->error('Info: ' . $info);
				}
			}
			$this->plugin->getLogger()->error('Updater version: ' . self::VERSION);
		}
		$this->finish();
	}

	public function __destruct(){
		$this->finish();
	}
}
