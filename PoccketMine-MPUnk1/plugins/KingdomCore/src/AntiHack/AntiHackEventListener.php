<?php

namespace AntiHack;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerItemHeldEvent;

class AntiHackEventListener implements Listener {

	private $plugin;
	
	public function __construct() {}

  public function onAntiHack(PlayerMoveEvent $event){
       $player = $event->getPlayer(); 
       $item = $player->getInventory()->getItemInHand();
       $br = C::RESET . C::WHITE . "\n";
       $kickMessage = $br . $br . C::RED . $player->getName() . $br . $br ."KingdomCraft". C::RED ." Does not Allow Hacking". $br ."Turn off Hacks or Mods if you Have Any";
  if($event->isCancelled() or $player->isOp()  or $player->isCreative() or $player->isSpectator() or $player->getAllowFlight() or $item->getId() == 288){
  return;
  }  
  else 
  {
  if(($player->getInAirTicks() > 30) >= 2000) {
       $player->kick($kickMessage);
     } 
    }
   }  
  }
