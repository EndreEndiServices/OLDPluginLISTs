<?php

namespace PrestigeSociety\Levels;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\Server;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class LevelsListener implements Listener {

	/** @var PrestigeSocietyCore */
	private $core;

	/**
	 *
	 * CombatLoggerListener constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		$this->core = $c;
	}

	/**
	 *
	 * @param PlayerJoinEvent $event
	 *
	 */
	public function onCreation(PlayerJoinEvent $event){
		if(!$this->core->PrestigeSocietyLevels()->playerExists($event->getPlayer())){
			$this->core->PrestigeSocietyLevels()->addNewPlayer($event->getPlayer());
		}
	}

	public function onDeath(PlayerDeathEvent $event){
		$target = $event->getPlayer();
		$cause = $event->getPlayer()->getLastDamageCause();

		if($cause instanceof EntityDamageByEntityEvent){

			$killer = $cause->getDamager();
			if(($killer instanceof Player) and ($target instanceof Player)){

				$this->core->PrestigeSocietyLevels()->setKills($killer, $this->core->PrestigeSocietyLevels()->getKills($killer) + 1);
				$this->core->PrestigeSocietyLevels()->setDeaths($target, $this->core->PrestigeSocietyLevels()->getDeaths($target) + 1);

				if($this->core->PrestigeSocietyNeeded->getNecesary($killer) <= $this->core->PrestigeSocietyExperience->getExp($killer)){
					$level = $this->core->PrestigeSocietyLevels()->getLevel($killer);
					Server::getInstance()->broadcastMessage(RandomUtils::colorMessage(str_replace(['@player', '@level'], [$killer->getName(), $level],
						$this->core->getMessage('levels', 'level_up'))));
				}

			}

			return;
		}

		$this->core->PrestigeSocietyLevels()->setDeaths($target, $this->core->PrestigeSocietyLevels()->getDeaths($target) + 1);
	}
}