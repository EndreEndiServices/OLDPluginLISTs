<?php

namespace Border;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\utils\TextFormat as C;
use KingdomCore\Main;

class BorderListener extends PluginBase implements Listener {

   protected $plugin;

   public function __construct(Main $plugin){
       $this->plugin = $plugin;
   }

   public function onBorderHub(PlayerMoveEvent $event){
       $player = $event->getPlayer();
       $x = round($player->getX());
       $y = round($player->getY());
       $z = round($player->getZ());
   if(($x >= 200 || $x <= 115) || ($y >= 79 || $y <= 64) || ($z >= 99 || $z <= 33) and $player->getLevel()->getName() == "hub"){
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn()); 
       $player->sendPopup(C::RED ."Woah You can't leave Spawn!");
    }
   }
}
