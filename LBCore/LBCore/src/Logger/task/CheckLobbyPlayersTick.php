<?php
namespace Logger\task;

use Logger\Logger;
use pocketmine\scheduler\PluginTask;

/**
 * look for players count in lobby
 */
class CheckLobbyPlayersTick extends PluginTask {
	/**@var Logger*/
	private $logger;
	
	public function __construct($owner) {
		parent::__construct($owner);
		$this->logger = Logger::getInstance();
	}
	
	public function onRun($currentTick) {
		if ($currentTick >= 20 * 60 * 30) { // Wait 30 minutes after server start
			$this->logger->checkPlayersInLobbyCount();
		}
	}
}
