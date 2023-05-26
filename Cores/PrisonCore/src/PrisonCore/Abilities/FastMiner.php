<?php
namespace PrisonCore\Abilities;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\event\Listener as Ability;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\entity\Effect;
use pocketmine\block\Block;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use PrisonCore\Core;
use PrisonCore\BasicLib;

class FastMiner implements Ability{
	
	 protected $mining = [14,
                                              15,
                                              57,
                                              16,
                                              21,
                                              73,
                                              74,
                                              129,
                                              1];

	 public function __construct(Core $plugin){
	      $this->core = $plugin;
		}
	 public function getCore(){
	      return $this->core;
		}
	 public function FastMine(PlayerInteractEvent $event){
	      $player = $event->getPlayer();
	      $blockId = $event->getBlock()->getId();
		   if(!$event->isCancelled() && $player->hasPermission("ability.fastmine")){
			  if(in_array($blockId, $this->mining)){
			    $ability = Effect::getEffect(Effect::HASTE);
			    $ability->setDuration(20 * 4);
			    $ability->setAmplifier(rand(2, 6));
			    $player->addEffect($ability);
            //$player->sendTitle("§bAbilities", "§aFastMiner active!");
		}
	}
}
}