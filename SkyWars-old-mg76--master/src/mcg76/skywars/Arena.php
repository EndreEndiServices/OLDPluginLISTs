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
 * MCG76 Arena
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class Arena {
	// #POS
	public $pos1;
	public $pos2;
	// #PLUGIN
	public $pgin;
	// #ARENA NAME - World Name
	public $name;
	public $description;
	public $levelSpawnLocation;
	// # INUSE or EMPTY
	public $levelName;
	public $status;
	public $pvpon = false;
	public $templateWorldName;
	public $c = [];
	// Capacity
	public $maxPlayers;
	public $maxplayerSpawnLocations;
	// #Resources
	public $playerSpawnLocations = [ ];
	public $occupiedSpawnLocations = [ ];
	public $chestLocations = [ ];
	public $gameplayers = [ ];
	public function __construct(SkyWarsPlugIn $pg, $name) {
		$this->pgin = $pg;
		$this->name = $name;
	}
	public function save() {
		$path = $this->pgin->getDataFolder () . "arena/";
		if (! file_exists ( $path )) {
			//@mkdir ( $this->pgin->getDataFolder () );
			@mkdir ( $path );
		}
		$data = new Config ( $path . "$this->name.yml", Config::YAML );
		
		$data->set ( "arenaName", $this->name );
		$data->set ( "levelName", $this->levelName );
		$data->set ( "description", $this->description );
		$data->set ( "status", $this->status );
		$data->set ( "pvpon", $this->pvpon );
		$data->set ( "templateWorldName", $this->templateWorldName );
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
		$this->log ( " saved - " . $path . "$this->name.yml" );
	}
	public function testSave() {
		$this->name = "skyblockbase";
		$this->levelName = "template_skywars";
		$this->status = "IN-USE";
		$this->maxPlayers = "5";
		$this->maxplayerSpawnLocations = "5";
		$this->pos1 = new Position ( 128, 128, 128 );
		$this->pos2 = new Position ( 128, 128, 128 );
		$this->levelSpawnLocation = new Position ( 128, 128, 128 );
		$this->templateWorldName = "world";
		
		$this->save();	
		$this->log ( " saved arena!" );
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
	
	public static function loadArenas($plugin) {
		$this->c = [];
                if(!is_file($this->getFolderData() . "arena/")){
			 @mkdir($this->getFolderData());
			//nothing to load
			return;
		}
		$plugin->getLogger()->info ( "loading arenas on " . $path );		
		$handler = opendir ( $path );
		while ( ($filename = readdir ( $handler )) !== false ) {
			//skip sub folders
			if (is_dir($filename)) {
				continue;
			}
			//skip folders
			if ($filename=="chestlocation" || $filename=="playerlocation" || $filename=="singleclickbutton" || $filename=="teamclickbutton") {
				continue;
			}						
			if ($filename != "." && $filename != "..") {
				$plugin->getLogger()->info ( "file - " . $filename );
				
				$data = new Config ( $path . $filename, Config::YAML );
				
				$xname = $data->get ( "arenaName" );
				$plugin->getLogger()->info($xname);
				// load levels
				Server::getInstance ()->loadLevel ( $xname );
				if (($pLevel = Server::getInstance ()->getLevelByName ( $xname )) === null)
					continue;
				
				$name = str_replace ( ".yml", "", $filename );
				
				$spawnLocation = null;
				if ($data->get ( "levelSpawnLocationX" ) != null) {
					$spawnLocation = new Position ( $data->get ( "levelSpawnLocationX" ), $data->get ( "levelSpawnLocationY" ), $data->get ( "levelSpawnLocationZ" ), $pLevel );
				}
				$pos1 = null;
				if ($data->get ( "pos1X" ) != null) {
					$pos1 = new Position ( $data->get ( "pos1X" ), $data->get ( "pos1Y" ), $data->get ( "pos1Z" ), $pLevel );
				}
				$pos2 = null;
				if ($data->get ( "pos2X" ) != null) {
					$pos2 = new Position ( $data->get ( "pos2X" ), $data->get ( "pos2Y" ), $data->get ( "pos2Z" ), $pLevel );
				}

				$description = $data->get ( "description");
				$pvpon = $data->get ( "pvpon");				
				$status = $data->get ( "status" );
				$name = $data->get ( "arenaName" );
				$levelName = $data->get ( "levelName" );
				$templateWorldName = $data->get ( "templateWorldName" );
				$maxPlayers = $data->get ( "maxPlayers" );
				$maxplayerSpawnLocations = $data->get ( "maxplayerSpawnLocations" );
				
				$arena = new Arena ( $plugin, $name );
				$arena->levelName = $levelName;
				$arena->templateWorldName = $templateWorldName;
				$arena->maxPlayers = $maxPlayers;
				$arena->maxplayerSpawnLocations = $maxplayerSpawnLocations;
				$arena->pos1 = $pos1;
				$arena->pos2 = $pos2;
				$arena->status = $status;
				$arena->pvpon = $pvpon;
				$arena->description = $description;
								
				$plugin->arenas [$name] = $arena;								
				$plugin->getLogger()->info ( "playworld count: " . count ( $plugin->arenas ) );
			}
		}
		closedir ( $handler );
	}

	public function toString () {
		$out="<arena>";
	}
	
	
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}
