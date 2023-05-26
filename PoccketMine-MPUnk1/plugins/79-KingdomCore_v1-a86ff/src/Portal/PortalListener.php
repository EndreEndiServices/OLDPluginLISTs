<?php

namespace Portal;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerMoveEvent;
use KingdomCore\Main;

class PortalListener extends PluginBase implements Listener {

   protected $plugin;

   public function __construct(Main $plugin){
       $this->plugin = $plugin;
   }

   public function onPortalGame(PlayerMoveEvent $event){
       $player = $event->getPlayer();
       $x = round($player->getX());
       $y = round($player->getY());
       $z = round($player->getZ());
   if(($x >= 163 and $x <= 171) and ($y >= 66 and $y <= 69) and ($z >= 35 and $z <= 35) and $player->getLevel()->getName() == "hub"){
       $this->plugin->gamesLobby($player); 
   }
   elseif(($x >= 198 and $x <= 199) and ($y >= 66 and $y <= 70) and ($z >= 62 and $z <= 70) and $player->getLevel()->getName() == "hub"){
       $this->plugin->parkourLobby($player); 
       }
      }
}
