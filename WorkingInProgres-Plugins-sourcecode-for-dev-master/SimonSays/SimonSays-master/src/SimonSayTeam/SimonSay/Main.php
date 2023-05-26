<?php

namespace SimonSaysTeam\SimonSays

use pocketmine\block\Block;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{
  
  public function onEnable(){
		$this->getServer()->getLogger()->info(TextFormat::BLUE . "SimonSays Has Been Enabled.");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onDisable(){
		$this->getServer()->getLogger()->info(TextFormat::GRAY . ">" . TextFormat::RED . "RED" . "SimonSays was disabled.");
	}
