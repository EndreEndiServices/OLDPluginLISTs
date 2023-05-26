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

use pocketmine\Player;

class TPAPI {
	/**
	 * @var Player[][]
	 */
	public $requestsList = [];

	/**
	 *
	 * @var PrestigeSocietyTeleport
	 *
	 */
	protected $base;

	/**
	 *
	 * TPAPI constructor.
	 *
	 * @param PrestigeSocietyTeleport $base
	 *
	 */
	public function __construct(PrestigeSocietyTeleport $base){
		$this->base = $base;
	}

	/**
	 *
	 * @param Player $p
	 *
	 */
	public function pushIntoRequests(Player $p){
		$this->requestsList[$p->getName()] = [];
	}

	/**
	 *
	 * @param Player $p
	 *
	 */
	public function removeFromRequests(Player $p){
		if(isset($this->requestsList[$p->getName()])){
			unset($this->requestsList[$p->getName()]);
		}
	}

	/**
	 *
	 * @param Player $p
	 *
	 * @return bool
	 *
	 */
	public function hasRequest(Player $p){
		return isset($this->requestsList[$p->getName()][1]);
	}

	/**
	 *
	 * @param Player $to
	 * @param Player $from
	 *
	 * @return bool
	 *
	 */
	public function requestExists(Player $to, Player $from){
		return isset($this->requestsList[$to->getName()][1]) and ($this->requestsList[$to->getName()][1] === $from);
	}

	/**
	 *
	 * @param Player $to
	 * @param Player $from
	 *
	 */
	public function sendRequestTo(Player $to, Player $from){
		$this->requestsList[$to->getName()] = [$to, $from];
	}

	/**
	 *
	 * @param Player $to
	 *
	 * @return bool[]|Player[]
	 *
	 */
	public function acceptRequest(Player $to){
		$to = $this->requestsList[$to->getName()][0];
		$from = $this->requestsList[$to->getName()][1];

		if($to->isOnline()){
			//$from->teleport($to);
			//unset($this->requestsList[$to->getName()][0]);
			return [true, $from];
		}
		unset($this->requestsList[$to->getName()][0]);

		return [false, $from];
	}

	public function removeQueue(Player $to){
		$to = $this->requestsList[$to->getName()][0];
		unset($this->requestsList[$to->getName()][0]);
	}

	/**
	 *
	 * @param Player $to
	 *
	 * @return bool[]|Player[]
	 *
	 */
	public function denyRequest(Player $to){
		$to = $this->requestsList[$to->getName()][0];
		$from = $this->requestsList[$to->getName()][1];

		if($from->isOnline()){
			unset($this->requestsList[$to->getName()][1]);

			return [true, $from];
		}
		unset($this->requestsList[$to->getName()][1]);

		return [false, $from];
	}
}