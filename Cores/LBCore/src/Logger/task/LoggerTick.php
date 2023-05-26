<?php

namespace Logger\task;

use Logger\Logger;
use pocketmine\Server;
use pocketmine\scheduler\PluginTask;

/**
 * Make regular checks for Logger like server status, kicked players, server health
 */
class LoggerTick extends PluginTask {

	/** @var Logger */
	private $logger;

	/** @var string */
	private $lastDate;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->logger = Logger::getInstance();
		$this->lastDate = date('Y.m.d');
	}

	public function onRun($currentTick) {
		$server = Server::getInstance();

		$tps = $server->getTicksPerSecond();
		$playerNumber = count($server->getOnlinePlayers());
		$ram = round((memory_get_usage() / 1024) / 1024, 2) . ' / ' . round((memory_get_usage(true) / 1024) / 1024, 2) . ' MB';
		$cpu = $server->getTickUsage() . '%';

		$msg = "SERVER STATUS: [ TPS: {$tps} PLAYERS: {$playerNumber} MEMORY: {$ram} CPU: {$cpu} ]";

		$this->logger->write($msg, true);
		$this->logger->checkServerStartsCount();
		$this->logger->checkKickedPlayers();
		$this->logger->checkCPUUsage();
		$this->logger->checkMemoryUsage($currentTick);

		if ($this->lastDate != date('Y.m.d')) {
			$this->logger->changeLogFile();
			$this->lastDate = date('Y.m.d');
		}
	}

}
