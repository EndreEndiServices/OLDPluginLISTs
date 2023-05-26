<?php

namespace Particle\effects;

/**
 * common interface for all particle effects
 * select() save options counter in player's data if needs
 * tick() make repeatable math magic
 */
interface ParticleEffect {

	public function select($player);

	public function tick($currentTick, $player, $showTo);
}
