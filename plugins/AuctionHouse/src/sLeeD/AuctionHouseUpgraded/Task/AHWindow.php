<?php

namespace sLeeD\AuctionHouseUpgraded\Task;

use sLeeD\AuctionHouseUpgraded\Main;

use pocketmine\scheduler\Task;
use pocketmine\Player;

class AHWindow extends Task{

	private $plugin;
	private $player;
	private $inventory;

	public function __construct(Main $plugin, Player $player, $inventory){
        $this->plugin = $plugin;
		$this->player = $player;
		$this->inventory = $inventory;
	}
	
	public function onRun(int $currentTick){
		if($this->inventory != null){
			$this->player->addWindow($this->inventory->getInventory());
		}
	}
	
}
