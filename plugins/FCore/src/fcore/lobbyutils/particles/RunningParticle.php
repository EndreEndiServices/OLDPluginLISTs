<?php

declare(strict_types=1);

namespace fcore\lobbyutils\particles;

use pocketmine\Player;

/**
 * Class RunningParticle
 * @package fcore\lobbyutils\particles
 */
abstract class RunningParticle {

    /** @var array $players */
    public $players = [];

    /**
     * @param Player $player
     */
    public function switchParticle(Player $player) {
        $this->players[$player->getName()] = isset($this->players[$player->getName()]) ?! $this->players[$player->getName()] : true;
    }

    /**
     * Task function
     */
    abstract public function run();
}