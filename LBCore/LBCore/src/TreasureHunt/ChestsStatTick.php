<?php

namespace TreasureHunt;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use TreasureHunt\task\UpdateChestStatTask;

/**
 * Repeatable task to save chest statistics into db (method updateChestsTask)
 */
class ChestsStatTick extends PluginTask {
	
	private $teeShirt;
	protected $server;


	public function __construct($plugin) {
		parent::__construct($plugin);
		$this->teeShirt = TreasureHunt::getInstance();
		$this->server = Server::getInstance();
	}

	public function onRun($currentTick) {
		if (!is_null(TreasureHunt::$chestsStat) && !empty(TreasureHunt::$chestsStat)) {
			$task = new UpdateChestStatTask(TreasureHunt::$chestsStat);
			$this->server->getScheduler()->scheduleAsyncTask($task);
		}
	}
}
