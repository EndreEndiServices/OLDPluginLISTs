<?php

namespace Particle\effects;

use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\PortalParticle;

/**
 * Describes math of portal effect
 */
class PortalParticleEffect implements ParticleEffect {

	const PRODUCT_ID = 9;

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
		$player->getLevel()->addParticle(new DustParticle($player->add(-0.5 + lcg_value(), 1.5 + lcg_value() / 2, -0.5 + lcg_value()), 255, 0, 255), $showTo);
		$player->getLevel()->addParticle(new DustParticle($player->add(-0.5 + lcg_value(), 1.5 + lcg_value() / 2, -0.5 + lcg_value()), 255, 0, 255), $showTo);
		$player->getLevel()->addParticle(new PortalParticle($player->add(-0.5 + lcg_value(), 0.5 + lcg_value(), -0.5 + lcg_value())), $showTo);
		$player->getLevel()->addParticle(new PortalParticle($player->add(-0.5 + lcg_value(), 0.5 + lcg_value(), -0.5 + lcg_value())), $showTo);
	}

}
