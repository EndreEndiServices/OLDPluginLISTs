<?php

namespace PrestigeSociety\AntiCheat;

use pocketmine\block\Lava;
use pocketmine\block\Water;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;

class PrestigeSocietyAntiCheat {

	/** @var int[] */
	private $damageTick = [];


	/**
	 *
	 * @param Player $p
	 *
	 * @return bool
	 *
	 */
	public function detectKillAura(Player $p){
		if(!isset($this->damageTick[$p->getName()])){
			$this->damageTick[$p->getName()] = microtime(false);

			return false;
		}
		if(isset($this->damageTick[$p->getName()]) and (microtime(1) - $this->damageTick[$p->getName()] <= 500000)){
			return true;
		}else{
			$this->damageTick[$p->getName()] = microtime(1);

			return false;
		}
	}

	/**
	 *
	 * @param Player $p
	 *
	 * @return bool
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function detectFlyingHack(Player $p){
		$pos = $p->getPosition();
		$isFlying = false;
		for($i = 0; $i < 10; ++$i){
			$pos->y -= $i;
			$block = $p->level->getBlock($pos);
			if($i == 1 and $block->isSolid()) return false;
			if(!$block->isSolid() and (!$block instanceof Water or !$block instanceof Lava) and !($block->y < 0)){
				if((!$p->isOp() or !$p->hasPermission("fly.allow")) and !$p->isCreative()){
					$isFlying = true;
				}else{
					$isFlying = false;
				}
			}else{
				$isFlying = false;
			}
		}

		return $isFlying;
	}

	/**
	 *
	 * @param EntityDamageByEntityEvent $e
	 *
	 * @return bool
	 *
	 */
	public function detectAntiKnockBack(EntityDamageByEntityEvent $e){
		$p = !$e->getEntity();
		if($p instanceof Player){
			if($e->getKnockBack() < (0.5) and !$p->isCreative()){
				return true;
			}else{
				return false;
			}
		}

		return false;
	}

	/**
	 *
	 * @param EntityDamageEvent $e
	 *
	 * @return bool
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function detectOneHitKill(EntityDamageEvent $e){
		$p = $e->getEntity();
		$hasOHK = false;
		if($p instanceof Player){
			foreach($p->getEffects() as $efc){
				if($e->getFinalDamage() > 19.5 and
					(!$p->isOp() or !$p->hasPermission("onehit.allow") or
						($efc->getId() == Effect::STRENGTH and $efc->getAmplifier() < 3))){
					$hasOHK = true;
				}
			}
		}

		return $hasOHK;
	}
	/*public function detectSpeedHack(){
		//TODO
	}*/
}