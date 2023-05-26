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
 * MCG76 ArenaChestLocation
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class ArenaChestLocation {
	public $pgin;
	public $name;
	public $spawnLocation;
	public $arenaName;
	public $type = "defaullt";
	public function __construct($pg, $name, $pos, $arena) {
		$this->pgin = $pg;
		$this->name = $name;
		$this->spawnLocation = $pos;
		$this->arenaName = $arena;
	}
	public function save() {
		$path = $this->pgin->getDataFolder () . "arena/chestlocation/";
		if (! file_exists ( $path )) {
			// @mkdir ($this->pgin->getDataFolder());
			@mkdir ( $path );
		}
		$data = new Config ( $path . $this->arenaName . "_" . "$this->name.yml", Config::YAML );
		// this should not happen
		$data->set ( "name", $this->name );
		$data->set ( "arenaName", $this->arenaName );
		$data->set ( "type", $this->type );
		if ($this->spawnLocation != null) {
			$data->set ( "spawnX", $this->spawnLocation->x );
			$data->set ( "spawnY", $this->spawnLocation->y );
			$data->set ( "spawnZ", $this->spawnLocation->z );
		}
		$data->save ();
		$this->log ( " saved - " . $path . "$this->name.yml" );
	}
	public function testSave() {
		$this->name = "chest1";
		$this->arenaName = "test world";
		$this->spawnLocation = new Vector3 ( 128, 128, 128 );
		
		$this->save ();
		$this->log ( " saved chest " );
	}
	public function delete() {
		$path = $this->pgin->getDataFolder () . "arena/chestlocation/";
		$name = $this->name;
		@unlink ( $path . "$name.yml" );
	}
	public static function loadChestLocation($plugin) {
		$path = $plugin->getDataFolder () . "arena/chestlocation/";
		if (! file_exists ( $path )) {
			@mkdir ( $this->pgin->getDataFolder () );
			@mkdir ( $path );
			// nothing to load
			return;
		}
		$plugin->getLogger()->info ( "loading chest Location on " . $path );
		$arenaChestLocations = [ ];
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
				// load levels
				//Server::getInstance ()->loadLevel ( $xname );
				//if (($pLevel = Server::getInstance ()->getLevelByName ( $xname )) === null)
				//	continue;				
				$name = str_replace ( ".yml", "", $filename );
				$spawnLocation = null;
				if ($data->get ( "spawnX" ) != null) {
					$spawnLocation = new Position ( $data->get ( "spawnX" ), $data->get ( "spawnY" ), $data->get ( "spawnZ" ));
				}
				
				$arenaName = $data->get ( "arenaName" );
				$type = $data->get ( "type" );
				
				$chestLocation = new ArenaChestLocation ( $plugin, $name, $spawnLocation, $arenaName );
				$chestLocation->type = $type;
				$arenaChestLocations [$name] = $chestLocation;
				$plugin->getLogger()->info ( "arena chest locations: " . count ( $arenaChestLocations ) );
			}
		}
		closedir ( $handler );		
		return $arenaChestLocations;
	}
	
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}