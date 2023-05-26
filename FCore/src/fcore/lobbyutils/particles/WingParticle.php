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
class WingParticle extends RunningParticle implements Particle {

    public $tick = 0;

    /** @var array $players */
    public $players = [];

    public $layout;

    public $strlenMap;

    /** @var LobbyUtilsManager $plugin */
    public $plugin;

    public function __construct(LobbyUtilsManager $plugin) {
        $this->plugin = $plugin;
        $l ="PXXXXXXXXXXXP\n".
            "PPXXXXXXXXXPP\n".
            "XPPXXXXXXXPPX\n".
            "XXPPXXXXXPPXX\n".
            "XXXPPXXXPPXXX\n".
            "XXPPPXXXPPPXX\n".
            "XXPPPPXPPPPXX\n".
            "XXXPPPXPPPXXX\n".
            "XXXPPPXPPPPXX\n".
            "XPPPPXXXPPPXX\n".
            "XXPXXXXXXXPXX\n";
        $this->layout = explode("\n", $l);
        foreach($this->layout as $key => $model){
            $this->strlenMap[$key] = strlen($this->layout[$key]);
        }
    }

    public function getLayout() {
        return $this->layout;
    }

    public function getName(): string {
        return "Wing";
    }

    public function getImage(): string {
        return "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Ghast-icon.png";
    }

    /**
     * @return int
     */
    public function getCost(): int {
        return 10000;
    }

    /**
     * @return bool
     */
    public function isFree(): bool {
        return false;
    }

    public function equip(Player $player) {
        if (ProfileManager::hasParticle($player, "wing") || ProfileManager::isVip($player)) {

            if(isset($this->players[$player->getName()])) {
                unset($this->players[$player->getName()]);
                $player->sendMessage("§a> You are removed particle §7Wing§a!");
            }
            else{
                $this->players[$player->getName()] = $player;
                $player->sendMessage("§a> You are equipped particle §7Wing§a!");
            }
        } else {
            $player->sendMessage("§c> You need to buy this particle first!");
        }
    }

    public function buy(Player $player) {
        if(ProfileManager::hasParticle($player, "wing")) {
            $player->sendMessage("§6> You are have already bought this particle!");
        }
        else {
            if(!ProfileManager::hasCoins($player, $this->getCost())) {
                $player->sendMessage("§c> You need more coins to buy this!");
                return;
            }
            ProfileManager::buyParticle($player, "wing", $this->getCost());
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
            $value = ProfileManager::hasParticle($player, "wing") ? "§aEQUIP" : "§bBUY";
            if($vip) {
                $value = "§aEQUIP";
            }
            return new Button("§6Wing  $value", $this->getImage(), "url");
        }
        else {
            $value = ProfileManager::hasParticle($player, "wing") ? "§aYou are already bought this particle!" : "§7{$this->getCost()} §cBUY";
            return new Button("§6 Wing  $value", $this->getImage(), "url");
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
        $layout = $this->getLayout();
        $sp = 0.25;
        $mb = 0.25;
        // byly tu 2 místo 3
        $y = $player->getY() + 3;
        $yaw = $player->getYaw(); /* 0-360 DEGREES */

        //if($model->getCenterMode() == 0){
        $svp = (max($this->strlenMap) * $sp / 2) - $sp / 2;
        //}
        #$pM = $model->getParticleMap();
        #$fp = $model->getParticle();
        /* moving to back */
        $bx = cos(($yaw - 90) * M_PI / 180) * $mb;
        $bz = sin(($yaw - 90) * M_PI / 180) * $mb;
        /* roatating to match players back */
        $cosR = cos($yaw * -1 * M_PI / 180);
        $sinR = sin($yaw * -1 * M_PI / 180);
        foreach ($layout as $layer) {
            $y -= $sp;
            /*if($model->getCenterMode() == 1){
                $vp = (strlen($layer) * $sp / 2) - $sp / 2;
            }else{*/
            $vp = $svp;
            //}
            for ($verticalPos = strlen($layer) - 1; $verticalPos >= 0; $verticalPos--) {
                if ($layer[$verticalPos] !== "X") {
                    $rx = $vp * $cosR;
                    $rz = -$vp * $sinR;
                    /*if($pM !== []){
                        $fp = $pM[$layer[$verticalPos]];
                    }*/
                    $particleObject = new GenericParticle(new Vector3($player->getX() + $rx + $bx, $y, $player->getZ() + $rz + $bz), \pocketmine\level\particle\Particle::TYPE_FLAME);
                    $player->getLevel()->addParticle($particleObject);
                }
                $vp -= $sp;
            }
        }
    }
}