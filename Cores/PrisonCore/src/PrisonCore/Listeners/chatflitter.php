<?php
namespace PrisonCore\Listeners;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use PrisonCore\Core;

class chatflitter implements Listener{
	 public function __construct(Core $plugin){
	      $this->core = $plugin;
		}
	 public function getCore(){
	      return $this->core;
		}
	 public function onChat(PlayerChatEvent $event){
	      $player = $event->getPlayer();
	      $msg = strtolower($event->getMessage());
	      if($this->getCore()->containsBadWord($msg, $this->getCore()->getBadWords()) && !$player->hasPermission("flitter.bypass")){
		     $event->setCancelled();
		     $player->sendMessage("§c§l[!] §r§cMessage blocked by chat flitter");
		     }
		}
}