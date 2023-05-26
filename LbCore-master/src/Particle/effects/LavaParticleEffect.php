<?php

namespace Particle\effects;

use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\LavaParticle;

/**
 * Describes lava effect math
 */
class LavaParticleEffect implements ParticleEffect {

	const PRODUCT_ID = 8;

	public function select($player) {
		//
	}

	/**
	 * Show the particle effect 
	 * 
	 * @param int $currentTick
	 * @param CustomPlayer $player
	 * @param Array|NULL $showTo
	 */
	public function tick($currentTick, $player, $showTo) {
		$player->getLevel()->addParticle(new LavaParticle($player->add(0, 1 + lcg_value(), 0)), $showTo);

		if ($player->getLastMove() >= $currentTick - 5) {
			$distance = -0.5 + lcg_value();
			$yaw = $player->yaw * M_PI / 180;
			$x = $distance * cos($yaw);
			$z = $distance * sin($yaw);
			$player->getLevel()->addParticle(new LavaDripParticle($player->add($x, 0.2, $z)), $showTo);
		}
	}

}
