<?php

declare(strict_types=1);

namespace fcore\pvp\kit;
use pocketmine\Player;

/**
 * Interface Kit
 * @package fcore\lobbyutils\gadget
 */
interface Kit {

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
    public function isOnlyForVip():bool;

    /**
     * @return bool
     */
    public function isFree():bool ;

    /**
     * @param Player $player
     */
    public function equip(Player $player);
}