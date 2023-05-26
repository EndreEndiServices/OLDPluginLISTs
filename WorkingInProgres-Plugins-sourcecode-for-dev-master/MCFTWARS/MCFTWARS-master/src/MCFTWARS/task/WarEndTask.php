<?php
namespace MCFTWARS\task;

use pocketmine\scheduler\PluginTask;
use MCFTWARS;

class WarEndTask extends PluginTask {
	/**
	 * 
	 * @var \MCFTWARS\MCFTWARS
	 */
	private $plugin;
	public function __construct(MCFTWARS\MCFTWARS $plugin) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}
	public function onRun($currentTick) {
		$this->plugin->war->EndWar();
	}
}