<?php

namespace BeatsCore\Anti;

use pocketmine\Player;
use pocketmine\event\{Listener, player\PlayerChatEvent};

use BeatsCore\Core;

class AntiSwearing implements Listener{

  private $plugin, $badwords;
  
  public function __construct(Core $plugin){
    $this->plugin = $plugin;
    $this->badwords = ["anal", "anus", "ass", "bastard", "bitch", "boob", "cock", "cum", "cunt", "dick", "dildo", "dyke", "fag", "faggot", "fuck", "fuk", "fk", "hoe", "tits", "whore", "handjob", "homo", "jizz", "cunt", "kike", "kunt", "muff", "nigger", "penis", "pussy", "queer", "rape", "semen", "sex", "shit", "slut", "titties", "twat", "vagina", "vulva", "wank", "FUCK", "BITCH", "FAGGOT", "DICK", "CUNT", "ASS", "nigger", "nigga"];
  }

  public function onChat(PlayerChatEvent $event){
    $msg = $event->getMessage();
    $player = $event->getPlayer();
      foreach($this->badwords as $badwords){
        if(strpos($msg, $badwords) !== false){
          $player->sendMessage("§l§dBeats§bChat §8»§r §cDo not swear, or you might get banned!");
          $event->setCancelled();
          return;
      }
    } 
  }
}