<?php

namespace TreasureHunt\task;

use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;
use TreasureHunt\TreasureHunt;

class UpdateChestStatTask extends LbAsyncTask {
	protected $chestStat;
	protected $result = 'fail';


	public function __construct($chestStat) {
		$this->chestStat = $chestStat;
	}
	
	public function onRun() {
		$url = 'http://104.131.74.238/treasurehunt.php';
		$postParam = array(
            'auth' => self::AUTH_STRING,
            'action' => 'updateStat',
			'data' => json_encode($this->chestStat),
        );
		
		$result = Utils::postURL($url, $postParam, 5);
		
		$result = json_decode($result, true);
		
		if ($result) {
			$this->result = $result['state'];
		}
	}
	
	public function onCompletion(Server $server) {
		if ($this->result == 'fail') {
			return;
		}
		TreasureHunt::$chestsStat = array();
	}
}
