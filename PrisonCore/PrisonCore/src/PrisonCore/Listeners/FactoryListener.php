<?php
namespace PrisonCore\Listeners;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerJoinEvent;

//use pocketmine\entity\Effect;
use pocketmine\block\Block;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use PrisonCore\Core;
use PrisonCore\BasicLib;

class FactoryListener implements Listener{
	
	 protected $factory = array();
	 protected $incomeAble = [1, 14, 15, 16, 56, 21, 129, 73, 74];

	 public function __construct(Core $plugin){
	      $this->core = $plugin;
	      $this->target = $plugin->cfg["deposit.target"];
		}
	 public function getCore(){
	      return $this->core;
		}
	 public function registerFactory(PlayerJoinEvent $event){
	      $player = $event->getPlayer();
	      $name = strtolower($player->getName());
	      if($this->getCore()->factoryExists($player)){
		     $this->factory[$name] = 0;
		   }
		}
	 public function processFactory(BlockBreakEvent $event){
	      $player = $event->getPlayer();
	      $name = strtolower($player->getName());
	      $blockId = $event->getBlock()->getId();
	      if($this->getCore()->factoryExists($player) && in_array($blockId, $this->incomeAble)){
		     $this->factory[$name]++;
		     $count = $this->target - $this->factory[$name];
		     $player->sendPopup("§l§a[FactoryManager]§r§8 ›› §6".$count." blocks till next factory deposit");
		   }
	      if($this->factory[$name] === $this->target){
		     $this->getCore()->giveFactoryIncome($player);
		     $this->factory[$name] = 0;
     }
}
}