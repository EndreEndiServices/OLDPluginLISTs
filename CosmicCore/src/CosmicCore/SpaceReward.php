<?php
namespace CosmicCore;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat as TF;

class SpaceReward
{

    public function __construct(CosmicCore $plugin)
    {
        $this->plugin = $plugin;
    }

    public function cashReward($p)
    {
        $arrayCash = array(1000, 1500, 2000, 2000, 3000, 4500, 2000, 1000000);
        $money = $this->plugin->reward->getRandomKey($arrayCash);
        $cash = Item::get(Item::PAPER, $money, 1);
        $cash->setCustomName(TF::AQUA . TF::BOLD . "Cosmic Note" . TF::RESET . TF::GRAY . " (Right Click)\n" . TF::LIGHT_PURPLE . "Value " . TF::WHITE . "$" . $cash->getDamage() . "\n" . TF::LIGHT_PURPLE . "Signer " . TF::GRAY . "Space Chest");
        $p->getInventory()->addItem($cash);
    }

    public function mineralReward($p)
    {
        $smallCount = mt_rand(1, 8);
        $bigCount = mt_rand(32, 64);
        $mineralArray = array(22, 41, 42, 57, 173, 41);
        $mineralRand = $this->plugin->reward->getRandomKey($mineralArray);
        $itemCount = mt_rand(1, 4);
        $p->getInventory()->addItem(Item::get($mineralRand, 0, $itemCount));
    }

    public function baseArmourReward($p)
    {
        $armor = array(306, 307, 308, 309, 314, 315, 316, 317);
        $armorR = $this->getRandomKey($armor);
        $vanillaArmor = Item::get($armorR, 0, 1);
        $names = array("Mighty Armor Of Poofless", "Mighty Armor Of Preston", "Pleb Armor Of Poofless", "Pleb Armor Of Preston", "Preston's Armor", "Poofless's Armor", "Pleb Gear", "The Vanilla Gear");
        $name = $this->plugin->reward->getRandomKey($names);
        $vanillaArmor->setCustomName(TF::YELLOW . $name);
        if ($armorR == 306 || $armorR == 314) $armorType = "helmet";
        if ($armorR == 307 || $armorR == 315) $armorType = "chestplate";
        if ($armorR == 308 || $armorR == 316) $armorType = "leggings";
        if ($armorR == 309 || $armorR == 317) $armorType = "boots";
        foreach ($this->plugin->reward->getVanillaEnchants($armorType) as $ae) {
            $this->plugin->reward->ce($vanillaArmor, $ae);
        }
        $p->getInventory()->addItem($vanillaArmor);
    }

    public function baseWeaponReward($p)
    {
        $swords = array(258, 279, 267, 276);
        $swordR = $this->plugin->reward->getRandomKey($swords);
        $vanillaSword = Item::get($swordR, 0, 1);
        $names = array("Mighty Sword Of Poofless", "Mighty Weapon Of Preston", "Pleb Weapon Of Poofless", "Pleb Weapon Of Preston", "Preston's Weapon", "Poofless's Weapon", "A Vanilla Weapon");
        $name = $this->plugin->reward->getRandomKey($names);
        $vanillaSword->setCustomName(TF::YELLOW . $name);
        foreach ($this->plugin->reward->getVanillaEnchants("sword") as $se) {
            $this->plugin->reward->ce($vanillaSword, $se);
        }
        $p->getInventory()->addItem($vanillaSword);
    }

    public function baseBlockReward($p)
    {
        $smallCount = mt_rand(1, 8);
        $bigCount = mt_rand(32, 64);
        switch (mt_rand(1, 7)) {
            case 1:
                $blocks = Item::get(46, 0, $bigCount);
                break;
            case 2:
                $blocks = Item::get(116, 0, 1);
                break;
            case 3:
                $blocks = Item::get(379, 0, 1);
                break;
            case 4:
                $blocks = Item::get(145, 0, 1);
                break;
            case 5:
                $blocks = Item::get(384, 0, $smallCount);
                break;
            case 6:
                $blocks = Item::get(466, 0, $smallCount);
                break;
            case 7:
                $blocks = Item::get(320, 0, 48);
                break;
        }
        $p->getInventory()->addItem($blocks);
    }

    public function potionReward($p)
    {
        $potion = mt_rand(5, 35);
        $itemCount = mt_rand(1, 4);
        switch (mt_rand(1, 4)) {
            case 1:
                $p->getInventory()->addItem(Item::get(Item::SPLASH_POTION, $potion, $itemCount));
                break;
            default:
                $p->getInventory()->addItem(Item::get(Item::POTION, $potion, $itemCount));
                break;
        }
    }

    public function spaceChestReward($p)
    {
        $array = array(101, 101, 101, 101, 102, 102);
        $arrayRand = $this->plugin->reward->getRandomKey($array);
        $p->getInventory()->addItem(Item::get(Item::CHEST, $arrayRand, 1));
    }

    public function simple($p)
    {
        switch (mt_rand(1, 7)) {
            case 1:
                $this->cashReward($p);
                break;
            case 2:
                $this->baseArmourReward($p);
                break;
            case 3:
                $this->mineralReward($p);
                break;
            case 4:
                $this->baseBlockReward($p);
                break;
            case 5:
                $this->potionReward($p);
                break;
            case 6:
                $this->spaceChestReward($p);
                break;
            case 7:
                $this->baseWeaponReward($p);
                break;
        }
    }

    public function opSwordReward($p)
    {
        $this->plugin->reward->giveOpSwordTo($p);
    }

    public function opBowReward($p)
    {
        $this->plugin->reward->giveOpBowTo($p);
    }

    public function opArmourReward($p)
    {
        switch (mt_rand(1, 4)) {
            case 1:
                $this->plugin->reward->giveOpHelmetTo($p);
                break;
            case 2:
                $this->plugin->reward->giveOpChestplateTo($p);
                break;
            case 3:
                $this->plugin->reward->giveOpLeggingsTo($p);
                break;
            case 4:
                $this->plugin->reward->giveOpBootsTo($p);
                break;
        }
    }

    public function legendary($p)
    {
        switch (mt_rand(1, 8)) {
            case 1:
                $this->cashReward($p);
                break;
            case 2:
                $this->spaceChestReward($p);
                break;
            case 3:
                $this->baseWeaponReward($p);
                break;
            case 4:
                $this->opSwordReward($p);
                break;
            case 5:
                $this->opBowReward($p);
                break;
            case 6:
                $this->opArmourReward($p);
                break;
            default:
                $this->opSwordReward($p);
                break;
        }
    }
}
