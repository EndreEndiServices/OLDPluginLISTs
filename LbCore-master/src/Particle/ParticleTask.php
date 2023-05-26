<?php

namespace Particle;

use pocketmine\scheduler\PluginTask;
use Particle\effects\ParticleEffect;
use LbCore\player\LbPlayer;

/**
 * Handle repeatable particle effects for players
 */
class ParticleTask extends PluginTask {

	/** @var Plugin */
	private $plugin;
	/** @var Array */
	private $effects = [];

	/**
	 * Class constructor
	 * 
	 * @param Plugin $plugin
	 */
	public function __construct($plugin) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}

	/**
	 * Set the particle effect to given player
	 * 
	 * @param CustomPlayer $player
	 * @param ParticleEffect $effect
	 */
	public function setPlayerParticleEffect($player, ParticleEffect $effect) {
		$player->particleEffectExtra = [];
		$this->effects[$player->getId()] = [$player, $effect];
		$effect->select($player);
	}

	/**
	 * Task start handling
	 * 
	 * @param int $currentTick
	 */
	public function onRun($currentTick) {
		foreach ($this->effects as $id => $data) {
			/** @var CustomPlayer $player */
			$player = $data[0];
			/** @var ParticleEffect $effect */
			$effect = $data[1];

			if ($player->closed) {
				unset($this->effects[$id]);
				continue;
			}

			if ($player->getState() != LbPlayer::IN_LOBBY || $player->getParticleEffectId() == 0) {
				continue;
			}

			$showTo = $player->getViewers();
			$showTo[] = $player;
			$effect->tick($currentTick, $player, $showTo);
		}
	}

}
