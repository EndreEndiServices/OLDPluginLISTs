<?php

namespace IPECore;

use pocketmine\scheduler\PluginTask;

class FarmsTask extends PluginTask {
	/**
	 *
	 * @param Farms $owner        	
	 */
	public function __construct(Main $owner) {
		parent::__construct ( $owner );
	}
	public function onRun($currentTick) {
		/**
		 * @var $owner Farms
		 */
		$owner = $this->getOwner ();
		$owner->tick ();
	}
}

?>