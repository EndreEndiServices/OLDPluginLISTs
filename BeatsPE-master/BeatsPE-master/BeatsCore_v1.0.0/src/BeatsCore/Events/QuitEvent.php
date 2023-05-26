<?php

namespace BeatsCore\Events;

use pocketmine\Player;
use pocketmine\event\{Listener, player\PlayerQuitEvent};

use BeatsCore\Core;

class QuitEvent implements Listener{

  private $plugin;

  public function __construct(Core $plugin){
    $this->plugin = $plugin;
  }

  public function onQuit(PlayerQuitEvent $e){
    $player = $e->getPlayer();
    $name = $player->getName();
    $e->setQuitMessage("§8[§c-§8] §b$name §eleft the server!");
  }
}