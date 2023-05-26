<?php
/*
  _____  __    __      _
 |___  |/ / /\ \ \  __| |  ___ __   __
    / / \ \/  \/ / / _` | / _ \\ \ / /
   / /   \  /\  / | (_| ||  __/ \ V /
  /_/     \/  \/   \__,_| \___|  \_/

                                      */

namespace StockPE;

//we need this from pocketmine xD !
use StockPE\Effect;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;

//we have to open a class , cuz pocketmine will search it to enable the plugin !
class Blood extends PluginBase implements Listener {

  //we have to enable the plugin !
  public function onEnable()
	  {
		  $this->getLogger()->info("[Enabled] by 7Wdev");
      $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }


  public function onDamage(EntityDamageEvent $event)
    {
  		if($event->isCancelled() !== false)
        {
  		    return false;
  	    }
  		$entity = $event->getEntity();
      $this->getBloodFX($entity, $event->getDamage());
    }
}
?>