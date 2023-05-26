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

class TpDenyCommand extends Command implements PluginIdentifiableCommand {
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
		parent::__construct("tpdeny", "deny a tp request from someone", "/tpdeny", ["tp-deny"]);
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
		if($this->base->getTPAPI()->hasRequest($sender)){
			$acre = $this->base->getTPAPI()->denyRequest($sender);
			if($acre[0]){
				$message = $this->base->getMessages()->get("tp_denied_request");
				$message = str_replace("@player", $acre[1]->getName(), $message);
				$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));
				$message = $this->base->getMessages()->get("tp_request_denied");
				$message = str_replace("@player", $sender->getName(), $message);
				$acre[1]->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

				return;
			}
			$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($this->base->getMessages()->get("tp_accept_player_offline")));
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