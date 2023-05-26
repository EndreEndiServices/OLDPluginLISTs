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
use StockPE\Blood;

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

class BloodFX implements Listener {

  public function getBloodFX(Entity $entity, $amplifier)
    {
		  $amplifier = (int) round($amplifier / 15);
		  for($i = 0; $i <= $amplifier; $i ++)
        {
		  	  $entity->getLevel()->addParticle(new DestroyBlockParticle(new Vector3($entity->x, $entity->y, $entity->z)));
        }
		  return true;
	  }

}
?>