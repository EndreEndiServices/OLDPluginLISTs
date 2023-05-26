<?php

declare(strict_types=1);

namespace fcore\lobbyutils\gadgets;

use fcore\form\Button;
use fcore\profile\ProfileManager;
use pocketmine\block\Block;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;

/**
 * Class TNTGadget
 * @package fcore\lobbyutils\gadgets
 */
class TNTGadget extends RunningGadget implements Gadget {

    public $players = [];

    /**
     * @return string
     */
    public function getName(): string {
        return "TNT";
    }

    /**
     * @return int
     */
    public function getCost(): int {
        return 100;
    }

    public function getImage(): string {
        return "http://icons.iconarchive.com/icons/chrisl21/minecraft/512/Tnt-icon.png";
    }

    public function isOnlyForVip(): bool {
        return false;
    }

    public function buy(Player $player) {
        $coins = ProfileManager::$players[$player->getName()]["coins"]-$this->getCost();
        if($coins >= 0) {
            ProfileManager::$players[$player->getName()]["coins"] =
            $c = ProfileManager::$players[$player->getName()]["gadgets"]["tnt"][1]+1;
            ProfileManager::$players[$player->getName()]["gadgets"]["tnt"] = [true, $c];
        }
        else {
            $player->sendMessage("§c> You have not too much money to buy this!");
        }
    }

    public function equip(Player $player) {
        if(!(ProfileManager::getPlayerGadgetsCount($player, "tnt") >= 1 || ProfileManager::isVip($player))) {
            $player->sendMessage("§c> Buy this gadget first!");
            return;
        }
        ProfileManager::removeGadget($player, "tnt", true);

        $pos = clone $player->asPosition();

        $this->players[$player->getName()] = [
            "pos" => $pos,
            "blockId" => $pos->getLevel()->getBlock($pos)->getId(),
            "blockDmg" => $pos->getLevel()->getBlock($pos)->getDamage(),
            "tick" => 60
        ];

        $pos->getLevel()->setBlock($pos, Block::get(Block::TNT));

        $player->sendMessage("§a> TNT Spawned!");
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
            $value = ProfileManager::hasGadget($player, "tnt") ? "§aEQUIP - ".ProfileManager::getPlayerGadgetsCount($player, "tnt") ."x": "§cBUY";
            if($vip) {
                $value = "§aEQUIP - 999";
            }
            return new Button("§6TNT  $value", $this->getImage(), "url");
        }
        else {
            $value = ProfileManager::hasGadget($player, "tnt") ? "§7{$this->getCost()} §6BUY" : "§7{$this->getCost()} §cBUY";
            return new Button("§6TNT  $value", $this->getImage(), "url");
        }
    }

    public function run() {
        if (count($this->players) == 0) {
            return;
        }

        foreach ($this->players as $name => $data) {
            if($data["tick"] === 0) {
                /** @var Position $pos */
                $pos = $data["pos"];

                $entities = $pos->getLevel()->getNearbyEntities(new AxisAlignedBB($pos->getX()-3, $pos->getY()-2, $pos->getZ()-3, $pos->getX()+3, $pos->getY()+4, $pos->getZ()+3));

                foreach ($entities as $entity) {
                    if($entity instanceof Player) {
                        $entity->knockBack($entity, 0, rand(-2,2), rand(-2,2), rand(3,4));
                    }
                }

                $pos->getLevel()->setBlock($pos, Block::get($data["blockId"], $data["blockDmg"]));
                $pos->getLevel()->addParticle(new HugeExplodeParticle($pos));

                unset($this->players[$name]);
            }
            else {
                $this->players[$name]["tick"]--;
            }
        }
    }

    public function isStackable(): bool {
        return true;
    }
}