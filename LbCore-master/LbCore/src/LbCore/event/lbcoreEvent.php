<?php
namespace LbCore\event;

use LbCore\LbCore;
use pocketmine\event\plugin\PluginEvent;

abstract class LbCoreEvent extends PluginEvent {

	public function __construct(LbCore $plugin) {
		parent::__construct($plugin);
	}
}