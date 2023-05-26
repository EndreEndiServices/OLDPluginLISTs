<?php

/*
        _       _           _        ____            _             _ _   _ ___
       / \   __| |_ __ ___ (_)_ __  / ___|___  _ __ | |_ _ __ ___ | | | | |_ _|
      / _ \ / _` | '_ ` _ \| | '_ \| |   / _ \| '_ \| __| '__/ _ \| | | | || |
     / ___ \ (_| | | | | | | | | | | |__| (_) | | | | |_| | | (_) | | |_| || |
    /_/   \_\__,_|_| |_| |_|_|_| |_|\____\___/|_| |_|\__|_|  \___/|_|\___/|___|


    Copyright (C) 2019 SchdowNVIDIA
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.

 */

declare(strict_types = 1);

namespace SchdowNVIDIA\AdminControlUI;

use _64FF00\PurePerms\PurePerms;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {


    public function onEnable()
    {
        @mkdir($this->getDataFolder());
        $this->saveResource("banned.yml");
        $this->saveResource("timebanned.yml");
        //$this->cfgChecker();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->saveDefaultConfig();
        /*if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") === null) {
            $this->getLogger()->error("OreEarn requires the plugin \"EconomyAPI\" to work!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }*/
    }

    // ConfirmUI (Extra) - Start

    private function openConfirmUI(Player $player, string $title, string $message) {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            return;
        });

        $form->setTitle($title);
        $form->setContent($message);
        $form->addButton("Okay!");
        $form->sendToPlayer($player);
        return $form;
    }

    // BanUI - Start

    private function openBanUI(Player $player) {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if($data != null) {
                switch ($data) {
                    case 1:
                        $this->openNormalBanUI($player);
                        break;
                    case 2:
                        //$this->openKickUI($player);
                        break;
                    case 3:
                        break;
                    case 4:

                        break;
                }
            } else {
                $this->openMenuUI($player);
            }
        });

        $form->setTitle("BanUI - Menu");
        $form->setContent("Here is a list for all options in the BanUI.");
        $form->addButton("§c§lBACK");
        $form->addButton("Ban");
        $form->addButton("Unban");
        $form->addButton("Time Ban");
        $form->addButton("Time Unban");
        $form->sendToPlayer($player);
        return $form;
    }

    private function openNormalBanUI(Player $player) {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if($data[0] === null) {
                $this->openMenuUI($player);
                return true;
            } else {
                $target = $data[0];
            }
            if(!isset($data[1])) {
                $reason = "No Reason provided.";
            } else {
                $reason = $data[1];
            }
            $banned = new Config($this->getDataFolder() . "banned.yml", Config::YAML);
            if($this->getServer()->getPlayer($target)) {
                $toBan = $this->getServer()->getPlayer($target);
                $banned->setNested($toBan->getName().".by", $player->getName());
                $banned->setNested($toBan->getName().".reason", $reason);
                $toBan->kick("§cAdminControlUI:\n§fYou've got banned by ".$player->getName()." for \"".$reason."\"", false);
                $this->openConfirmUI($player,"BanUI - Done", "You've successfully banned ".$toBan->getName()." for \"".$reason."\"!");
            } else {
                $banned->setNested($target->getName().".by", $player->getName());
                $banned->setNested($target->getName().".reason", $reason);
                $this->openConfirmUI($player,"BanUI - Done", "You've successfully banned ".$target." for \"".$reason."\"!");
            }
            $banned->save();
            $banned->reload();
        });

        $form->setTitle("BanUI - Ban");
        $form->addInput("Name");
        $form->addInput("Reason");
        $form->sendToPlayer($player);
        return $form;
    }

    private function openTimeBanUI(Player $player) {
        $form = new CustomForm(function (Player $player, array $data = null) {

        });
    }



    // BanUI - End

    // KickUI - Start

    private function openKickUI(Player $player) {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if($data[0] === null) {
                $this->openMenuUI($player);
                return true;
            } else {
                $target = $data[0];
            }
            if(!isset($data[1])) {
                $reason = "No Reason provided.";
            } else {
                $reason = $data[1];
            }
            if($this->getServer()->getPlayer($target)) {
                $toKick = $this->getServer()->getPlayer($target);
                    $toKick->kick("§cAdminControlUI: \n§fYou've got kicked §fby " . $player->getName() . " for \"" . $reason . "\"", false);
                    $this->openConfirmUI($player, "KickUI - Done", "You've successfully kicked ".$toKick->getName()." for \"".$reason."\"");
                    return true;
            } else {
                $player->sendMessage("§cERROR: §fThere is no player with the name: " . $data[0]);
            }
        });
        $form->setTitle("KickUI");
        $form->addInput("Name");
        $form->addInput("Reason");
        $form->sendToPlayer($player);
        return $form;
    }

    // KickUI - End

    // MenuUI - Start

    private function openMenuUI(Player $player) {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if($data != null) {
                switch ($data) {
                    case 0:
                        break;
                    case 1:
                        $this->openBanUI($player);
                        break;
                    case 2:
                        $this->openKickUI($player);
                        break;
                    case 3:

                        break;
                    case 4:

                        break;
                }
            } else {
                return true;
            }
        });
        $buttons = 0;
        $form->setTitle("AdminControlUI");
        $form->setContent("Welcome to the AdminControlUI!");
        $form->addButton("§c§lLEAVE");
        if($player->hasPermission("admincontrol.full") || $player->hasPermission("admincontrol.ban")) {
            $form->addButton("Ban");
            $buttons++;
        }
        if($player->hasPermission("admincontrol.full") || $player->hasPermission("admincontrol.kick")) {
            $form->addButton("Kick");
            $buttons++;
    }
        if($this->getConfig()->get("pureperms-support") === true) {
            if($player->hasPermission("admincontrol.full") || $player->hasPermission("admincontrol.groups")) {
                $form->addButton("Groups");
                $buttons++;
            }
        }
        if($this->getConfig()->get("economyapi-support") === true) {
            if($player->hasPermission("admincontrol.full") || $player->hasPermission("admincontrol.economy")) {
                $form->addButton("Economy");
                $buttons++;
            }
        }
        if($buttons === 0) {
            $form->setTitle("§cERROR: §fYou have the permission to open the AdminControlUI but no permissions for any of the caterogies!");
        }
        $form->sendToPlayer($player);
        return $form;
    }

    // MenuUI - End

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if($command->getName() === "admincontrol") {
            if ($sender instanceof Player) {
                if ($sender->hasPermission("admincontrol.open") || $sender->hasPermission("admincontrol.full")) {
                    $this->openMenuUI($sender);
                } else {
                    $sender->sendMessage("§cYou don't have the required permissions to use this command!");
                }
            } else {
                $sender->sendMessage("Run this command in-game!");
            }
        }
        return true;
    }

}