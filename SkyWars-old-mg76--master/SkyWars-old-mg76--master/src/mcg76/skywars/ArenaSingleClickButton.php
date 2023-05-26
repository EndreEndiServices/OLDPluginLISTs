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
 * MCG76 ArenaSingleClickButton
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class ArenaSingleClickButton {
	public $pgin;
	public $name;
	public $spawnLocation;
	public $arenaName;
	public $type = "join";
	public $action = "teleport";
	public function __construct($pg, $name, $pos, $arena) {
		$this->pgin = $pg;
		$this->name = $name;
		$this->spawnLocation = $pos;
		$this->arenaName = $arena;
	}
	public function save() {
		$path = $this->pgin->getDataFolder () . "arena/singleclickbutton/";
		if (! file_exists ( $path )) {
			// @mkdir ($this->pgin->getDataFolder());
			@mkdir ( $path );
		}
		$data = new Config ( $path . $this->arenaName . "_" . "$this->name.yml", Config::YAML );
		// this should not happen
		$data->set ( "name", $this->name );
		$data->set ( "arenaName", $this->arenaName );
		$data->set ( "type", $this->type );
		$data->set ( "action", $this->action );
		if ($this->spawnLocation != null) {
			$data->set ( "spawnX", $this->spawnLocation->x );
			$data->set ( "spawnY", $this->spawnLocation->y );
			$data->set ( "spawnZ", $this->spawnLocation->z );
		}
		$data->save ();
		$this->log ( " saved - " . $path . "$this->name.yml" );
	}
	public function testSave() {
		$this->name = "PlayerClickableButton_1";
		$this->arenaName = "skywarsbase1";
		$this->type = "join";
		$this->action = "teleport";
		$this->spawnLocation = new Vector3 ( 128, 128, 128 );		
		$this->save ();
		$this->log ( "-saved single click button " );
	}
	public function delete() {
		$path = $this->pgin->getDataFolder () . "arena/singleclickbutton/";
		$name = $this->name;
		@unlink ( $path . "$name.yml" );
	}
	public static function loadSingleClickButton($plugin) {
		$path = $plugin->getDataFolder () . "arena/singleclickbutton/";
		if (! file_exists ( $path )) {
			@mkdir ( $this->pgin->getDataFolder () );
			@mkdir ( $path );
			// nothing to load
			return;
		}
		$plugin->getLogger()->info ( "loading single click button on " . $path );
		$singleClickButtons = [ ];
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
				$action = $data->get ( "action" );
				
				$clickButton = new ArenaSingleClickButton ( $plugin, $name, $spawnLocation, $arenaName );
				$clickButton->type = $type;
				$clickButton->action = $action;
				$clickButton->arenaName = $arenaName;
				//save key
				$key=$data->get ( "spawnX" )." ".$data->get ( "spawnY" )." ".$data->get ( "spawnZ" );
				$plugin->getLogger()->info($key);
				
				$singleClickButtons [$key] = $clickButton;
				$plugin->getLogger()->info ( "single click buttons: " . count ( $singleClickButtons ) );
			}
		}
		closedir ( $handler );		
		return $singleClickButtons;
	}
	
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}