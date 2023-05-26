<?php

namespace Kits\task;

use LbCore\task\LbAsyncTask;
use pocketmine\utils\Utils;

class SaveKitsTask extends LbAsyncTask {
	protected $playerName;
	protected $kits = null;
	protected $lastTimeActivated;

	public function __construct(string $playerName, $kits) {
		$this->playerName = $playerName;
		if (is_array($kits)) {
			$kits = implode(', ', $kits);
		}
		$this->kits = $kits;
		$this->lastTimeActivated = date('Y-m-d H:i:s');
	}
	
	public function onRun() {
		$url = 'http://104.131.74.238/kits.php';
		$postParam = array(
            'auth' => self::AUTH_STRING,
            'action' => 'set',
			'username' => $this->playerName,
			'kit_id' => $this->kits,
			'activation_time' => $this->lastTimeActivated,
        );
		
        $result = Utils::postURL($url, $postParam, 5);
		$result = json_decode($result, true);
		
		if ($result && $result['state'] == 'success') {
			return true;
		} else if ($result['state'] == 'fail') {
			var_dump($result['message']);
			return false;
		}
	}
	
	public function onCompletion(\pocketmine\Server $server) {}
}
