<?php

namespace Logger\task;

use Logger\Logger;
use pocketmine\scheduler\PluginTask;

/**
 * Permanent check of resources usage
 */
class ResourcesUsageTick extends PluginTask {
	/** @var Logger */
	private $logger;
	
	public function __construct($owner) {
		parent::__construct($owner);
		$this->logger = Logger::getInstance();
	}
	
	public function onRun($currentTick) {
		$this->logger->trackCpuUsage();
	}
}
