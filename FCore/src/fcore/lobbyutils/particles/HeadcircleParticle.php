<?php

declare(strict_types=1);

namespace fcore\lobbyutils\particles;

use fcore\form\Button;
use fcore\lobbyutils\LobbyUtilsManager;
use fcore\profile\ProfileManager;
use pocketmine\level\particle\GenericParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;

class HeadcircleParticle extends RunningParticle implements Particle {

    public $tick = 0;

    /** @var array $players */
    public $players = [];

    /** @var LobbyUtilsManager $plugin */
    public $plugin;

    public function __construct(LobbyUtilsManager $plugin) {
        $this->plugin = $plugin;
    }

    public function getName(): string {
        return "Headcircle";
    }

    public function getImage(): string {
        return "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Ghast-icon.png";
    }

    /**
     * @return int
     */
    public function getCost(): int {
        return 1000;
    }

    /**
     * @return bool
     */
    public function isFree(): bool {
        return false;
    }

    public function equip(Player $player) {
        if (ProfileManager::hasParticle($player, "headcircle") || ProfileManager::isVip($player)) {

            if(isset($this->players[$player->getName()])) {
                unset($this->players[$player->getName()]);
                $player->sendMessage("§a> You are removed particle §7Headcircle§a!");
            }
            else{
                $this->players[$player->getName()] = $player;
                $player->sendMessage("§a> You are equipped particle §7Headcircle§a!");
            }
        } else {
            $player->sendMessage("§c> You need to buy this particle first!");
        }
    }

    public function buy(Player $player) {
        if(ProfileManager::hasParticle($player, "headcircle")) {
            $player->sendMessage("§6> You are have already bought this particle!");
        }
        else {
            if(!ProfileManager::hasCoins($player, $this->getCost())) {
                $player->sendMessage("§c> You need more coins to buy this!");
                return;
            }
            ProfileManager::buyParticle($player, "headcircle", $this->getCost());
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
            $value = ProfileManager::hasParticle($player, "headcircle") ? "§aEQUIP" : "§bBUY";
            if($vip) {
                $value = "§aEQUIP";
            }
            return new Button("§6Headcircle  $value", $this->getImage(), "url");
        }
        else {
            $value = ProfileManager::hasParticle($player, "headcircle") ? "§aYou are already bought this particle!" : "§7{$this->getCost()} §cBUY";
            return new Button("§6Headcircle  $value", $this->getImage(), "url");
        }
    }

    public function run() {
        if(count($this->players) == 0) return;

        /** @var Player $player */
        foreach ($this->players as $player) {
            if($player instanceof Player && $player->isOnline()) {
                $this->draw($player);
            }
        }
    }

    public function draw(Player $player) {
        $this->tick++;
        if($this->tick%5 !== 0) {
            return;
        }


        $ri = 25; //rotation increase per render (degrees)
        $t = 0.6; //diameter
        $y = $player->getY() + 2;

        //$rot = $model->getRuntimeData("rot");
        //$rot =  abs($this->tick % 360 - 360);
        $rot =  abs($this->tick % 360 - 180);
        if($rot === null){
            $rot = 0;
        }
        $rot += $ri;
        if($rot > 360){
            $rot = $ri;
        }

        $rotRad = $rot * M_PI/180;
        $rx = -sin($rotRad) * $t;
        $rz = cos($rotRad) * $t;
        $fx = $player->getX() + $rx;
        $fz = $player->getZ() + $rz;
        $particleObject = new GenericParticle(new Vector3($fx, $y, $fz), 17);
        $player->getLevel()->addParticle($particleObject);
    }
}
