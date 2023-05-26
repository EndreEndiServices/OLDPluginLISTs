<?php

namespace TheZombies;

use pocketmine\scheduler\PluginTask;

class Task extends PluginTask
{
	public $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
	
	public function onRun($currentTicks){
		foreach($this->plugin->arenas as $name => $class){
			$class->tick();
		}
	}
}
