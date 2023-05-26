<?php
namespace MCFTWARS\team;

use pocketmine\level\Position;
class blueTeam extends Team {
	
	public $soldiers = array();
	
	/**
	 * @return Position
	 */
	public function getSpawnPoint() {
		$pos = explode(".", $this->plugin->warDB["spawn"]["blue-team"]["pos"]);
		$level = $this->plugin->getServer()->getLevelByName($this->plugin->warDB["spawn"]["blue-team"]["level"]);
		return new Position($pos[0], $pos[1], $pos[2], $level);
	}
	public function setSpawnPoint(Position $pos) {
		$this->plugin->warDB["spawn"]["blue-team"]["pos"] = (int)$pos->getX().".".(int)$pos->getY().".".(int)$pos->getZ();
		$this->plugin->warDB["spawn"]["blue-team"]["level"] = $pos->getLevel()->getName();
	}
	public function getTeamName() {
		return "블루팀";
	}
}