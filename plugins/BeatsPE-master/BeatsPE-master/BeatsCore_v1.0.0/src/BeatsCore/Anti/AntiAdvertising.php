<?php

namespace BeatsCore\Anti;

use pocketmine\Player;
use pocketmine\event\{Listener, player\PlayerChatEvent};

use BeatsCore\Core;

class AntiAdvertising implements Listener{

  private $plugin, $links;
  
  public function __construct(Core $plugin){
    $this->plugin = $plugin;
    $this->links = [".leet.cc", ".net", ".com", ".us", ".co", ".co.uk", ".ddns", ".ddns.net", ".cf", ".me", ".cc", ".ru", ".eu", ".tk", ".gq", ".ga", ".ml", ".org", ".1", ".2", ".3", ".4", ".5", ".6", ".7", ".8", ".9", "nethergames", "NG"];
  }

  public function onChat(PlayerChatEvent $event){
    $msg = $event->getMessage();
    $player = $event->getPlayer();
      foreach($this->links as $links){
        if(strpos($msg, $links) !== false){
          $player->sendMessage("§l§dBeats§bChat §8»§r §cDo not advertise, or you might get banned!");
          $event->setCancelled();
          return;
      }
    } 
  }
}