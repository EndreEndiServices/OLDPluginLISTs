<?php

declare(strict_types=1);

namespace fcore\pvp;

use fcore\FCore;
use fcore\form\Button;
use fcore\profile\ProfileManager;
use fcore\pvp\kit\Kit;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\Player;

/**
 * Class PvPManager
 * @package fcore\pvp
 */
class PvPManager implements Listener {

    /** @var FCore $plugin */
    public $plugin;

    /**
     * PvPManager constructor.
     * @param FCore $plugin
     */
    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     */
    public function teleportToPVP(Player $player) {
        if(ProfileManager::$players[$player->getName()]["kit"] === null) {
            $player->sendMessage(FCore::getPrefix()."§cSelect the kit first!");
            return;
        }
        $player->setMaxHealth(20);
        $player->setDisplayName($player->getName());
        $player->setHealth(20);
        $player->setFood(20);
        $player->setXpProgress(0);
        $player->setXpLevel(0);
        $player->removeAllEffects();
        $player->teleport($this->plugin->getServer()->getLevelByName(FCore::PVP_LEVEL_NAME)->getSafeSpawn());
        $player->setAllowFlight(false);
        $player->setGamemode($player::ADVENTURE);
        /** @var Kit $kit */
        $kit = $this->plugin->kitMgr->kits[ProfileManager::$players[$player->getName()]["kit"]];
        $kit->equip($player);
        switch (rand(1, 5)) {
            case 1:
                $player->teleport(new Vector3(221, 38, 290));
                break;
            case 2:
                $player->teleport(new Vector3(207,30, 300));
                break;
            case 3:
                $player->teleport(new Vector3(239, 35, 313));
                break;
            case 4:
                $player->teleport(new Vector3(229, 39, 215));
        }
        $player->sendMessage(FCore::getPrefix()."§aYou are teleported to PvP");
    }

    /**
     * @param Player $player
     */
    public function openPVPMenu(Player $player) {

        $buttons = [
            new Button("§e§lSelect kit", "http://icons.iconarchive.com/icons/chrisl21/minecraft/128/Folder-Blank-icon.png", "url"),
            new Button("§e§lJoin to PVP", "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Iron-Sword-icon.png", "url")
        ];

        $func = function (Player $player, array $data) {
            $result = $data[0];
            if($result === null) return;
            switch ($result) {
                case 0:
                    FCore::$instance->kitMgr->openKitForm($player);
                    break;
                case 1:
                    $this->teleportToPVP($player);
                    break;
            }
        };


        $form = $this->plugin->formApi->createSimpleForm("§6§lPVP", "§aSelect the kit and join to the arena!", $buttons, $func);
        $form->send($player);
    }

}