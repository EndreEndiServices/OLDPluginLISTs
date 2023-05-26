<?php

namespace Kits\task;

use LbCore\task\LbAsyncTask;
use LbCore\player\exceptions\PlayerBaseException;
use pocketmine\Server;
use pocketmine\utils\Utils;

class GetKitsTask extends LbAsyncTask {
	protected $playerName;
	protected $kits = null;
	
	public function __construct(string $playerName) {
		$this->playerName = $playerName;
	}
	
	public function onRun() {
		$url = 'http://104.131.74.238/kits.php';
		$postParam = array(
            'auth' => self::AUTH_STRING,
            'action' => 'get',
            'username' => $this->playerName,
        );
		
        $result = Utils::postURL($url, $postParam, 5);
		$result = json_decode($result, true);
		
		if ($result && $result['state'] == 'success') {
			$this->kits = (object) $result['data'];
			return true;
		} else if ($result['state'] == 'fail') {
			var_dump($result['message']);
			return false;
		}
	}
	
	public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->playerName);
		
		if (is_null($player)) {
			return;
		}
		
		$kits = (array) $this->kits;
		if (is_null($kits) || empty($kits)) {
			return;
		}
		
		$kits = array_shift($kits);
		$kitId = $kits['kits'];
		
		//check if user already have active kit
		$activeInterval = new \DateTime(date('Y-m-d H:i:s'));
		$activeInterval->sub(new \DateInterval('P1D'));//look for last 24 hours
		$datetimeActivated = $kits['last_kit_activated'] ? new \DateTime($kits['last_kit_activated']) : $activeInterval;
		if ($datetimeActivated > $activeInterval) {
			try {
				$player->addKit($kitId);
			} catch (PlayerBaseException $e) {
				$server->getLogger()->warning($e->getMessage());
			}
		}
	}
}
