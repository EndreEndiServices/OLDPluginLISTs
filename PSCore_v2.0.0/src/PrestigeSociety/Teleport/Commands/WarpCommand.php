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

class WarpCommand extends Command implements PluginIdentifiableCommand {
	/**
	 * @var PrestigeSocietyTeleport
	 */
	protected $base;

	/**
	 *
	 * @param PrestigeSocietyTeleport $base
	 *
	 */
	public function __construct(PrestigeSocietyTeleport $base){
		$this->base = $base;
		parent::__construct("warp", "warp main command", "Usage: /warp help", ["warps"]);
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed|void
	 * @throws \InvalidStateException
	 *
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$sender instanceof Player){
			$sender->sendMessage("Please run this command in-game.");

			return;
		}
		if(count($args) <= 0){
			$this->getUsage();

			return;
		}
		if(empty($args[1]) and strtolower($args[0]) === "help"){
			$this->sendHelpMessage($sender);

			return;
		}elseif(empty($args[1]) and strtolower($args[0]) === "list"){
			$homes = $this->base->getWarpAPI()->getWarps();
			$message = $this->base->getMessages()->get("warp_list");
			$message = str_replace("@warp_list", implode(", ", $homes), $message);
			$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

			return;
		}elseif(empty($args[1]) and strtolower($args[0]) !== "list"){

			$home = $this->base->getWarpAPI()->getWarpPosition($args[0]);
			if($home !== null){
				if($sender->hasPermission("warp." . $args[0])){
					if($sender->hasPermission("warp.instant")){

						if($args[0] == "pvp"){
							if($this->base->plugin->PrestigeSocietyLevels->getLevel($sender) < 5){
								$message = "&6[!] &cYou need to have level 5 to enter at pvp arena!";
								$message = RandomUtils::colorMessage($message);
								$sender->sendMessage($message);

								return;
							}
						}

						$message = $this->base->getMessages()->get("warp_tp_success");
						$message = str_replace("@warp_name", $args[0], $message);

						$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));
						$sender->teleport($home);

					}else{

						if($args[0] == "pvp"){
							if($this->base->plugin->PrestigeSocietyLevels->getLevel($sender) < 5){
								$message = "&6[!] &cYou need have Level 5 for enter on PVP!";
								$message = RandomUtils::colorMessage($message);
								$sender->sendMessage($message);

								return;
							}
						}

						$delay = intval($this->base->plugin->getConfig()->get('tp_delays')['warp_tp_delayed']);

						$message = $this->base->getMessages()->get("warp_tp_delayed");
						$message = str_replace(["@warp_name", "@delay"], [$args[0], $delay], $message);

						$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

						$task = new TPDelayTask($this->base->plugin, $sender, $home);
						$id = $this->getPlugin()->getScheduler()->scheduleDelayedTask($task, 20 * $delay)->getTaskId();
						Sessions::addToQueue($sender, $id);
					}
				}else{
					$message = $this->base->getMessages()->get("non_warp_permission");
					$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));
				}
			}else{
				$message = $this->base->getMessages()->get("non_existent_warp");
				$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));
			}

		}elseif(isset($args[1]) and strtolower($args[0]) === "set"){

			if(!$sender->hasPermission('Command.warp.admin')) return;
			$this->base->getWarpAPI()->setWarp($args[1], $sender->x, $sender->y, $sender->z, $sender->level->getName());

			$message = $this->base->getMessages()->get("warp_set");
			$message = str_replace("@warp_name", $args[1], $message);
			$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

			return;

		}elseif(isset($args[1]) and strtolower($args[0]) === "delete"){

			if(!$sender->hasPermission('Command.warp.admin')) return;
			$this->base->getWarpAPI()->deleteWarp($args[1]);

			$message = $this->base->getMessages()->get("warp_deleted");
			$message = str_replace("@warp_name", $args[1], $message);
			$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function sendHelpMessage(Player $player){
		$player->sendMessage(PrestigeSocietyTeleport::colorMessage("&a--Warp Help Commands--"));
		$player->sendMessage(PrestigeSocietyTeleport::colorMessage("&b/warp list &f: &eShows warp list"));
		$player->sendMessage(PrestigeSocietyTeleport::colorMessage("&b/warp <name> &f: &eTeleport to a specified warp"));
		if($player->hasPermission("pl.warp.admin")){
			$player->sendMessage(PrestigeSocietyTeleport::colorMessage("&b/warp set <name> &f: &eSets a new warp"));
			$player->sendMessage(PrestigeSocietyTeleport::colorMessage("&b/warp delete <name> &f: &eDeletes a specified warp"));
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