<?php

namespace SimonSayTeam\SimonSay\Match

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

public $moved = [];
public function onPlayerMove(PlayerMoveEvent $event){
  if($event->getFrom()->x == $event->getPlayer()->x && $event->getFrom()->z == $event->getPlayer()->z){
    return;
  }
  $this->moved[$event->getPlayer()->getName() = $event->getPlayer()->getName();
  
  if(in_array(Player $player->getName(), $this->moved)){
  //
}else{
  $player->setHealth(0)
}
}

