<?php

declare(strict_types=1);

namespace fcore\lobbyutils\gadgets;

use fcore\form\Button;
use fcore\profile\ProfileManager;
use pocketmine\Player;

/**
 * Class FlyGadget
 * @package fcore\lobbyutils\gadgets
 */
class GiantGadget extends EquipGadget implements Gadget {

    /**
     * @return string
     */
    public function getName(): string {
        return "Giant";
    }

    /**
     * @return int
     */
    public function getCost(): int {
        return  2000;
    }

    /**
     * @return bool
     */
    public function isFree(): bool {
        return false;
    }

    /**
     * @return string
     */
    public function getImage(): string {
        return "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Gold-Hoe-icon.png";
    }

    /**
     * @return bool
     */
    public function isOnlyForVip(): bool {
        return false;
    }

    /**
     * @return bool
     */
    public function isStackable(): bool {
        return false;
    }

    /**
     * @param Player $player
     * @param string $type
     * @param bool $vip
     *
     * @return Button
     */
    public function constructButton(Player $player, string $type, bool $vip = false): Button {
        if($type == "use") {
            $value = ProfileManager::hasGadget($player, "giant") ? "§aEQUIP" : "§cBUY";
            if($vip) {
                $value = "§aEQUIP";
            }
            return new Button("§6Giant  $value", $this->getImage(), "url");
        }
        else {
            $value = ProfileManager::hasGadget($player, "giant") ? "§aYou are already bought this gadget!" : "§7{$this->getCost()} §cBUY";
            return new Button("§6Giant  $value", $this->getImage(), "url");
        }
    }

    /**
     * @param Player $player
     * @return mixed|void
     */
    public function equip(Player $player) {
        if(ProfileManager::isVip($player) || ProfileManager::hasGadget($player, "giant") || ProfileManager::getPlayerRank($player) == "youtuber") {
            $bool = $player->getScale() == 1;
            $mode = $bool ? "enabled" : "disabled";
            $player->sendMessage("§a> Giant gadget $mode!");
            $newScale = $bool ? 2 : 1;
            $player->setScale($newScale);
        }
        else {
            $player->sendMessage("§cBuy this gadget first!");
        }
    }

    public function buy(Player $player) {
        $coins = ProfileManager::$players[$player->getName()]["coins"] - $this->getCost();
        if($coins >= 0) {
            ProfileManager::$players[$player->getName()]["coins"] = $coins;
            $c = ProfileManager::$players[$player->getName()]["gadgets"]["giant"][1] + 1;
            ProfileManager::$players[$player->getName()]["gadgets"]["giant"] = [true, $c];
            $player->sendMessage("§a> You are successfully bought giant gadget!");
        }
        else {
            $player->sendMessage("§c> You have not too much money to buy this!");
        }
    }
}
