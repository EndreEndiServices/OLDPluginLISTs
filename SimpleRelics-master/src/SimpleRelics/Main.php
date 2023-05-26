<?php

namespace SimpleRelics;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{
	
	
		public function onEnable() {
			
			$this->getServer()->getPluginManager()->registerEvents($this,$this);
			$this->saveDefaultConfig();
			$this->getServer()->getLogger()->notice(TF::AQUA . TF::BOLD . "SimpleRelics" . TF::RESET . TF::GRAY . "has been loaded! Plugin by ShadowLessMCPE");
			$this->getServer()->getLogger()->notice(TF::GRAY . "Github: https://github.com/diamondgamermcpe");
			$this->getServer()->getLogger()->notice(TF::GRAY . "Twitter: https://twitter.com/DavidGamingzz");
		
	}
	
		public function onDisable() {
		
			$this->getServer()->getLogger()->warning(TF::AQUA . TF::BOLD . "SimpleRelics" . TF::RESET . TF::GRAY . "has been unloaded!");
		
	}
	
		public function onBreak(BlockBreakEvent $event) {

	//////////////////////// Relic ////////////////////////
		
			if($event->getBlock()->getId() == 1) {
			
				if(mt_rand(1, 5000) === 10) {
		
					$player = $event->getPlayer();
					$name = $player->getName();
			
					$tier1 = Item::get(Item::NETEHR_STAR, 0, 1);
					$tier1->setCustomName(TF::RESET . TF::GOLD . TF::BOLD . "Relic" . TF::RESET . TF::GRAY . " (Click)" . PHP_EOL .
					TF::GRAY . " * " . TF::GREEN . "A treasure found by mining stone" . PHP_EOL .
					TF::GRAY . " * " . TF::GREEN . "Tap anywhere to see what it holds");
		
					$player->getInventory()->addItem($tier1);
					$player->addtitle(TF::GRAY . "You have found a", TF::GOLD . TF::BOLD . "Relic" . TF::RESET);
					$this->getServer()->broadcastMessage(TF::RED . TF::BOLD . "(!) " . TF::RESET . TF::GREEN . $name . TF::GRAY . " has found a " . TF::GOLD . TF::BOLD . "Relic");
					
				}
			
			}
		}

	//////////////////////// Open Relic ////////////////////////
	
		public function onTap(PlayerInteractEvent $event) {
	
			$player = $event->getPlayer();
			$item = $player->getInventory()->getItemInHand();
  
				if($item->getName() == TF::RESET . TF::GOLD . TF::BOLD . "Relic" . TF::RESET . TF::GRAY . " (Click)" . PHP_EOL . TF::GRAY . " * " . TF::GREEN . "A treasure found by mining stone" . PHP_EOL . TF::GRAY . " * " . TF::GREEN . "Tap anywhere to see what it holds") {
		
					$player->getInventory()->removeItem(Item::get(Item::NETHER_STAR, 0, 1));
					$player->addTitle(TF::GRAY . "Uncovering", TF::GOLD . TF::BOLD . "Relic");
					
					$open = $player->addTitle(TF::GREEN . "You have received", TF::GREEN . "your rewards!");
					
					$this->getServer()->getScheduler()->scheduleDelayedTask($open, 100);
					
					
				foreach($this->getConfig()->get("relic-loot") as $rewards){
					
					$give = $player->getInventory()->addItem(Item::get($rewards, 0, mt_rand(1,$this->getConfig()->get("relic-max"))));
					
					$this->getServer()->getScheduler()->scheduleDelayedTask($give, 500);

			}
		}
	}
}
