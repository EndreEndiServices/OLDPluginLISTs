<?php
/*
 *   Teleport: A TP essentials plugin
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace PrestigeSociety\Teleport\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Teleport\PrestigeSocietyTeleport;

class SetSpawnCommand extends Command implements PluginIdentifiableCommand {
	/**
	 * @var PrestigeSocietyTeleport
	 */
	protected $base;

	/**
	 *
	 * TpaAcceptCommand constructor.
	 *
	 * @param PrestigeSocietyTeleport $base
	 *
	 */
	public function __construct(PrestigeSocietyTeleport $base){
		$this->base = $base;
		parent::__construct("setspawn", "set spawn position", "/setspawn", ["sethub", "setlobby"]);
		$this->setPermission("command.setspawn");
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed|void
	 *
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage("Please run this Command in-game.");

			return;
		}
		$message = $this->base->getMessages()->get("spawn_position_set");
		$message = str_replace(["@x", "@y", "@z", "@level"], [$sender->x, $sender->y, $sender->z, $sender->level->getName()], $message);
		$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));
		$this->base->setSpawn($sender);
	}

	/**
	 *
	 * @return Plugin
	 *
	 */
	public function getPlugin(): Plugin{
		return $this->base->plugin;
	}
}