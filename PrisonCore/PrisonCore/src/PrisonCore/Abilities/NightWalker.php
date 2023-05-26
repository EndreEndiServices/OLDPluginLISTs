<?php
namespace PrisonCore\Abilities;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\event\Listener as Ability;
use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\level\Level;

use pocketmine\entity\Effect;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use PrisonCore\Core;
use PrisonCore\BasicLib;

class NightWalker implements Ability{
	 public function __construct(Core $plugin){
	      $this->core = $plugin;
		}
	 public function getCore(){
	      return $this->core;
		}
	 public function nightWalk(PlayerMoveEvent $event){
	      $player = $event->getPlayer();
	      $level = $player->getLevel();
		   if($player->hasPermission("ability.nightwalk") && $level->getTime() >= 12000){
		      $ability = Effect::getEffect(16);
		      $ability->setDuration(20 * 16);
		      $player->addEffect($ability);
		}
   }
}