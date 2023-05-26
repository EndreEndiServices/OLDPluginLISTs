<?php
namespace FaigerSYS\VeryShortSex;

use pocketmine\scheduler\PluginTask;

class ClearRequestsTask extends PluginTask {
	
	public function onRun($tick) {
		$this->getOwner()->clearInactiveRequests();
	}
	
}
