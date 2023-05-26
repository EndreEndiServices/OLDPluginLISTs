<?php

namespace mcg76\skywars;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\block\Chest;
use pocketmine\item\Item;

/**
 * MCG76 ArenaScoreboard
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class ArenaScoreboard {
	// #POS
	public $pos1;
	public $pos2;
	// #PLUGIN
	public $pgin;
	// #ARENA NAME - World Name
	public $name;
	public $levelSpawnLocation;
	// # INUSE or EMPTY
	public $levelName;
	public $status;	
	//Capacity
	public $maxPlayers;
	public $maxplayerSpawnLocations;
	// #Resources
	public $playerSpawnLocations = [ ];
	public $occupiedSpawnLocations = [ ];
	public $chestLocations = [ ];
	public $gameplayers = [ ];
	
	public function __construct(SkyBlockPlugIn $pg, $name) {
		$this->pgin = $pg;
		$this->name = $name;
	}
	public function save() {
		$path = $this->pgin->getDataFolder () . "arena/";
		$data = new Config ( $path . "$this->name.yml", Config::YAML );		
		$data->set ( "arenaName", $this->name );
		$data->set ( "levelName", $this->levelName );
		$data->set ( "status", $this->status );				
		$data->set ( "maxPlayers", $this->maxPlayers );
		$data->set ( "maxplayerSpawnLocations", $this->maxplayerSpawnLocations );
		
		if ($this->levelSpawnLocation != null) {
			$data->set ( "levelSpawnLocationX", $this->levelSpawnLocation->x );
			$data->set ( "levelSpawnLocationY", $this->levelSpawnLocation->y );
			$data->set ( "levelSpawnLocationZ", $this->levelSpawnLocation->z );
		}		
		if ($this->pos1 != null) {
			$data->set ( "pos1X", $this->pos1->x );
			$data->set ( "pos1Y", $this->pos1->y );
			$data->set ( "pos1Z", $this->pos1->z );
		}
		if ($this->pos2 != null) {
			$data->set ( "pos2X", $this->pos2->x );
			$data->set ( "pos2Y", $this->pos2->y );
			$data->set ( "pos2Z", $this->pos2->z );
		}
		
		$data->save ();
	}
	public function addPlayerSpawnLocation(Position $pos) {
		$key = $pos . x . "_" . $pos . y . "_" . $pos . z;
		if (! isset ( $this->playerSpawnLocations [$key] )) {
			$this->playerSpawnLocations [$key] = $pos;
		}
	}
	public function deletePlayerSpawnLocation(Position $pos) {
		$key = $pos . x . "_" . $pos . y . "_" . $pos . z;
		unset ( $this->playerSpawnLocations [$key] );
	}
	
	public function listPlayerSpawnLocation() {
		$out = "";
		$i = 1;
		foreach ( $this->playerSpawnLocations as $pos ) {
			$key = $i . ". " . $pos . x . "_" . $pos . y . "_" . $pos . z . "\n";
			$out = $out . $key;
			$i ++;
		}
		return $out;
	}
	
	public function listChestLocation() {
		$out = "";
		$i = 1;
		foreach ( $this->playerSpawnLocations as $pos ) {
			$key = $i . ". " . $pos . x . "_" . $pos . y . "_" . $pos . z . "\n";
			$out = $out . $key;
			$i ++;
		}
		return $out;
	}
		
	public function delete() {
		$path = $this->pgin->getDataFolder () . "arena/";
		$name = $this->name;
		@unlink ( $path . "$name.yml" );
	}
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}