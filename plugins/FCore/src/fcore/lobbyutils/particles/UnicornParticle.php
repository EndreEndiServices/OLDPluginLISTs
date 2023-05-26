<?php

declare(strict_types=1);

namespace fcore\lobbyutils\particles;
use fcore\FCore;
use fcore\form\Button;
use fcore\lobbyutils\LobbyUtilsManager;
use fcore\profile\ProfileManager;
use pocketmine\level\particle\DustParticle;
use pocketmine\Player;

/**
 * Class UnicornParticle
 * @package fcore\lobbyutils\particles
 */
class UnicornParticle extends RunningParticle implements Particle {

    /** @var LobbyUtilsManager $plugin */
    public $plugin;

    public $players = [];

    /**
     * UnicornParticle constructor.
     * @param LobbyUtilsManager $plugin
     */
    public function __construct(LobbyUtilsManager $plugin) {
        $this->plugin = $plugin;
    }

    public function getName(): string {
        return "Unicorn";
    }

    public function getCost(): int {
        return 2000;
    }

    public function isFree(): bool {
        return false;
    }

    public function getImage(): string {
        return "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Cake-icon.png";
    }

    public function equip(Player $player) {
        if (ProfileManager::hasParticle($player, "unicorn") || ProfileManager::isVip($player)) {

            if(isset($this->players[$player->getName()])) {
                unset($this->players[$player->getName()]);
                $player->sendMessage("§a> You are removed particle §7Unicorn§a!");
            }
            else{
                $this->players[$player->getName()] = $player;
                $player->sendMessage("§a> You are equipped particle §7Unicorn§a!");
            }
        } else {
            $player->sendMessage("§c> You need to buy this particle first!");
        }
    }

    public function buy(Player $player) {
        if(ProfileManager::hasParticle($player, "unicorn")) {
            $player->sendMessage("§6> You are have already bought this particle!");
        }
        else {
            if(!ProfileManager::hasCoins($player, $this->getCost())) {
                $player->sendMessage("§c> You need more coins to buy this!");
                return;
            }
            ProfileManager::buyParticle($player, "unicorn", $this->getCost());
            $player->sendMessage("§a> You are successfully bought this particle!");
        }
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
            $value = ProfileManager::hasParticle($player, "unicorn") ? "§aEQUIP" : "§bBUY";
            if($vip) {
                $value = "§aEQUIP";
            }
            return new Button("§6Unicorn  $value", $this->getImage(), "url");
        }
        else {
            $value = ProfileManager::hasParticle($player, "unicorn") ? "§aYou are already bought this particle!" : "§7{$this->getCost()} §cBUY";
            return new Button("§6Unicorn  $value", $this->getImage(), "url");
        }
    }


    public function run() {
        $players = $this->players;
        /** @var Player $player */
        foreach ($players as $player) {
            $player->getLevel()->addParticle(new DustParticle($player->add(0, 1.5), 255, 0, 0));
            $player->getLevel()->addParticle(new DustParticle($player->add(0, 1.3), 255, 0, 255));
            $player->getLevel()->addParticle(new DustParticle($player->add(0, 1.1), 0, 0, 255));
            $player->getLevel()->addParticle(new DustParticle($player->add(0, 0.9), 0, 255, 0));
            $player->getLevel()->addParticle(new DustParticle($player->add(0, 0.7), 255, 255, 0));
            $player->getLevel()->addParticle(new DustParticle($player->add(0, 0.5), 0, 255, 255));
        }
    }
}