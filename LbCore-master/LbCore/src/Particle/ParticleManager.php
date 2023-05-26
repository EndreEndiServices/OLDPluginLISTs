<?php

namespace Particle;

use Particle\effects\LavaParticleEffect;
use Particle\effects\ParticleEffect;
use Particle\effects\PortalParticleEffect;
use Particle\effects\RainbowParticleEffect;
use Particle\effects\RedstoneParticleEffect;
use LbCore\LbCore;

/**
 * Particle manager class
 */
class ParticleManager {

	/** @var LavaParticleEffect */
	public static $lava;
	/** @var RedstoneParticleEffect */
	public static $redstone;
	/** @var PortalParticleEffect */
	public static $portal;
	/** @var RainbowParticleEffect */
	public static $rainbow;
	/** @var Plugin */
	private $plugin;
	/** @var ParticleTask */
	private $task;

	/**
	 * Init static class properties
	 */
	public static function initParticleEffects() {
		self::$lava = new LavaParticleEffect();
		self::$redstone = new RedstoneParticleEffect();
		self::$portal = new PortalParticleEffect();
		self::$rainbow = new RainbowParticleEffect();
	}
	
	/**
	 * Class constructor
	 * 
	 * @param Plugin $plugin
	 */
	public function __construct() {
		self::initParticleEffects();
		$this->plugin = LbCore::getInstance();
		$this->task = new ParticleTask($this->plugin);
		$this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($this->task, 3);
	}

	/**
	 * Set the particle effect to given player
	 * 
	 * @param CustomPlayer $player
	 * @param ParticleEffect $effect
	 */
	public function setPlayerParticleEffect($player, ParticleEffect $effect) {
		$this->task->setPlayerParticleEffect($player, $effect);
	}

}
