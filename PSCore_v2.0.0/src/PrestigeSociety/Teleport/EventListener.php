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

namespace PrestigeSociety\Teleport;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Teleport\Handle\Sessions;

class EventListener implements Listener {
	/**
	 * @var PrestigeSocietyTeleport
	 */
	protected $base;

	/**
	 *
	 * EventListener constructor.
	 *
	 * @param PrestigeSocietyTeleport $base
	 *
	 */
	public function __construct(PrestigeSocietyTeleport $base){
		$this->base = $base;
	}

	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		/** @var Player[]|int[] $session */
		$session = Sessions::getFromQueue($player);

		if($session !== null){

			$to = $event->getTo();
			$from = $event->getFrom();

			if($to->x !== $from->x || $to->y !== $from->y || $to->z !== $from->z){
				$this->base->plugin->getScheduler()->cancelTask($session['task']);
				$message = $this->base->getMessages()->get('teleport_cancel');
				$player->sendMessage(RandomUtils::colorMessage($message));
				Sessions::removeFromQueue($player);
			}

		}
	}

	/**
	 *
	 * @param PlayerJoinEvent $e
	 *
	 * @priority MONITOR
	 *
	 */
	public function onJoin(PlayerJoinEvent $e){
		$this->base->getTPAPI()->pushIntoRequests($e->getPlayer());
	}

	/**
	 *
	 * @param PlayerQuitEvent $e
	 *
	 * @priority MONITOR
	 *
	 */
	public function onQuit(PlayerQuitEvent $e){
		$this->base->getTPAPI()->removeFromRequests($e->getPlayer());
	}
}