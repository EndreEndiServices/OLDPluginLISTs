<?php
namespace PrisonCore\Abilities;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\event\Listener as Ability;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\entity\Effect;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use PrisonCore\Core;
use PrisonCore\BasicLib;

class SelfDefense implements Ability{
	 public function __construct(Core $plugin){
	      $this->core = $plugin;
		}
	 public function getCore(){
	      return $this->core;
		}
	 public function selfDefense(EntityDamageEvent $event){
	      $player = $event->getEntity();
		   if(!$event->isCancelled() && $player instanceof Player && $player->hasPermission("ability.selfdefense")){
			  $ability = Effect::getEffect(11);
			  $ability->setDuration(20 * 8);
			  $player->addEffect($ability);
           // $player->sendTitle("§bAbilities", "§7SelfDefense active!");
		}
	}
}