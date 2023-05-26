<?php 
namespace ServerCore;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

class Checker extends PluginTask{
	public function __construct($plugin){
		$this->plugin = $plugin;
		parent::__construct($plugin); 
	}

	public function onRun($tick){ 
$this->getOwner()->task();
	 
}
}

