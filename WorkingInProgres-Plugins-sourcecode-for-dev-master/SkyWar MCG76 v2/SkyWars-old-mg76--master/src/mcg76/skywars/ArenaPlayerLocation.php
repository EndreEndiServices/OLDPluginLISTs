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
 * MCG76 ArenaPlayerLocation
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class ArenaPlayerLocation {
	public $pgin;
	public $name;
	public $spawnLocation;
	public $buttonLocation;
	public $buttonWorlName;
	public $kit;
	public $arenaName;
	public $type = "defaullt";
	public function __construct($pg, $name, $pos, $arena) {
		$this->pgin = $pg;
		$this->name = $name;
		$this->spawnLocation = $pos;
		$this->arenaName = $arena;
	}
	public function save() {
		$path = $this->pgin->getDataFolder () . "arena/playerlocation/";
		if (! file_exists ( $path )) {
			// @mkdir ($this->pgin->getDataFolder());
			@mkdir ( $path );
		}
		$data = new Config ( $path . $this->arenaName . "_" . "$this->name.yml", Config::YAML );
		// this should not happen
		$data->set ( "name", $this->name );
		$data->set ( "arenaName", $this->arenaName );
		$data->set ( "type", $this->type );
		
		$data->set ( "button_worldName", $this->buttonWorlName );
		if ($this->buttonLocation != null) {
			$data->set ( "buttonX", $this->buttonLocation->x );
			$data->set ( "buttonY", $this->buttonLocation->y );
			$data->set ( "buttonZ", $this->buttonLocation->z );
		}		
		if ($this->spawnLocation != null) {
			$data->set ( "spawnX", $this->spawnLocation->x );
			$data->set ( "spawnY", $this->spawnLocation->y );
			$data->set ( "spawnZ", $this->spawnLocation->z );
		}
		$data->save ();
		$this->log ( " saved - " . $path . "$this->name.yml" );
	}
	public function testSave() {
		$this->name = "player1";
		$this->arenaName = "test world";
		$this->spawnLocation = new Vector3 ( 128, 128, 128 );
		
		$this->save ();
		$this->log ( " saved player location ");
	}
	public function delete() {
		$path = $this->pgin->getDataFolder () . "arena/playerlocation/";
		$name = $this->name;
		@unlink ( $path . "$name.yml" );
	}
	public static function loadPlayerLocation($plugin) {
		$path = $plugin->getDataFolder () . "arena/playerlocation/";
		if (! file_exists ( $path )) {
			@mkdir ( $this->pgin->getDataFolder () );
			@mkdir ( $path );
			// nothing to load
			return;
		}
		$plugin->getLogger()->info ( "loading player Location on " . $path );
		$playerLocations = [ ];
		$handler = opendir ( $path );
		while ( ($filename = readdir ( $handler )) !== false ) {
			//$plugin->getLogger()->info ( "file - " . $filename );
			//skip sub folders
			if (is_dir($filename)) {
				continue;
			}				
			
			if ($filename != "." && $filename != "..") {
				$plugin->getLogger()->info ( "file - " . $filename );
				$data = new Config ( $path . $filename, Config::YAML );
				
				$xname = $data->get ( "name" );
				$plugin->getLogger()->info($xname);
				// load levels
				//Server::getInstance ()->loadLevel ( $xname );
				//if (($pLevel = Server::getInstance ()->getLevelByName ( $xname )) === null)
				//	continue;
				
				$name = str_replace ( ".yml", "", $filename );				
				$spawnLocation = null;
				if ($data->get ( "spawnX" ) != null) {
					$spawnLocation = new Position ( $data->get ( "spawnX" ), $data->get ( "spawnY" ), $data->get ( "spawnZ" ));
				}
				$buttonLocation = null;
				if ($data->get ( "buttonX" ) != null) {
					$buttonLocation = new Position ( $data->get ( "buttonX" ), $data->get ( "buttonY" ), $data->get ( "buttonZ" ));
				}
				$buttonWorldName = $data->get ( "button_worldName" );				
				$arenaName = $data->get ( "arenaName" );
				$type = $data->get ( "type" );				
				//save
				$key=$data->get ( "spawnX" )." ".$data->get ( "spawnY" )." ".$data->get ( "spawnZ" );
				//$plugin->getLogger()->info($key);
				
				$arenaLocation = new ArenaPlayerLocation ( $plugin, $name, $spawnLocation, $arenaName );
				$arenaLocation->type = $type;
				$arenaLocation->buttonWorlName = $buttonWorldName;
				$arenaLocation->buttonLocation = $buttonLocation;
				
				$playerLocations [$name] = $arenaLocation;
				$plugin->getLogger()->info ( "arena player locations: " . count ( $playerLocations ) );
			}
		}
		closedir ( $handler );
		
		return $playerLocations;
	}
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}