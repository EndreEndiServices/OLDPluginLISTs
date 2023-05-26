<?php

namespace bridge\Lord;

use pocketmine\scheduler\PluginTask;
use bridge\Main;

class BridgeTask extends PluginTask{
	
	public function __construct(Main $plugin){
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}
	
	public function onRun($timer){
		$this->plugin->updateArenas(true);
	}
}