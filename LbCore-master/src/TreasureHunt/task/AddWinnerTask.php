<?php

namespace TreasureHunt\task;

use LbCore\player\LbPlayer;
use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;
use TreasureHunt\TreasureHunt;
use TreasureHunt\Slack;

class AddWinnerTask extends LbAsyncTask {
	protected $chestId;
	protected $contestId;
	protected $playerName;
	protected $result = 'fail';

	public function __construct($chestId, $contestId, $playerName) {
		$this->chestId = $chestId;
		$this->contestId = $contestId;
		$this->playerName = $playerName;
	}
	
	public function onRun() {
		$url = 'http://104.131.74.238/treasurehunt.php';
		$postParam = array(
            'auth' => self::AUTH_STRING,
            'action' => 'addWinner',
			'chest_id' => $this->chestId,
			'contest_id' => $this->contestId,
			'username' => $this->playerName,
        );
		
		$result = Utils::postURL($url, $postParam, 10);
		$result = json_decode($result, true);
		
		if ($result) {
			$this->result = $result['state'];
		}
	}
	
	public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->playerName);
		$chest = TreasureHunt::getInstance()->getTeeShirtChest();
		
		if (!($player instanceof LbPlayer) || is_null($chest) || $this->result !== 'success') {
			return;
		}
		
		$player->setFindTreasure();

		$playerName = $player->getName();
		$gameType = strtoupper($chest->game_type);
		$mapName = $chest->arena_name;
		$serverName = $server->getConfigString('server-dns', 'unknown.lbsg.net');

		$message = "{$playerName} has found the t-shirt on {$serverName} server on {$mapName} map.";
		$server->getScheduler()->scheduleAsyncTask(new Slack($serverName, $message));
	}
}
