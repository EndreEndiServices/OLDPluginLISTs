<?php
namespace MCFTWARS;

use pocketmine\Player;
use MCFTWARS\team\Team;
use MCFTWARS\team\redTeam;
use MCFTWARS\team\blueTeam;

class soldier {
	
	private $player, $team;
	
	public function __construct(Player $player) {
		$this->player = $player;
	}
	public function getPlayer() {
		return $this->player;
	}
	public function setTeam(Team $team) {
		$this->team = $team;
	}
	/**
	 * @return redTeam|blueTeam
	 */
	public function getTeam() {
		return $this->team;
	}
}