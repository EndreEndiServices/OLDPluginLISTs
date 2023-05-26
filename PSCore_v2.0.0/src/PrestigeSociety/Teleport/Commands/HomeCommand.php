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
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Teleport\Handle\Sessions;
use PrestigeSociety\Teleport\PrestigeSocietyTeleport;
use PrestigeSociety\Teleport\Task\TPDelayTask;
use sex\guard\Manager;

class HomeCommand extends Command implements PluginIdentifiableCommand {
	/**
	 * @var PrestigeSocietyTeleport
	 */
	protected $base;

	/**
	 * @var PrestigeSocietyCore
	 */
	private $core;

	/**
	 * TpaAcceptCommand constructor.
	 * @param PrestigeSocietyTeleport $base
	 */
	public function __construct(PrestigeSocietyTeleport $base, PrestigeSocietyCore $core){
		$this->base = $base;
		$this->core = $core;
		parent::__construct("home", "home main Command", "/home help", []);
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
			$sender->sendMessage("Please run this Command in-game.");

			return;
		}
		if($sender->getLevel() == "Work") return;
		if(count($args) <= 0){
			$this->setUsage($sender);

			return;
		}
		if(empty($args[1]) and strtolower($args[0]) === "help"){

			$this->sendHelpMessage($sender);

			return;

		}elseif(empty($args[1]) and strtolower($args[0]) === "list"){

			$homes = $this->base->getHomeAPI()->getPlayerHomes($sender);
			$message = $this->base->getMessages()->get("home_list");
			$message = str_replace("@home_list", implode(", ", $homes), $message);
			$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

			return;

		}elseif(empty($args[1]) and strtolower($args[0]) !== "list"){

			if($sender->hasPermission("home.instant")){
				$home = $this->base->getHomeAPI()->getHomePosition($sender, $args[0]);
				if($home !== null){
					$message = $this->base->getMessages()->get("home_tp_success");
					$message = str_replace("@home_name", $args[0], $message);
					$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));
					$sender->teleport($home);
				}

				return;
			}else{
				$home = $this->base->getHomeAPI()->getHomePosition($sender, $args[0]);
				if($home !== null){
					$delay = intval($this->base->plugin->getConfig()->get('tp_delays')['home_tp_delay']);
					$message = $this->base->getMessages()->get("home_tp_delayed");
					$message = str_replace(["@home_name", "@delay"], [$args[0], $delay], $message);
					$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));
					$task = new TPDelayTask($this->base->plugin, $sender, $home);
					$id = $this->getPlugin()->getScheduler()->scheduleDelayedTask($task, 20 * $delay)->getTaskId();
					Sessions::addToQueue($sender, $id);
				}

				return;
			}

		}elseif(isset($args[1]) and strtolower($args[0]) === "set"){
			if(Manager::getInstance()->getRegion($sender->getPosition()) !== null) return;
			$count = count($this->base->getHomeAPI()->getPlayerHomes($sender));
			if($count < 2){
				$this->base->getHomeAPI()->setHome($sender, $args[1], $sender->x, $sender->y, $sender->z, $sender->getLevel()->getFolderName());
				$message = $this->base->getMessages()->get("home_set");
				$message = str_replace("@home_name", $args[1], $message);
				$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

				return;
			}else{
				if($this->core->PrestigeSocietyCountHomes->playerExists($sender)){
					$countHomes = $this->core->PrestigeSocietyCountHomes->getCount($sender);
					if($countHomes > $count){
						$this->base->getHomeAPI()->setHome($sender, $args[1], $sender->x, $sender->y, $sender->z, $sender->getLevel()->getFolderName());
						$message = $this->base->getMessages()->get("home_set");
						$message = str_replace("@home_name", $args[1], $message);
						$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

						return;
					}
				}
				$message = $this->base->getMessages()->get("home_count");
				$message = str_replace("@home_count", $count, $message);
				$message = str_replace("\\n", "\n", $message);
				$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

				return;
			}

		}elseif(isset($args[1]) and strtolower($args[0]) === "delete"){
			$this->base->getHomeAPI()->deleteHome($sender, $args[1]);
			$message = $this->base->getMessages()->get("home_deleted");
			$message = str_replace("@home_name", $args[1], $message);
			$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));
		}
	}

	public function sendHelpMessage(Player $player){
		$player->sendMessage(PrestigeSocietyTeleport::colorMessage("&a--Home Help Commands--"));
		$player->sendMessage(PrestigeSocietyTeleport::colorMessage("&b/home list &f: &eShows your home list"));
		$player->sendMessage(PrestigeSocietyTeleport::colorMessage("&b/home <home> &f: &eTeleport to a specified home"));
		$player->sendMessage(PrestigeSocietyTeleport::colorMessage("&b/home set <name> &f: &eSets a new home"));
		$player->sendMessage(PrestigeSocietyTeleport::colorMessage("&b/home delete <name> &f: &eDeletes a specified home"));
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->base->plugin;
	}
}