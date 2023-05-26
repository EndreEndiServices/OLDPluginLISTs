<?php

namespace mcg76\skywars;

use mcg76\skywars\ArenaPlayerLocation;
use mcg76\skywars\ArenaChestLocation;
use mcg76\skywars\ArenaKit;
use mcg76\skywars\Arena;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3 as Vector3;

/**
 * MCG76 SkyWarsConfiguration
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class SkyWarsConfiguration {
	private $pgin;
	public function __construct(SkyWarsPlugIn $pg) {
		$this->pgin = $pg;
	}
	public function loadConfiguration() {
		//$this->pgin->saveDefaultConfig ();
		//$this->pgin->reloadConfig ();
				
		$path = $this->pgin->getDataFolder();
		if (! file_exists ( $path )) {
			@mkdir ($this->pgin->getDataFolder());
		}		
		//@mkdir ( $this->pgin->getDataFolder() );		
		$this->pgin->config = (new Config ( $this->pgin->getDataFolder () . "config.yml", Config::YAML, array (
				"districtborder" => "off",
				"message" => "This is end of the skywars district",
				"game_play_countdown_wait_time" => "5",
				"spawnmods" => "yes",
				"skywars_base_world" => "skywarsbase1",
				"skywars_play_world" => "skywarsbase1_play",				
				"skywars_lobby_world" => "skywarshome",
				"skywars_lobby_x" => "153",
				"skywars_lobby_y" => "58",
				"skywars_lobby_z" => "151",
				"skywars_shop_sales1_x" => "144",
				"skywars_shop_sales1_y" => "57",
				"skywars_shop_sales1_z" => "138",
				"skywars_shop_sales2_x" => "171",
				"skywars_shop_sales2_y" => "63",
				"skywars_shop_sales2_z" => "164",
				"blocks" => "3000" 
		) ))->getAll ();
		
		//$this->createTestData();
		// load arena profile
		// $this->loadArenaProfile();
		// load spawn location
		// $this->loadArenaSpawnLocations ();		
		$this->loadArenaConfigurations();		
	}

	public function loadArenaConfigurations() {
		$this->pgin->arenas = Arena::loadArenas($this->pgin);
		$this->pgin->arenaPlayerSpawnLocations = ArenaPlayerLocation::loadPlayerLocation($this->pgin);
		$this->pgin->arenaPlayerSpawnLocationsSingle = $this->pgin->arenaPlayerSpawnLocations;
		$this->pgin->arenaPlayerSpawnLocationsTeam = $this->pgin->arenaPlayerSpawnLocations;
		//$this->pgin->arenaChestSpawnLocations = ArenaChestLocation::loadChestLocation($this->pgin);
		//$this->pgin->singleClickButtons = ArenaSingleClickButton::loadSingleClickButton($this->pgin);
		//$this->pgin->teamClickButtons = ArenaTeamClickButton::loadTeamClickButton($this->pgin);
		
		//create one to one mapping of click button and spawn location 
		foreach ($this->pgin->arenaPlayerSpawnLocations as $clickbtn) {
			//$pos = array_pop($this->pgin->arenaPlayerSpawnLocations);			
			$pos = $clickbtn->spawnLocation;
			$key = $clickbtn->buttonLocation->x . "_" . $clickbtn->buttonLocation->y . "_" . $clickbtn->buttonLocation->z;
			$this->pgin->mappingClickButtonsToSpawnLocations[$key] = $clickbtn;
			$this->log("map btn:".$key." to -> ". $pos->x." ".$pos->y." ".$pos->z);
		}
		$this->log("mapping total =".count($this->pgin->mappingClickButtonsToSpawnLocations));
	}
	
	
	public function createTestData() {
// 		$this->log("sample arena");
// 		$sampleArena = new Arena ( $this->pgin, "skywar1" );
// 		$sampleArena->testSave();
		
// 		$this->log("sample player");
// 		$samplePlayerLocation = new ArenaPlayerLocation ( $this->pgin, "player1", new Vector3 ( 128, 128, 128 ), "skywar1" );
// 		$samplePlayerLocation->testSave();
		
// 		$this->log("sample chest");
// 		$sampleChestLocation = new ArenaChestLocation($this->pgin, "chest1", new Vector3 ( 128, 128, 128 ), "skywar1" );
// 		$sampleChestLocation->testSave();

// 		Arena::loadArenas($this->pgin);
// 		ArenaPlayerLocation::loadPlayerLocation($this->pgin);
// 		ArenaChestLocation::loadChestLocation($this->pgin);		
		
// 		$teamButton = new ArenaTeamClickButton($this->pgin, "team1_join_button", new Vector3 ( 128, 128, 128 ), "skywar1");
// 		$teamButton->testSave();
		
// 		$singleButton =  new ArenaSingleClickButton($this->pgin, "player1_join_button", new Vector3 ( 128, 128, 128 ), "skywar1");
// 		$singleButton->testSave();
		
	}
		
	public function loadArenaProfile() {
		$path = $this->pgin->getDataFolder() . "arena/";
		if (! file_exists ( $path )) {
			@mkdir ( $this->pgin->getDataFolder() );
			@mkdir ( $path );
			return;
		}
		$this->log ( "loading arena profiles on " . $path );
		
		$handler = opendir ( $path );
		while ( ($filename = readdir ( $handler )) !== false ) {
			$this->log ( "file - " . $filename );
			
			if ($filename != "." && $filename != "..") {
				$data = new Config ( $path . $filename, Config::YAML );
				
				$xname = $data->get ( "arenaName" );
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
				
				if ($data->get ( "chestX" ) != null) {
					$chestLocation = new Position ( $data->get ( "chestX" ), $data->get ( "chestY" ), $data->get ( "chestZ" ), $pLevel );
				}
				$status = $data->get ( "status" );
				$name = $data->get ( "arenaName" );
				$levelName = $data->get ( "levelName" );
				$templateWorldName = $data->get ( "templateWorldName" );
				$maxPlayers = $data->get ( "maxPlayers" );
				$maxplayerSpawnLocations = $data->get ( "maxplayerSpawnLocations" );
				
				$arena = new Arena ( $this->pgin, $name );
				$arena->levelName = $levelName;
				$arena->templateWorldName = $templateWorldName;
				$arena->maxPlayers = $maxPlayers;
				$arena->maxplayerSpawnLocations = $maxplayerSpawnLocations;
				$arena->pos1 = $pos1;
				$arena->pos2 = $pos2;
				$arena->status = $status;
				
				$this->pgin->maps [$xname] = $map;
				$this->log ( "playworld count: " . count ( $this->pgin->maps ) );
			}
		}
		closedir ( $handler );
	}
	
	public function loadArenaSpawnLocations() {
		$path = $this->pgin->getDataFolder () . "arena/playerlocation/";
		if (! file_exists ( $path )) {
			@mkdir ( $this->pgin->getDataFolder () );
			@mkdir ( $path );
			return;
		}
		$this->log ( "load arena player spawn location " . $path );
		
		$handler = opendir ( $path );
		while ( ($filename = readdir ( $handler )) !== false ) {
			$this->log ( "file - " . $filename );
			
			if ($filename != "." && $filename != "..") {
				$data = new Config ( $path . $filename, Config::YAML );
				
				// $this->log ( "loading : " . $data->get ( "name" ) );
				$xname = $data->get ( "name" );
				$levelname = $data->get ( "levelname" );
				
				// load levels
				Server::getInstance ()->loadLevel ( $levelname );
				if (($pLevel = Server::getInstance ()->getLevelByName ( $levelname )) === null)
					continue;
				
				$name = str_replace ( ".yml", "", $filename );
				$spawnLocation = new Position ( $data->get ( "spawnX" ), $data->get ( "spawnY" ), $data->get ( "spawnZ" ), $pLevel );
				$chestLocation = new Position ( $data->get ( "chestX" ), $data->get ( "chestY" ), $data->get ( "chestZ" ), $pLevel );
				// $xname = $data->get ( "name" );
				$status = $data->get ( "status" );
				$chestFilled = $data->get ( "chestFilled" );
				$levelname = $data->get ( "levelname" );
				$ownerName = $data->get ( "ownername" );
				$shared = $data->set ( "shared" );
				
				$this->ownerName = $ownerName;
				$this->log ( "loading player profile : " . $name );
				$map = new SkyBlockPlayer ( $this->pgin, $name );
				$map->spawnLocation = $spawnLocation;
				$map->chestLocation = $chestLocation;
				$map->chestFilled = $chestFilled;
				$map->status = $status;
				$map->levelname = $levelname;
				$map->ownerName = $ownerName;
				$map->shared = $shared;
				
				$this->log ( "$levelname: " . $levelname . " shared:" . $shared . " owner " . $ownerName );
				$this->skyplayers [$xname] = $map;
				$this->log ( "playworld count: " . count ( $this->skyplayers ) );
			}
		}
		closedir ( $handler );
	}

	
	/**
	 * Logging util function
	 *
	 * @param unknown $msg        	
	 */
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}