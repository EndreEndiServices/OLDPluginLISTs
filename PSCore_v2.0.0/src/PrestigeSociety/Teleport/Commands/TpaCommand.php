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

use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Teleport\PrestigeSocietyTeleport;

class TpaCommand extends Command implements PluginIdentifiableCommand {
	/**
	 * @var PrestigeSocietyTeleport
	 */
	protected $base;

	/**
	 * TpaCommand constructor.
	 * @param PrestigeSocietyTeleport $base
	 */
	public function __construct(PrestigeSocietyTeleport $base){
		$this->base = $base;
		parent::__construct("tpa", "send a tp request to someone", "/tpa <player>", ["tp-request"]);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed|void
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$sender instanceof Player){
			$sender->sendMessage("Please run this Command in-game.");

			return;
		}
		if(count($args) <= 0){
			$sender->sendMessage($this->usageMessage);

			return;
		}
		if($sender->getLevel() == "Work") return;
		if($sender->getLevel() == "pvp") return;
		$player = $this->base->plugin->getServer()->getPlayer($args[0]);
		if($player !== null){

			$subBlock = $player->getLevel()->getBlockIdAt($player->x, $player->y - 1, $player->z);
			if($subBlock == Block::SPONGE or $subBlock == 41 or $subBlock == 42 or $subBlock == 57){
				$message = str_replace("@player", $player->getName(), $this->base->getMessages()->get("cannot_send_tpa"));
				$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

				return;
			}

			$message = str_replace("@player", $sender->getName(), $this->base->getMessages()->get("incoming_tpa_request"));
			$message2 = str_replace("@player", $player->getName(), $this->base->getMessages()->get("sent_tpa_request"));
			$player->sendMessage(PrestigeSocietyTeleport::colorMessage($message));
			$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message2));
			$this->base->getTPAPI()->sendRequestTo($player, $sender);

			return;
		}
		$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($this->base->getMessages()->get("tp_request_fail")));
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