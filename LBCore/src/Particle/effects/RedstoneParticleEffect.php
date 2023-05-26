<?php

namespace Particle\effects;

use pocketmine\level\particle\RedstoneParticle;

/**
 * Describes math of redstone effect
 */
class RedstoneParticleEffect implements ParticleEffect {

	const PRODUCT_ID = 7;

	/**
	 * Save effect counter in player's data
	 * @param CustomPlayer $player
	 */
	public function select($player) {
		$player->particleEffectExtra["i"] = 0;
	}

	/**
	 * Show the particle effect 
	 * 
	 * @param int $currentTick
	 * @param CustomPlayer $player
	 * @param Array|NULL $showTo
	 */
	public function tick($currentTick, $player, $showTo) {
		if ($player->getLastMove() < $currentTick - 5) {
			// idle particles
			$n = $player->particleEffectExtra["i"] ++;

			$v = 2 * M_PI / 120 * ($n % 120);
			$i = 2 * M_PI / 70 * ($n % 70);
			$x = cos($i);
			$y = cos($v);
			$z = sin($i);

			$player->getLevel()->addParticle(new RedstoneParticle($player->add($x, 1 - $y, -$z)), $showTo);
			$player->getLevel()->addParticle(new RedstoneParticle($player->add(-$x, 1 - $y, $z)), $showTo);
		} else {
			// move particles
			if ($player->particleEffectExtra["i"] !== 0) {
				$player->particleEffectExtra["i"] = 0;
			}

			$distance = -0.5 + lcg_value();
			$yaw = $player->yaw * M_PI / 180;
			$x = $distance * cos($yaw);
			$z = $distance * sin($yaw);
			$y = lcg_value() * 0.4;
			$player->getLevel()->addParticle(new RedstoneParticle($player->add($x, $y, $z)), $showTo);
		}
	}

}
