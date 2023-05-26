<?php

namespace VipLounge;

use pocketmine\scheduler\PluginTask;
use LbCore\LbCore;

/**
 * Holds task for guards activity (tick method)
 */
class VIPLoungeGuardsTask extends PluginTask {

	/** @var LbCore */
	private $LbCore;

	/**
	 * @param LbCore $owner
	 */
	public function __construct($owner) {
		parent::__construct($owner);
		$this->LbCore = $owner;
	}

	/**
	 * Call tick method for each guard
	 * @param $currentTick
	 */
	public function onRun($currentTick) {
		$guards = VIPLounge::getInstance()->getLoungeGuards();
		foreach ($guards as $guard) {
			$guard->tick();
		}
	}

}
