<?php

declare(strict_types=1);

namespace fcore\lobbyutils\particles;

use fcore\form\Button;
use fcore\lobbyutils\LobbyUtilsManager;
use fcore\profile\ProfileManager;
use pocketmine\level\particle\GenericParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;

/**
 * Class HelixParticle
 * @package fcore\lobbyutils\particles
 */
class HelixParticle extends RunningParticle implements Particle {

    public $tick = 0;

    /** @var array $players */
    public $players = [];

    /** @var LobbyUtilsManager $plugin */
    public $plugin;

    public function __construct(LobbyUtilsManager $plugin) {
        $this->plugin = $plugin;
    }

    public function getName(): string {
        return "Helix";
    }

    public function getImage(): string {
        return "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Ghast-icon.png";
    }

    /**
     * @return int
     */
    public function getCost(): int {
        return 5000;
    }

    /**
     * @return bool
     */
    public function isFree(): bool {
        return false;
    }

    public function equip(Player $player) {
        if (ProfileManager::hasParticle($player, "helix") || ProfileManager::isVip($player)) {

            if(isset($this->players[$player->getName()])) {
                unset($this->players[$player->getName()]);
                $player->sendMessage("§a> You are removed particle §7Helix§a!");
            }
            else{
                $this->players[$player->getName()] = $player;
                $player->sendMessage("§a> You are equipped particle §7Helix§a!");
            }
        } else {
            $player->sendMessage("§c> You need to buy this particle first!");
        }
    }

    public function buy(Player $player) {
        if(ProfileManager::hasParticle($player, "helix")) {
            $player->sendMessage("§6> You are have already bought this particle!");
        }
        else {
            if(!ProfileManager::hasCoins($player, $this->getCost())) {
                $player->sendMessage("§c> You need more coins to buy this!");
                return;
            }
            ProfileManager::buyParticle($player, "helix", $this->getCost());
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
            $value = ProfileManager::hasParticle($player, "helix") ? "§aEQUIP" : "§bBUY";
            if($vip) {
                $value = "§aEQUIP";
            }
            return new Button("§6Helix  $value", $this->getImage(), "url");
        }
        else {
            $value = ProfileManager::hasParticle($player, "helix") ? "§aYou are already bought this particle!" : "§7{$this->getCost()} §cBUY";
            return new Button("§6Helix  $value", $this->getImage(), "url");
        }
    }

    public function run() {
        if(count($this->players) == 0) return;

        /** @var Player $player */
        foreach ($this->players as $player) {
            $this->draw($player);
        }
    }

    public function draw(Player $player) {
        $this->tick++;
        if($this->tick%5 !== 0) {
            return;
        }
        $h = 2; //height
        $yi = 0.02; //height increase per particle
        $t = 0.25; //lower radius
        $ti = 0.01; //radius increase per particle
        $res = 25; //particles per circle
        $yOffset = 0;
        $y = $player->getY() + $yOffset;
        $rot =  abs($this->tick % 360 - 360);
        $rotRad = $rot * M_PI / 180;
        $cos = cos($rotRad);
        $sin = sin($rotRad);
        for($yaw = 0, $cy = $y; $cy < $y + $h; $yaw += (M_PI * 2) / $res, $cy += $yi, $t += $ti){
            $diffx = -sin($yaw) * $t;
            $diffz = cos($yaw) * $t;
            $rx = $diffx * $cos + $diffz * $sin;
            $rz = -$diffx * $sin + $diffz * $cos;
            $fx = $player->getX() + $rx;
            $fz = $player->getZ() + $rz;
            $particleObject = new GenericParticle(new Vector3($fx, $cy, $fz), 7);
            $player->getLevel()->addParticle($particleObject);
        }
    }
}