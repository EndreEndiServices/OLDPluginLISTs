<?php

declare(strict_types=1);

namespace fcore\lobbyutils\gadgets;

use pocketmine\Player;

/**
 * Class EquipGadget
 * @package fcore\lobbyutils\gadgets
 */
abstract class EquipGadget {

    /** @var array $players */
    public $players = [];

    /**
     * @param Player $player
     */
    public function switchGadget(Player $player) {
        $this->players[$player->getName()] = isset($this->players[$player->getName()]) ? !$this->players[$player->getName()] : true;
    }

}