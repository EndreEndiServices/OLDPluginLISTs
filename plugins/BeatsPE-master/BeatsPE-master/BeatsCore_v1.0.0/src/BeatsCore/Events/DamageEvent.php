<?php

namespace BeatsCore\Events;

use pocketmine\Player;
use pocketmine\event\{Listener, entity\EntityDamageEvent};

use BeatsCore\Core;

class DamageEvent implements Listener{

  private $plugin;

  public function __construct(Core $plugin){
    $this->plugin = $plugin;
  }

  public function onDamage(EntityDamageEvent $e){
  	if($e->getCause() === EntityDamageEvent::CAUSE_FALL){
  		$e->setCancelled();
  	}
  }
}