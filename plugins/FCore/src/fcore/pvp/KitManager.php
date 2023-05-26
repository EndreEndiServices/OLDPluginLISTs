<?php

declare(strict_types=1);

namespace fcore\pvp;

use fcore\FCore;
use fcore\form\Button;
use fcore\form\FormAPI;
use fcore\form\SimpleForm;
use fcore\profile\ProfileManager;
use fcore\pvp\kit\ArcherKit;
use fcore\pvp\kit\BomberKit;
use fcore\pvp\kit\ClassicKit;
use fcore\pvp\kit\DwarfKit;
use fcore\pvp\kit\Kit;
use fcore\pvp\kit\VipKit;
use fcore\pvp\kit\WarriorKit;
use fcore\Settings;
use pocketmine\Player;

class KitManager {

    /** @var FCore $plugin */
    public $plugin;

    /** @var array $kits */
    public $kits = [];

    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        $this->addKits();
    }

    public function addKits() {
        $this->kits["classic"] = new ClassicKit();
        $this->kits["archer"] = new ArcherKit();
        $this->kits["bomber"] = new BomberKit();
        $this->kits["dwarf"] = new DwarfKit();
        $this->kits["vip"] = new VipKit();
        $this->kits["warrior"] = new WarriorKit();
    }

    public function openKitForm(Player $player) {
        $buttons = [];
        /**
         * @var string $index
         * @var Kit $kit
         */
        foreach ($this->kits as $index => $kit) {
            $o = ProfileManager::$players[$player->getName()]["kits"][$index] == false ? "§cBUY" : "§aDEPLOY";
            if(ProfileManager::isVip($player)) {
                $o = "§aDEPLOY";
            }
            array_push($buttons, new Button("§6§l".$kit->getName()." §r".$o, $kit->getImage(), "url"));
        }

        $func = function (Player $player, array $data) {
            $result = $data[0];
            if($result === null) return;

            $kits = [];
            foreach (\fcore\FCore::$instance->kitMgr->kits as $kit) {
                array_push($kits, $kit);
            }

            if(empty($kits[$result])) return;

            /** @var Kit $kit */
            $kit = $kits[$result];

            $own = ProfileManager::$players[$player->getName()]["kits"][strtolower($kit->getName())];
            if(\fcore\profile\ProfileManager::isVip($player)) {
                $own = true;
            }
            if($own) {
                ProfileManager::$players[$player->getName()]["kit"] = strtolower($kit->getName());
                $player->sendMessage(\fcore\FCore::getPrefix()."§a{$kit->getName()} kit deployed!");
            }
            else {
                \fcore\FCore::$instance->kitMgr->openKitShop($player);
            }
        };

        $form = $this->plugin->formApi->createSimpleForm("§l§6Kits", "§aSelect kit.", $buttons, $func);
        $form->send($player);
    }

    public function openKitShop(Player $player) {
        /** @var FormAPI $formApi */
        $formApi = $this->plugin->formApi;

        $onSubmit = function (Player $sender, array $data) {
            $result = $data[0];
            if($result === null) return;

            /** @var Kit[] kits */
            $kits = [];

            foreach ($this->kits as $index => $kit) {
                array_push($kits, $kit);
            }

            if(empty($kits[intval($result)])) {
                return;
            }

            /** @var Kit $kit */
            $kit = $kits[intval($result)];

            $playerCoins = (int)ProfileManager::getPlayerProfileData($sender, "coins");

            if($playerCoins - $kit->getCost() >= 0) {
                $sender->sendMessage(FCore::getPrefix()."§aYou bought {$kit->getName()} kit!");
                ProfileManager::$players[$sender->getName()]["kits"][strtolower($kit->getName())] = true;
            }
            else {
                $sender->sendMessage(FCore::getPrefix()."§cYou need more coins to buy this!");
            }
        };

        $buttons = [];

        /**
         * @var  Kit $kit
         */
        foreach ($this->kits as $index => $kit) {
            array_push($buttons, new Button("§l".$kit->getName()."§r §7".$kit->getCost(), $kit->getImage(), "url"));
        }

        /** @var SimpleForm $form */
        $form = $formApi->createSimpleForm("§1§lShop§r§3 >>> §eKits", "Select the kit to buy", $buttons, $onSubmit);
        $form->send($player);
    }

    public function registerKits() {
        $this->kits["classic"] = new ClassicKit;
    }
}