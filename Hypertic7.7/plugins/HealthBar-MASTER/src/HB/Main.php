<?php

namespace HB;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\entity\EntityDamageEvent;

class Main extends PluginBase implements Listener{

public function onEnable(){

$this->getServer()->getLogger()->info("[HB]HealthBar Loaded");
$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Checker($this),10); 
}


}
