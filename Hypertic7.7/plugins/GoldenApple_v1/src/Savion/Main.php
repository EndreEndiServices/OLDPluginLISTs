<?php

namespace Savion;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\entity\Effect;

class Main extends PluginBase implements Listener {

public function onEnable(){
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getServer()->getLogger()->info("Activated");
}

public function eat(PlayerItemConsumeEvent $ev){

   $p=$ev->getPlayer();

   if($ev->getItem()->getId() === 322){

             $p->addEffect(Effect::getEffect(10)->setAmplifier(3)->setDuration(200)->setVisible(false));
             $p->addEffect(Effect::getEffect(21)->setAmplifier(0)->setDuration(1000)->setVisible(false));
             $p->setHealth($p->getHealth() + 6);

    }
 }
}
