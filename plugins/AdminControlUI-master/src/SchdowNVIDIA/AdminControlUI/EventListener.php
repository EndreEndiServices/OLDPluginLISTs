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

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\Player;
use pocketmine\utils\Config;

class EventListener implements Listener {

    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPreLogin(PlayerPreLoginEvent $event) {
        $banned = new Config($this->plugin->getDataFolder() . "banned.yml", Config::YAML);
        $player = $event->getPlayer();
        if($banned->exists($player->getName())) {
            $player->close("", "§cAdminControlUI:\n§fYou've been banned by ".$banned->getNested($player->getName().".by")." for \"".$banned->getNested($player->getName().".reason")."\"", true);
        }
    }

}