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

class WarpAPI {
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
		$this->db = new \SQLite3($base->plugin->databasesFolder() . "warps.db");
		$this->db->query("CREATE TABLE IF NOT EXISTS warps (warp TEXT, x INT, y INT, z INT, level TEXT)");
	}

	/**
	 *
	 * @param $name
	 * @param $x
	 * @param $y
	 * @param $z
	 * @param $level
	 *
	 */
	public function setWarp($name, $x, $y, $z, $level){
		$pre = $this->db->prepare("INSERT INTO warps (warp, x, y, z, level) VALUES (:warp, :x, :y, :z, :level)");
		$pre->bindValue(":warp", "{$name}");
		$pre->bindValue(":x", $x);
		$pre->bindValue(":y", $y);
		$pre->bindValue(":z", $z);
		$pre->bindValue(":level", $level);
		$pre->execute();
		$pre->close();
	}

	/**
	 *
	 * @param $name
	 *
	 * @return null|Position
	 *
	 */
	public function getWarpPosition($name){
		if($this->warpExists($name)){
			$query = $this->db->query("SELECT x, y, z, level FROM warps WHERE warp = '{$name}'");
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
	 * @param $name
	 *
	 * @return bool
	 *
	 */
	public function warpExists($name){
		$query = $this->db->query("SELECT warp FROM warps WHERE warp = '{$name}'");

		return $query->fetchArray(SQLITE3_ASSOC) != 0;
	}

	/**
	 *
	 * @return string[]
	 *
	 */
	public function getWarps(){
		$query = $this->db->query("SELECT * FROM warps");
		$warps = [];
		while($value = $query->fetchArray(SQLITE3_ASSOC)){
			$warps[] = $value['warp'];
		}

		return $warps;
	}

	/**
	 *
	 * @param $name
	 *
	 * @return bool
	 *
	 */
	public function deleteWarp($name){
		if($this->warpExists($name)){
			$this->db->query("DELETE FROM warps WHERE warp = '{$name}'");

			return true;
		}

		return false;
	}

}