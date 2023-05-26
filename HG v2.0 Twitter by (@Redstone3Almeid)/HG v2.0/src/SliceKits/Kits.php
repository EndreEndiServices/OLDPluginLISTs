<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 30/09/2016
 * Time: 21:59
 */

namespace SliceKits;

use pocketmine\Player;

use pocketmine\item\Item;
use pocketmine\utils\Utils;

class Kits
{

    private $plugin;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getConfig(){
        return $this->plugin->configuration;
    }

    public function isNone(Player $player)
    {
        $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
        $player->getInventory()->setChestplate(Item::get(Item::CHAIN_CHESTPLATE));
    }

    public function isStomper(Player $player)
    {
        if($this->getConfig()->get($player->getName()) === "stomper"){
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(Item::AIR, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::END_PORTAL_FRAME, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");

        } else {

        }
    }

    public function isTrapper(Player $player)
    {
        if($this->getConfig()->get($player->getName()) === "trapper"){
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(Item::EGG, 0, 16));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");
        }
    }

    public function isViper(Player $player)
    {
        if($this->getConfig()->get($player->getName()) === "viper"){
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(376, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");

        } else {

        }
    }

    public function isLocalizer(Player $player)
    {
        if($this->getConfig()->get($player->getName()) === "localizer"){
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(289, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");

        } else {

        }
    }

    public function isDamager(Player $player)
    {
        if($this->getConfig()->get($player->getName()) === "damager"){
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(372, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");

        } else {

        }
    }

    public function isPVP(Player $player)
    {
        if($this->getConfig()->get($player->getName()) === "pvp"){
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(Item::STONE_SWORD, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");
        } else {

        }
    }

    public function isFireman(Player $player)
    {
        if ($this->getConfig()->get($player->getName()) === "fireman") {
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(377, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");
        } else {

        }
    }

    public function isMiner(Player $player)
    {
        if ($this->getConfig()->get($player->getName()) === "miner") {

        } else {

        }
    }

    public function isKangaruu(Player $player)
    {
        if ($this->getConfig()->get($player->getName()) === "kangaruu") {
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(288, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");
        } else {

        }
    }

    public function isTank(Player $player)
    {
        if ($this->getConfig()->get($player->getName()) === "tank") {
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(336, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");
        } else {

        }
    }

    public function isProtection(Player $player)
    {
        if ($this->getConfig()->get($player->getName()) === "protection") {

        } else {

        }
    }

    public function isGhoul(Player $player)
    {
        if ($this->getConfig()->get($player->getName()) === "ghoul") {
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(405, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");
        } else {

        }
    }

    public function isOsama(Player $player)
    {
        if ($this->getConfig()->get($player->getName()) === "osama") {
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(369, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");
        } else {

        }
    }

    public function isSnail(Player $player)
    {
        if ($this->getConfig()->get($player->getName()) === "snail") {
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(372, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");
        } else {

        }
    }

    public function isExplosion(Player $player)
    {
        if ($this->getConfig()->get($player->getName()) === "explosion") {
            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(113, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::COMPASS, 0, 1));
            $player->sendMessage("§a[ §6SliceHG §a] §bHG INICIOU! Você recebeu o KIT!");
        } else {

        }
    }

}