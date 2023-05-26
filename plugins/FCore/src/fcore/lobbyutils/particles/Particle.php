<?php

declare(strict_types=1);

namespace fcore\lobbyutils\particles;

use fcore\form\Button;
use pocketmine\Player;

/**
 * Interface Particle
 * @package fcore\lobbyutils\gadget
 */
interface Particle {

    /**
     * @return string
     */
    public function getName():string;

    /**
     * @return int
     */
    public function getCost():int;

    /**
     * @return string
     */
    public function getImage():string;

    /**
     * @return bool
     */
    public function isFree():bool;

    /**
     * @api
     *
     * @param Player $player
     */
    public function equip(Player $player);

    /**
     * @api
     *
     * @param Player $player
     * @param string $type
     * @param bool $vip
     *
     * @return Button
     */
    public function constructButton(Player $player, string $type, bool $vip = false):Button;


    /**
     * @api
     *
     * @param Player $player
     */
    public function buy(Player $player);
}