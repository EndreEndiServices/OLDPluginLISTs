<?php

namespace BeatsCore;

use BeatsCore\Core;
use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

class Title extends PluginTask{

	public function __construct(Core $main, Player $player){
		parent::__construct($main, $player);
		$this->main = $main;
		$this->player = $player;
    }
    
	public function onRun($tick){
        $this->player->addTitle("§k§aii§r §l§dBeats§bPE§r §k§aii§r", "§k§aii§r §l§3Factions§r §k§aii§r");
	}
}