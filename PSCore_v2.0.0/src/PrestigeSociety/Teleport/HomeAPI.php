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

use pocketmine\level\Position;
use pocketmine\Player;

class HomeAPI {
	/**
	 * @var \SQLite3
	 */
	protected $db;
	/**
	 * @var PrestigeSocietyTeleport
	 */
	protected $base;

	/**
	 *
	 * HomeAPI constructor.
	 *
	 * @param PrestigeSocietyTeleport $base
	 *
	 */
	public function __construct(PrestigeSocietyTeleport $base){
		$this->base = $base;
		$this->db = new \SQLite3($base->plugin->databasesFolder() . "homes.db");
		$this->db->query("CREATE TABLE IF NOT EXISTS homes (home TEXT, x INT, y INT, z INT, level TEXT)");
	}

	/**
	 *
	 * @param Player $p
	 * @param        $name
	 * @param        $x
	 * @param        $y
	 * @param        $z
	 * @param        $level
	 *
	 */
	public function setHome(Player $p, $name, $x, $y, $z, $level){
		$pre = $this->db->prepare("INSERT INTO homes (home, x, y, z, level) VALUES (:home, :x, :y, :z, :level)");
		$pre->bindValue(":home", "{$p->getName()}_{$name}");
		$pre->bindValue(":x", $x);
		$pre->bindValue(":y", $y);
		$pre->bindValue(":z", $z);
		$pre->bindValue(":level", $level);
		$pre->execute();
		$pre->close();
	}

	/**
	 * @param Player $p
	 * @param        $name
	 *
	 * @return null|Position
	 *
	 */
	public function getHomePosition(Player $p, $name){
		if($this->homeExists($p, $name)){
			$query = $this->db->query("SELECT x, y, z, level FROM homes WHERE home = '{$p->getName()}_{$name}'");
			while($q = $query->fetchArray(SQLITE3_ASSOC)){
				if(!$this->base->plugin->getServer()->isLevelLoaded($q['level'])){
					$this->base->plugin->getServer()->loadLevel($q['level']);
				}
				$level = $this->base->plugin->getServer()->getLevelByName($q['level']);
				if($level !== null){
					return new Position($q['x'], $q['y'], $q['z'], $level);
				}
			}
		}

		return null;
	}

	/**
	 *
	 * @param Player $p
	 * @param        $name
	 *
	 * @return bool
	 *
	 */
	public function homeExists(Player $p, $name){
		$query = $this->db->query("SELECT home FROM homes WHERE home = '{$p->getName()}_{$name}'");

		return $query->fetchArray(SQLITE3_ASSOC) != 0;
	}

	/**
	 *
	 * @param Player $p
	 *
	 * @return string[]
	 *
	 */
	public function getPlayerHomes(Player $p){
		$query = $this->db->query("SELECT * FROM homes");
		$homes = [];
		while($value = $query->fetchArray(SQLITE3_ASSOC)){
			if(stripos($value['home'], $p->getName() . '_') !== false){
				$homes[] = str_replace($p->getName() . '_', '', $value['home']);
			}
		}

		return $homes;
	}

	/**
	 *
	 * @param Player $p
	 * @param        $name
	 *
	 * @return bool
	 *
	 */
	public function deleteHome(Player $p, $name){
		if($this->homeExists($p, $name)){
			$this->db->query("DELETE FROM homes WHERE home = '{$p->getName()}_{$name}'");

			return true;
		}

		return false;
	}

}