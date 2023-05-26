<?php
namespace PrisonCore\Abilities;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\event\Listener as Ability;
use pocketmine\event\player\PlayerDeathEvent;

use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

use pocketmine\entity\Effect;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use PrisonCore\Core;
use PrisonCore\BasicLib;

class SelfDestruct implements Ability{
	 public function __construct(Core $plugin){
	      $this->core = $plugin;
		}
	 public function getCore(){
	      return $this->core;
		}
	 public function selfDestruct(PlayerDeathEvent $event){
	      $player = $event->getPlayer();
		   if($player->hasPermission("ability.selfdestruct")){
		      $pos = $player->getPosition();
		      $ability = new Explosion($pos, 4);
		      $ability->explodeB();
		}
   }
}