<?php
namespace LbCore\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Update number of players on server
 */
class UpdatePlayerCount extends AsyncTask {

	public function __construct() {}

	public function onRun() {
		$result = Utils::getURL('http://boatstat.us/api.php', 5);
		$data = json_decode($result);
		$this->setResult($data);
	}

	public function onCompletion(Server $server) {
		if($this->hasResult()){
			$server->getPluginManager()->getPlugin("LbCore")->playerCount = $this->getResult();
		}
	}
}
