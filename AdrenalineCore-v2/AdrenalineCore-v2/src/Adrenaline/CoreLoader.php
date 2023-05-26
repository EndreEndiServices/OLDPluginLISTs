<?php

/*
 *               _                      _ _
 *      /\      | |                    | (_)
 *     /  \   __| |_ __ ___ _ __   __ _| |_ _ __   ___
 *    / /\ \ / _` | '__/ _ \ '_ \ / _` | | | '_ \ / _ \
 *   / ____ \ (_| | | |  __/ | | | (_| | | | | | |  __/
 *  /_/    \_\__,_|_|  \___|_| |_|\__,_|_|_|_| |_|\___|
 *
 * This plugin cannot be shared, or used by anyone else.
 * The only people allowed to use this, must have permission by AppleDevelops.
 * If you don't have permission, and use this plugin, I will not be afraid to take action.
 *
 * @author AppleDevelops
 *
 */

namespace Adrenaline;

use Adrenaline\Commands\UHCCommand;
use Adrenaline\Managers\CommandManager;
use Adrenaline\Managers\EventManager;
use Adrenaline\Managers\LoginManager;
use Adrenaline\Tasks\AdvertisementTask;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class CoreLoader extends PluginBase {

    public $taskIsRunning = 0; //For the UHC tasks.
    public $loginManager;

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents(new EventManager($this), $this);
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        @mkdir($this->getDataFolder() . "players/");
        $this->sendManagers();
        $this->sendAds();
        $this->loginManager = new LoginManager($this);
    }

    /* Custom API */

    public function sendPrefix() {
        return TextFormat::GRAY . "[" . TextFormat::RED . "Adrenaline" . TextFormat::GOLD . "UHC" . TextFormat::GRAY . "] " . TextFormat::RESET;
    }

    public function sendAds() {
        return $this->getServer()->getScheduler()->scheduleRepeatingTask(new AdvertisementTask($this), 2400);
    }

    public function isTaskRunning() {
        if ($this->taskIsRunning === 0) {
            return false;
        } else {
            return true;
        }
    }

    public function setTaskRunning() {
        return $this->taskIsRunning = 1;
    }

    public function sendManagers() {
        new CommandManager($this);
        //new LoginManager($this); Removed
    }

    /*public function getLoginManager(): LoginManager {
        return $this->loginManager;
    }*/
}