<?php

namespace SalmonDE\Updater\Tasks;

use pocketmine\scheduler\AsyncTask;
use SalmonDE\Updater\UpdateManager;
use SalmonDE\Updater\Utils;

class VerifyVersionTask extends AsyncTask {

	public function __construct(\pocketmine\plugin\Plugin $pl, $k){
		$this->pluginName = $pl->getName();
		$this->k = $k;
	}

	public function onRun(){
		$data = Utils::getDataFromService($this->pluginName);
		$this->setResult($data);
	}

	public function onCompletion(\pocketmine\Server $server){
		$inst = UpdateManager::$instances[$this->pluginName];
		$data = $this->getResult();
		if(isset($data['error'])){
			$inst->processError($data['error']);
		}else{
			$inst->checkVersion($data, $this->k);
		}
	}
}
