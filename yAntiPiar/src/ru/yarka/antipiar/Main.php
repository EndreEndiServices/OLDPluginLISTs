<?php

namespace ru\yarka\antipiar;

use pocketmine\plugin\PluginBase;
use pocketmine\event\{Listener, player\PlayerChatEvent};

class Main extends PluginBase implements Listener {

 public function onEnable()
 {
  $this->getServer()->getPluginManager()->registerEvents($this, $this);
 }
  
 public function onChat(PlayerChatEvent $e)
 {
  foreach(scandir("plugins/yAntiPiar/") as $file){
  $cfg = new Config("plugins/yAntiPiar/".$file);
  $e->setMessage(preg_replace("/[а-яa-zA-Z0-9\._-]+\.(ru|com|cc|net|org|рф|pro)/i", "***", $msg));
  unset($cfg);
  }
 }

}