<?php

namespace Pets\task;

use pocketmine\scheduler\PluginTask;

/**
 * This task checks every 1 minute if player is need to get random pet message
 */
class SpawnPetsTick extends PluginTask {
	
	/**
	 * Base class constructor
	 * @param Plugin $plugin
	 */
	public function __construct($plugin) {
		parent::__construct($plugin);
	}
	
	/**
	 * Repeatable check for pet message receivers
	 * 
	 * @param int $currentTick
	 */
	public function onRun($currentTick) {
		$onlinePlayers = \pocketmine\Server::getInstance()->getOnlinePlayers();
		foreach ($onlinePlayers as $player) {
			if(($state = $player->getPetState())) {
				if($state['state'] == "toggle") {
					$player->togglePetEnable();
				} elseif($state['state'] == "enable") {
					$player->enablePet($player, $state['petType']);
				} elseif($state['state'] == "show") {
					$player->showPet($state['petType']);
				} elseif($state['state'] == "hide") {
					$player->hidePet();					
				}  elseif($state['state'] == "create") {
					$player->createPet();					
				}
				$player->clearPetState();
			}
		}
	}
	
	
}
