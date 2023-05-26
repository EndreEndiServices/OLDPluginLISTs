<?php

namespace TheZombies;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerQuitEvent, PlayerInteractEvent};
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};
use pocketmine\Player;
use pocketmine\entity\Effect;

class Events extends PluginBase implements Listener
{
	public $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function onQuit(PlayerQuitEvent $event){
		foreach($this->plugin->arenas as $arena){
			if($arena->inArena($event->getPlayer()) != 0){
				$arena->removePlayer($event->getPlayer(), "quit");
			}
		}
	}
	
	public function onDamage(EntityDamageEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof Player){
			if($event instanceof EntityDamageByEntityEvent){
				$damager = $event->getDamager();
				if($damager instanceof Player){
					foreach($this->plugin->arenas as $arena){
						if($arena->inArena($entity) != 0 and $arena->inArena($damager) != 0){
						if($arena->status == Arena::STATUS_WAITING or $arena->status == Arena::STATUS_START or $arena->status == Arena::STATUS_RELOAD){
							$event->setCancelled();
						}
						if($arena->status == Arena::STATUS_GAME){
							$event->setCancelled();
							if($arena->inArena($entity) == 1 and $arena->inArena($damager) == 2){
								$arena->zombies[$entity->getName()] = $entity->getName();
								unset($arena->survivors[$entity->getName()]);
								$entity->setNameTag("§c". $entity->getName());
								$entity->addEffect(Effect::getEffect(1)->setDuration(9999999)->setVisible(false));
								$entity->sendMessage("§7{$damager->getName()} §eпревратил вас в §cЗомби§e!");
								foreach($arena->world->getPlayers() as $players){
									$players->sendMessage("§l§cINFECTION! §r§eВыживший §7{$entity->getName()} §eбыл превращен в §cЗомби§e!");
								}
							}
						}
						}
					}
				}
			}else{
				foreach($this->plugin->arenas as $arena){
					if($arena->inArena($entity) != 0){
						$ev->setCancelled();
					}
				}
			}
		}
	}
	
	public function onClick(PlayerInteractEvent $ev){
		if($ev->getItem()->getId() == 120 and $ev->getItem()->getCustomName() == "§r§bВыйти в лобби"){
			foreach($this->plugin->arenas as $arena){
				if($arena->inArena($ev->getPlayer()) != 0){
					$arena->removePlayer($ev->getPlayer(), "quit");
				}else{
					$ev->getPlayer()->getInventory()->setItemInHand(0, 0);
				}
			}
		}
	}
}
