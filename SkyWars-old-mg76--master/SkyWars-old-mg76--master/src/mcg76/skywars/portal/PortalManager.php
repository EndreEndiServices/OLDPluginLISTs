<?php

namespace mcg76\skywars\portal;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\utils\Config;
use mcg76\skywars\SkyWarsPlugIn;
use pocketmine\Player;
use mcg76\skyblock\BlockBuilder;

/**
 * MCG76 PortalManager
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 @author minecraftgenius76@gmail.com
 *
 */

class PortalManager {
	private $pgin;
	public function __construct(SkyWarsPlugIn $pg) {
		$this->pgin = $pg;
	}
	public function trigger(Player $e) {
		foreach ( $this->pgin->portals as $p ) {
			if ($p->inside ( $e->getPosition() )) {
				$p->teleport ( $e->getPosition());
				break;
			}
		}
	}
	public function loadPortals() {
		$path = $this->pgin->getDataFolder () . "portals/";
		if (! file_exists ( $path )) {
			@mkdir ( $this->pgin->getDataFolder () );
			@mkdir ( $path );
			return;
		}
		$handler = opendir ( $path );
		while ( ($filename = readdir ( $handler )) !== false ) {			
			if ($filename != "." && $filename != "..") {				
				$data = new Config ( $path . $filename, Config::YAML );
				
				$this->log("load level portal :".$path.$filename);
				$this->log("  world :".$data->get ( "pointLevel"));
				$this->log("  destination :".$data->get ( "destinationLevel"));
				
				if (($pLevel = Server::getInstance ()->getLevelByName ( $data->get ( "pointLevel" ) )) === null)
					continue;
				if (($dLevel = Server::getInstance ()->getLevelByName ( $data->get ( "destinationLevel" ) )) === null)
					continue;
				
				$name = str_replace ( ".yml", "", $filename );
				$p1 = new Position ( $data->get ( "point1X" ), $data->get ( "point1Y" ), $data->get ( "point1Z" ), $pLevel );
				$p2 = new Position ( $data->get ( "point2X" ), $data->get ( "point2Y" ), $data->get ( "point2Z" ), $pLevel );
				//$destination = new Position ( $data->get ( "destinationX" ), $data->get ( "destinationY" ), $data->get ( "destinationZ" ), $dLevel );
				$destination = $data->get ( "destination" );
				$this->pgin->portals [$name] = new Portal ($p1,$p2,$name,$destination);
				
				$this->log("  position: ".$p1. " | ".$p2 . " ".$destination);
				//rebuild the portal
				//$level = PortalManager::getLevel($this->pgin->getServer(), $name);
				if ($pLevel!=null) {
				  $builder = new BlockBuilder($this->pgin);
				  $p1->y = $p1->y+1;
				  $builder->renderPortal($pLevel, $p1, $destination);
				}
			}
		}
		closedir ( $handler );
	}
	public function addPortal(Portal $p) {
		$this->pgin->portals [$p->name] = $p;
		$p->save ( $this->pgin->getDataFolder () . "portals/" );
		$this->log("save portal ".$p->name . " | ".$p->destination);
	}
	public function deletePortal($name) {
		if (! isset ( $this->pgin->portals [$name] ))
			return false;
		$this->pgin->portals [$name]->delete ( $this->pgin->getDataFolder () . "portals/" );
		unset ( $this->pgin->portals [$name] );
		return true;
	}
	
	public static function isLevelLoaded(Player $player, $levelhome) {
		$level = null;
		if (!$player->getServer()->isLevelGenerated($levelhome)) {
			$player->sendMessage("unable to find world name [".$levelhome."]");
			return ;
		}
		
		if (!$player->getServer()->isLevelLoaded($levelhome)) {
			$player->getServer()->loadLevel($levelhome);
		}
		
		if ($player->getServer()->isLevelLoaded($levelhome)) {
			$player->sendMessage("level loaded -".$levelhome);
			$level = $player->getServer()->getLevelByName($levelhome);
			if ($level==null) {
				//$this->log("level not found: ".$levelhome);
				$player->sendMessage("level not found: ".$levelhome);
				return null;
			}
		}
		return $level;
	}
	
	public static function getLevel($server, $levelhome) {
		$level = null;
		if (!$server->isLevelGenerated($levelhome)) {
			//$player->sendMessage("unable to find world name [".$levelhome."]");
			return ;
		}
	
		if (!$server->isLevelLoaded($levelhome)) {
			$server->loadLevel($levelhome);
		}
	
		if ($server->isLevelLoaded($levelhome)) {
			//$player->sendMessage("level loaded -".$levelhome);
			$level = $server->getLevelByName($levelhome);
			if ($level==null) {
				//$this->log("level not found: ".$levelhome);
				//$player->sendMessage("level not found: ".$levelhome);
				return null;
			}
		}
		return $level;
	}
	
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}