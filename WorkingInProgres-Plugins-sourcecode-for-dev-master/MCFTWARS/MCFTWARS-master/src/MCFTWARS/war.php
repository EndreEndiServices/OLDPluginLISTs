<?php

namespace MCFTWARS;

use pocketmine\Player;
use MCFTWARS\team\redTeam;
use MCFTWARS\team\blueTeam;
use pocketmine\utils\TextFormat;
use pocketmine\level\Position;

class war {
	private $plugin, $isplay = false, $eventlistener;
	public $redteam, $blueteam;
	private $soldiers = [ ];
	public function __construct(MCFTWARS $plugin) {
		$this->plugin = $plugin;
		$this->redteam = new redTeam ( $plugin );
		$this->blueteam = new blueTeam ( $plugin );
		$this->eventlistener = $plugin->eventlistener;
	}
	public function participate(Player $player) {
		$soldier = new soldier ( $player );
		if ($player->isCreative()) {
			$player->setGamemode(0);
		}
		if (count($this->redteam->soldiers) < count($this->blueteam->soldiers)) {
			$soldier->setTeam ( $this->redteam );
			$color = TextFormat::RED;
		} else {
			$soldier->setTeam ( $this->blueteam );
			$color = TextFormat::BLUE;
		}
		$soldier->getTeam()->soldiers[$player->getName()] = $soldier;
		$player->setNameTag($color."[{$soldier->getTeam()->getTeamName()}] {$player->getName()}");
		$player->teleport ( $soldier->getTeam ()->getSpawnPoint () );
		$this->eventlistener->giveRandomItem($player);
		$this->soldiers [$player->getName ()] = $soldier;
	}
	public function isPlay() {
		return $this->isplay;
	}
	/**
	 *
	 * @param Player|string $player        	
	 * @return soldier|null
	 */
	public function getSoldier($player) {
		if (! $player instanceof Player) {
			$player = $this->plugin->getServer ()->getPlayer ( $player );
		}
		if (isset ( $this->soldiers [$player->getName ()] )) {
			return $this->soldiers [$player->getName ()];
		} else {
			return null;
		}
	}
	public function getSoldiers() {
		return $this->soldiers;
	}
	/**
	 *
	 * @param Player|string $player        	
	 * @return boolean
	 */
	public function leaveWar($player) {
		if (! $player instanceof Player) {
			$player = $this->plugin->getServer ()->getPlayer ( $player );
			
		}
		if ($this->getSoldier ( $player ) == null) {
			return false;
		} else {
			$this->getSoldier($player)->getPlayer()->teleport($this->getLobby());
			$player->getInventory()->clearAll();
			unset($this->eventlistener->touchinfo[$player->getName()]);
			unset($this->getSoldier($player)->getTeam()->soldiers[$player->getName()]);
			unset ( $this->soldiers [$player->getName ()] );
		}
		return true;
	}
	public function StartWar() {
		$this->isplay = true;
		$this->plugin->getServer ()->broadcastMessage ( TextFormat::DARK_AQUA . $this->plugin->get ( "default-prefix" ) . " " . $this->plugin->get ( "start-war" ) );
	}
	public function EndWar() {
		$this->isplay = false;
		$this->plugin->getServer ()->broadcastMessage ( TextFormat::DARK_AQUA . $this->plugin->get ( "default-prefix" ) . " " . $this->plugin->get ( "end-war" ) );
		if (isset($this->soldiers)) {
			foreach ( $this->soldiers as $soldier ) {
				$this->leaveWar($soldier->getPlayer());
			}
		}
	}
	public function setLobby(Position $pos) {
		$this->plugin->warDB ["spawn"] ["lobby"] ["pos"] = (int)$pos->getX().".".(int)$pos->getY().".".(int)$pos->getZ();
		$this->plugin->warDB ["spawn"] ["lobby"] ["level"] = $pos->getLevel ()->getName ();
	}
	public function getLobby() {
		$pos = explode ( ".", $this->plugin->warDB ["spawn"] ["lobby"] ["pos"] );
		$level = $this->plugin->getServer ()->getLevelByName ( $this->plugin->warDB ["spawn"] ["lobby"] ["level"] );
		return new Position ( $pos [0], $pos [1], $pos [2], $level );
	}
}