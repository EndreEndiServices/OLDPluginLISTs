<?php

namespace PrestigeSociety\CombatLogger;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class CombatLoggerListener implements Listener {

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
	 * @priority MONITOR
	 *
	 * @param PlayerQuitEvent $e
	 *
	 */
	public function onQuit(PlayerQuitEvent $e){
		if($this->core->PrestigeSocietyCombatLogger->inCombat($e->getPlayer())){
			$e->getPlayer()->setHealth(0);
			$this->core->PrestigeSocietyCombatLogger()->endTime($e->getPlayer());
		}
	}

	/**
	 *
	 * @priority MONITOR
	 *
	 * @param PlayerDeathEvent $e
	 *
	 */
	public function onDeath(PlayerDeathEvent $e){
		$this->core->PrestigeSocietyCombatLogger()->endTime($e->getPlayer());
	}

	/**
	 *
	 * @priority LOWEST
	 *
	 * @param PlayerCommandPreprocessEvent $e
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function onCmd(PlayerCommandPreprocessEvent $e){
		if($e->getMessage()[0] !== '/'){
			return;
		}

		if($this->core->PrestigeSocietyCombatLogger->inCombat($e->getPlayer()) and !$e->getPlayer()->hasPermission('pl.cl.admin')){
			$e->getPlayer()->sendMessage(RandomUtils::colorMessage($this->core->getMessage('combat_logger', 'no_commands')));
			$e->setCancelled();
		}
	}


	/**
	 *
	 * @priority MONITOR
	 *
	 * @param EntityDamageEvent $e
	 *
	 */
	public function onDamage(EntityDamageEvent $e){
		$cause = $e->getEntity()->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent){

			$player = $e->getEntity();
			$damager = $cause->getDamager();

			if($damager instanceof Player and $player instanceof Player){

				if($this->core->PrestigeSocietyStaffMode->isInStaffMode($damager)) return;

				if($this->core->FunBox->isLSDEnabled($damager)){
					$this->core->FunBox->toggleLSD($damager);
				}

				if($this->core->FunBox->isGodEnabled($damager)){
					$this->core->FunBox->toggleGod($damager);
				}

				$this->core->PrestigeSocietyCombatLogger()->
				checkTime($player, 10);
			}
		}
	}
}