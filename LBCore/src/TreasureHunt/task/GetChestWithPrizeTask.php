<?php

namespace TreasureHunt\task;

use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;
use TreasureHunt\TreasureHunt;

class GetChestWithPrizeTask extends LbAsyncTask {
	protected $gameType;
	protected $chest = null;	
	
	public function __construct($gameType) {
		$this->gameType = $gameType;
	}
	
	public function onRun() {
		$url = 'http://104.131.74.238/treasurehunt.php';
		$postParam = array(
            'auth' => self::AUTH_STRING,
            'action' => 'getChest',
			'game_type' => $this->gameType,
        );
		
		$result = Utils::postURL($url, $postParam, 5);
		$result = json_decode($result, true);
		
		if ($result && $result['state'] == 'success') {
			if (!empty($result['data'])) {
				$this->chest = (object) $result['data'][0];
			}
		}
	}
	
	public function onCompletion(Server $server) {
		TreasureHunt::setTeeShirtChest($this->chest);
		if (!is_null($this->chest)) {
			TreasureHunt::createTreasureChest();
		}
	}
}
