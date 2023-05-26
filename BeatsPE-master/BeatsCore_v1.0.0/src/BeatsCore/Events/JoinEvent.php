<?php

namespace BeatsCore\Events;

use pocketmine\Player;
use pocketmine\event\{Listener, player\PlayerJoinEvent};

use BeatsCore\{Core, Title};

class JoinEvent implements Listener{

  private $plugin;

  public function __construct(Core $plugin){
    $this->plugin = $plugin;
  }

  public function onJoin(PlayerJoinEvent $e){
  	$player = $e->getPlayer();
  	$name = $player->getName();
  	$e->setJoinMessage("§8[§a+§8] §b$name §ejoined the server!");
  	$player->sendMessage("§2=================================\n -        §l§dBeats§bPE §cOP §3Factions!§r\n§2 -         §eBeatsPE.ddns.net 19132\n§2 - \n§2 - §aStore: BeatsNetworkPE.buycraft.net\n§2 - \n§2 - §aWelcome back, §6$name!\n§2 - \n§2 - §7You're playing on OP Factions!\n§2=================================");
  	#$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new Title($this, $player), 30);
  }
}