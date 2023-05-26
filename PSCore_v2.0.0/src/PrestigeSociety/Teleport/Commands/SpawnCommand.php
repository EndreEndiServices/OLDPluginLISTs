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
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Teleport\Handle\Sessions;
use PrestigeSociety\Teleport\PrestigeSocietyTeleport;
use PrestigeSociety\Teleport\Task\TPDelayTask;

class SpawnCommand extends Command implements PluginIdentifiableCommand {
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
		parent::__construct("spawn", "teleport to spawn", "/spawn", ["hub", "lobby"]);
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
		if(!$sender instanceof Player){
			$sender->sendMessage("Please run this command in-game.");

			return;
		}

		if($sender->hasPermission("spawn.instant")){
			$sender->teleport($this->base->getSpawn());
		}else{
			$delay = intval($this->base->plugin->getConfig()->get('tp_delays')['spawn_tp_delayed']);
			$message = $this->base->getMessages()->get("spawn_tp_delayed");
			$message = str_replace("@delay", $delay, $message);
			$sender->sendMessage(RandomUtils::colorMessage($message));
			$task = new TPDelayTask($this->base->plugin, $sender, $this->base->getSpawn());
			$id = $this->getPlugin()->getScheduler()->scheduleDelayedTask($task, 20 * $delay)->getTaskId();
			Sessions::addToQueue($sender, $id);
		}
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