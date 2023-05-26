<?php

namespace sLeeD\AuctionHouseUpgraded\Task;

use sLeeD\AuctionHouseUpgraded\Main;

use pocketmine\scheduler\Task;
use pocketmine\Player;

class AHAuction extends Task{

	private $plugin;
	private $player;

	public function __construct(Main $plugin, Player $player){
        $this->plugin = $plugin;
		$this->player = $player;
	}
	
	public function onRun(int $currentTick){
		$this->plugin->openAuctionHouse($this->player);
	}
	
}
