<?php
namespace MCFTWARS\team;

use MCFTWARS;
use pocketmine\level\Position;
class redTeam extends Team {
	
	public $soldiers = array();
	
	public function getSpawnPoint() {

		$pos = explode(".", $this->plugin->warDB["spawn"]["red-team"]["pos"]);
		$level = $this->plugin->getServer()->getLevelByName($this->plugin->warDB["spawn"]["red-team"]["level"]);
		return new Position($pos[0], $pos[1], $pos[2], $level);
	}
	public function setSpawnPoint(Position $pos) {
		$this->plugin->warDB["spawn"]["red-team"]["pos"] = (int)$pos->getX().".".(int)$pos->getY().".".(int)$pos->getZ();
		$this->plugin->warDB["spawn"]["red-team"]["level"] = $pos->getLevel()->getName();
	}
	public function getTeamName() {
		return "레드팀";
	}
}