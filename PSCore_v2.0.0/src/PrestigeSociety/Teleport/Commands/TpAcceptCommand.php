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
use PrestigeSociety\Teleport\Handle\Sessions;
use PrestigeSociety\Teleport\PrestigeSocietyTeleport;
use PrestigeSociety\Teleport\Task\TPDelayTask;

class TpAcceptCommand extends Command implements PluginIdentifiableCommand {
	/**
	 * @var PrestigeSocietyTeleport
	 */
	protected $base;

	/**
	 * TpaAcceptCommand constructor.
	 * @param PrestigeSocietyTeleport $base
	 */
	public function __construct(PrestigeSocietyTeleport $base){
		$this->base = $base;
		parent::__construct("tpaccept", "accept a tp request from someone", "/tpaccept", ["tp-accept"]);
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed|void
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$sender instanceof Player){
			$sender->sendMessage("Please run this command in-game.");

			return;
		}
		if($sender->getLevel() == "Work") return;
		if($sender->getLevel() == "pvp") return;
		if($this->base->getTPAPI()->hasRequest($sender)){

			$subBlock = $sender->getLevel()->getBlockIdAt($sender->x, $sender->y - 1, $sender->z);
			$blocks = $this->base->plugin->getConfig()->getAll();
			$blocks = [(int)$blocks['vip_zone_block_id'], (int)$blocks['vip_iron_block_id'], (int)$blocks['vip_gold_block_id'], (int)$blocks['vip_diamond_block_id']];

			if($subBlock == Block::SPONGE or in_array($subBlock, $blocks)){
				$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($this->base->getMessages()->get("cannot_accept_tpa")));

				return;
			}

			$acre = $this->base->getTPAPI()->acceptRequest($sender);
			if($acre[0]){

				if($acre[1]->hasPermission("tpa.instant")){

					$message = $this->base->getMessages()->get("tp_accept_request");
					$message = str_replace("@player", $acre[1]->getName(), $message);
					$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

					$message = $this->base->getMessages()->get("tp_request_accepted");
					$message = str_replace("@player", $sender->getName(), $message);
					$acre[1]->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

					return;
				}else{
					$delay = intval($this->base->plugin->getConfig()->get('tp_delays')['tpa_tp_delay']);

					$message = $this->base->getMessages()->get("tp_accept_request_delayed");
					$message = str_replace(["@player", "@delay"], [$acre[1]->getName(), $delay], $message);

					$sender->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

					$message = $this->base->getMessages()->get("tp_request_accepted_delayed");

					$message = str_replace(["@player", "@delay"], [$sender->getName(), $delay], $message);
					$acre[1]->sendMessage(PrestigeSocietyTeleport::colorMessage($message));

					$task = new TPDelayTask($this->base->plugin, $acre[1], $sender->asPosition());
					$id = $this->getPlugin()->getScheduler()->scheduleDelayedTask($task, 20 * $delay)->getTaskId();
					Sessions::addToQueue($acre[1], $id);

					return;
				}
			}
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