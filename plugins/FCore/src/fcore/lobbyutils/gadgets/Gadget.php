<?php

declare(strict_types=1);

namespace fcore\lobbyutils\gadgets;
use fcore\form\Button;
use pocketmine\Player;

/**
 * Interface Gadget
 * @package fcore\lobbyutils\gadgets
 */
interface Gadget {

    /**
     * @return string $name
     */
    public function getName():string;

    /**
     * @return int $cost
     */
    public function getCost():int;

    /**
     * @return string $image
     */
    public function getImage():string;

    /**
     * @return bool $vip
     */
    public function isOnlyForVip():bool;

    /**
     * @return bool $stackable
     */
    public function isStackable():bool;

    /**
     * @param Player $player
     */
    public function equip(Player $player);

    /**
     * @api
     *
     * @param Player $player
     * @param bool $vip
     *
     * @return Button
     */
    public function constructButton(Player $player, string $type, bool $vip = false):Button;

    /**
     * @param Player $player
     */
    public function buy(Player $player);
}