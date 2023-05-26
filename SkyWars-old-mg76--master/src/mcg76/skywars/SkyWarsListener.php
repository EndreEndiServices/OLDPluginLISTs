<?php

namespace mcg76\skywars;

use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\tile\Sign;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\block\Chest;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\Player;
use mcg76\skywars\map\SkyBlockGenerator;
use mcg76\skywars\map\SuperFlat;
use mcg76\skywars\portal\PortalManager;
use mcg76\skywars\portal\Portal;
use mcg76\skywars\SkyWarsCommand;
use mcg76\skywars\SkyWarsConfiguration;

/**
 * MCG76 SkyWarsListener
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class SkyWarsListener implements Listener {
	public $pgin;
	public function __construct(SkyWarsPlugIn $pg) {
		$this->pgin = $pg;
	}
	public function onPlayerInteract(PlayerInteractEvent $event) {
		$b = $event->getBlock ();
		$player = $event->getPlayer ();
		if ($this->pgin->pos_display_flag == 1) {
			// $event->getPlayer()->sendMessage("block TOUCHED: [".$b."]");
			$event->getPlayer ()->sendMessage ( "block TOUCHED: [x=" . $b->x . " y=" . $b->y . " z=" . $b->z . "]" );
		}
		
		$this->handleSingleButtonClick ( $b, $player );
		$this->handleTeamButtonClick ( $b, $player );
		
		// if ($this->pgin->gamemode == 2) {
		if ($event->getBlock ()->getID () == 54) {
			$player = $event->getPlayer ();
			if (isset ( $this->pgin->skyplayers [$player->getName ()] )) {
				// $map = $this->pgin->skyplayers [$player->getName ()];
				// if ($map != null) {
				// if ($map->chestFilled == null || $map->chestFilled == "no") {
				// $map->getRandomChestItems ( $event->getPlayer (), $event->getBlock () );
				// $map->chestFilled == "yes";
				// $map->save ();
				// }
				// }
				// ArenaKit::getSkywarKit ( $player );
				ArenaKit::getRandomChestItems ( $player, $event->getBlock () );
			}
		}
		// }
	}
	public function handleSingleButtonClick($b, Player $player) {
		$key = $b->x . "_" . $b->y . "_" . $b->z;
		
		if (isset ( $this->pgin->mappingClickButtonsToSpawnLocations [$key] )) {
			
			$p1 = $this->pgin->mappingClickButtonsToSpawnLocations [$key];
			// $btn = $this->pgin->singleClickButtons [$key];
			$player->sendMessage ( "clicked single " . $key );
			if ($this->pgin->gamemode == 2) {
				$message = "Skywars arena is busy, game in progress. please wait!";
				$player->sendMessage ( $message );
				return;
			}
			$playworld = $p1->arenaName . "_play";
			
			$this->log ( "arena player spawn =" . count ( $this->pgin->arenaPlayerSpawnLocationsSingle ) );
			$this->log ( "countDownCounter =" . $this->pgin->countDownCounter );
			$this->log ( "select arena " . $p1->arenaName );
			
			if (count ( $this->pgin->mappingClickButtonsToSpawnLocations ) > 0) {
				// $playworld = "skywarsbase1_play";
				// firs time click
				if ($this->pgin->countDownCounter <= 0) {
					// prepare new game world
					$cmd = new SkyWarsCommand ( $this->pgin );
					
					// $this->pgin->getConfig ()->get ( "skywars_play_world" );
					if ($playworld == null) {
						$playworld = "skywarsplay_" . $player->getName ();
					}
					// just in case remove old world
					$success = $cmd->deletePlayWorld ( $playworld );
					
					$message = "preparing new Skywars play world...";
					$this->pgin->getServer ()->broadcastMessage ( $message );
					$success = $cmd->addPlayWorld ( $p1->arenaName, $playworld );
					if ($success) {
						$message = "Skywars play world is ready...";
						$this->pgin->getServer ()->broadcastMessage ( $message );
					}
					$waitTime = $this->pgin->getConfig ()->get ( "game_play_countdown_wait_time" );
					if ($waitTime == null) {
						$waitTime = 3;
					}
					// 5 minutes
					$this->pgin->startPlayTime = (time () + $waitTime);
					$this->pgin->countDownCounter = $waitTime;
				}
				
				// change click button color
				$builder = new BlockBuilder ( $this->pgin );
				// $block = $player->level->getBlock(new Position($b->x,$b->y,$b->z));
				// 51 fire, 133 emeral block
				// $builder->updateBlock($block, $player->level, 51);
				
				// $p1 = array_shift ( $this->pgin->arenaPlayerSpawnLocationsSingle );
				// find the maping position
				
				$this->teleportWorld ( $player, $playworld );
				
				// put this back to last
				// $this->pgin->arenaPlayerSpawnLocationsSingle [] = $p1;
				
				// $p1->spawnLocation->y = $p1->spawnLocation->y + 10;
				$player->teleport ( new Vector3 ( $p1->spawnLocation->x, $p1->spawnLocation->y + 10, $p1->spawnLocation->z ) );
				// move player to the shell until game starts
				$builder->buildShell ( $player, 4, $p1->spawnLocation->x, $p1->spawnLocation->y + 10, $p1->spawnLocation->z );
				// //self destroy it move to startup
				$message = "------------------------------";
				$this->pgin->getServer ()->broadcastMessage ( $message );
				$message = $player->getName () . " joined Skywars game at [$p1->spawnLocation]";
				$this->pgin->getServer ()->broadcastMessage ( $message );
				
				$tx = $p1->spawnLocation->x + 2;
				$ty = $p1->spawnLocation->y + 1 + 10;
				$tz = $p1->spawnLocation->z + 2;
				$player->teleport ( new Position ( $tx, $ty, $tz ) );
				// change player gamemode to speculator
				// $player->setGamemode(3);
				
				$this->pgin->skywarsPlayersWithShell [$player->getName ()] = new Position ( $p1->spawnLocation->x, $p1->spawnLocation->y, $p1->spawnLocation->z );
				// save player clicked position
				$this->pgin->singleSkyPlayers [$player->getName ()] = new Position ( $b->x, $b->y, $b->z );
				// players by name
				$this->pgin->skyplayers [$player->getName ()] = $player;
				
				$player->sendMessage ( "Skywars start shortly. Please wait..." );
				// $message = "Skywar play still have " . count ( $this->pgin->arenaPlayerSpawnLocationsSingle ) . " spots available. Join now!";
				// $this->pgin->getServer ()->broadcastMessage ( $message );
				
				$this->pgin->gamemode = 1;
			}
			return;
		}
	}
	public function handleTeamButtonClick($b, $player) {
		$key = $b->x . " " . $b->y . " " . $b->z;
		if (isset ( $this->pgin->teamClickButtons [$key] )) {
			$btn = $this->pgin->teamClickButtons [$key];
			$player->sendMessage ( "clicked team " . $key );
			
			return;
		}
	}
	
	/**
	 * Teleport to new world
	 *
	 * @param CommandSender $sender        	
	 * @param unknown $levelname        	
	 */
	public function teleportWorld(Player $sender, $levelname) {
		// $sender->sendMessage("=SIMPLE WORLDS=");
		if (! $sender instanceof Player) {
			$sender->sendMessage ( "skyblock tp command only WORKS in-game mode! " );
			return;
		}
		if (! $sender->getServer ()->isLevelLoaded ( $levelname )) {
			$ret = $sender->getServer ()->loadLevel ( $levelname );
			if (! $ret) {
				$sender->sendMessage ( "Error, unable load World: " . $levelname . ". please contact server administrator." );
				return;
			}
		}
		$level = $sender->getServer ()->getLevelByName ( $levelname );
		if ($level == null) {
			$sender->sendMessage ( "Error, unable located world: " . $levelname . ". please contact server administrator." );
			return;
		}
		// if (! $sender->getServer ()->isLevelGenerated ( $levelname )) {
		// $sender->sendMessage ( "New world has not generated yet! try later." );
		// return;
		// }
		$sender->sendMessage ( "Teleporting to [" . $levelname . "]" );
		$sender->teleport ( $level->getSafeSpawn () );
		return;
	}
	public function onPlayerMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer ();
		
		$x = abs ( round ( $event->getTo ()->x ) );
		$y = abs ( round ( $event->getTo ()->y ) );
		$z = abs ( round ( $event->getTo ()->z ) );
		
		$xx = round ( $event->getTo ()->x );
		$yy = round ( $event->getTo ()->y );
		$zz = round ( $event->getTo ()->z );
		
		// check portal
		$xyz = $xx . $yy . $zz;
		if (isset ( $this->pgin->portals [$xyz] )) {
			// unset($this->pgin->portals[$xyz]);
			$levelhome = $this->pgin->portals [$xyz];
			// send player
			$player->sendMessage ( $levelhome );
			$level = PortalManager::isLevelLoaded ( $player, $levelhome );
			if ($level != null) {
				$player->teleport ( $level->getSpawnLocation () );
				return;
			}
		}
		
		if ($this->pgin->config ["districtborder"] == "on") {
			$t = new Vector2 ( $x, $z );
			$s = new Vector2 ( $this->pgin->spawn ["x"], $this->pgin->spawn ["z"] );
			$r = $this->pgin->config ["blocks"];
			// $this->log ( $r . "|" . round ( $this->pgin->spawn ["x"] ) . " " . round ( $this->pgin->spawn ["z"] ) . " | " . round ( $event->getTo ()->x ) . " " . round ( $event->getTo ()->y ) . " " . round ( $event->getTo ()->z ) . " | " . abs ( round ( $t->distance ( $s ) ) ) );
			$dff = abs ( $t->distance ( $s ) );
			if ($dff >= $r) {
				$event->setCancelled ( true );
				$player->sendMessage ( "[SkyBlock] " . $this->pgin->config ["message"] );
				$player->teleport ( $player->getLevel ()->getSpawnLocation () );
				return;
			}
		}
		
		$this->checkWinner ( $player );
	}
	public function checkWinner(Player $player) {
		// $this->log ( "gamemode:" . $this->pgin->gamemode . " " . count ( $this->pgin->skyplayers ) );
		if ($this->pgin->gamemode == 2) {
			// check players left
			if (count ( $this->pgin->skyplayers ) == 1) {
				$p = array_shift ( $this->pgin->skyplayers );
				$message = "=======================================";
				$p->sendMessage ( $message );
				$message = "Conglatulations!!! You WON  Skywars game!";
				$p->sendMessage ( $message );
				$message = "=======================================";
				$this->pgin->getServer ()->broadcastMessage ( $message );
				$message = "Conglatulations!!!" . $p->getName () . " - WON Skywars game!";
				$this->pgin->getServer ()->broadcastMessage ( $message );
				$message = $p->getName () . " - WON Skywars game!";
				$this->pgin->getServer ()->broadcastMessage ( $message );
				//
				$this->pgin->gamemode = 0;
				// refill
				$this->pgin->arenaPlayerSpawnLocationsSingle = $this->pgin->arenaPlayerSpawnLocations;
				// clear
				$this->pgin->skywarsPlayersWithShell = [ ];
				$this->pgin->skyplayers = [ ];
				$this->pgin->singleSkyPlayers = [ ];
				
				// /delete playworld
				$cmd = new SkyWarsCommand ( $this->pgin );
				$playworld = $this->pgin->getConfig ()->get ( "skywars_play_world" );
				if ($playworld == null) {
					$playworld = "skywarsbase1_play";
				}
				// COMMENT OUT TO AVOID CHUNK ERROR
				// $cmd->deletePlayWorld ( $playworld );
				// $message = "removed old skywars play world";
				// $this->pgin->getServer ()->broadcastMessage ( $message );
				$this->log ( $message );
				// send player back to lobby
				$cmd = new SkyWarsCommand ( $this->pgin );
				$cmd->gotolobby ( $p );
				// clear
				$p->getInventory ()->clearAll ();
			}
		}
	}
	
	/**
	 * OnBlockBreak
	 *
	 * @param BlockBreakEvent $event        	
	 */
	public function onBlockBreak(BlockBreakEvent $event) {
		$player = $event->getPlayer ();
		$b = $event->getBlock ();
		if ($this->pgin->pos_display_flag == 1) {
			$event->getPlayer ()->sendMessage ( "block BREAKED: [x=" . $b->x . " y=" . $b->y . " z=" . $b->z . "]" );
		}
		// compensate pocketmine issue
		// SkyBlockKit::addBreakBlock($player,$b);
		
		$x = abs ( round ( $event->getBlock ()->getX () ) );
		$y = abs ( round ( $event->getBlock ()->getY () ) );
		$z = abs ( round ( $event->getBlock ()->getZ () ) );
		if ($this->pgin->config ["districtborder"] == "on") {
			$t = new Vector2 ( $x, $z );
			$s = new Vector2 ( $this->pgin->spawn ["x"], $this->pgin->spawn ["z"] );
			$r = $this->pgin->config ["blocks"];
			$dff = abs ( $t->distance ( $s ) );
			if ($dff >= $r) {
				$event->setCancelled ( true );
				$event->getPlayer ()->sendMessage ( "[SkyWars] " . $this->pgin->config ["message"] );
				return;
			}
		}
	}
	
	/**
	 * onBlockPlace
	 *
	 * @param BlockPlaceEvent $event        	
	 */
	public function onBlockPlace(BlockPlaceEvent $event) {
		$x = abs ( round ( $event->getBlock ()->getX () ) );
		$y = abs ( round ( $event->getBlock ()->getY () ) );
		$z = abs ( round ( $event->getBlock ()->getZ () ) );
		
		if ($this->pgin->config ["districtborder"] == "on") {
			$t = new Vector2 ( $x, $z );
			$s = new Vector2 ( $this->pgin->spawn ["x"], $this->pgin->spawn ["z"] );
			$r = $this->pgin->config ["blocks"];
			$dff = abs ( $t->distance ( $s ) );
			if ($dff >= $r) {
				$event->setCancelled ( true );
				$event->getPlayer ()->sendMessage ( "[SkyWars] " . $this->pgin->config ["message"] );
				return;
			}
		}
	}
	
	// /**
	// * OnPlayerJoin
	// *
	// * @param PlayerJoinEvent $event
	// */
	public function onPlayerJoin(PlayerJoinEvent $event) {
		$event->getPlayer ()->addAttachment ( $this->pgin, "mcg76.plugin.skywars", true );
		// provide a sign to player
		// ArenaKit::getJoinServerKit($event->getPlayer());
		$cmd = new SkyWarsCommand ( $this->pgin );
		$cmd->gotolobby ( $event->getPlayer () );
		// clear
		$event->getPlayer ()->getInventory ()->clearAll ();
		
		$sx = $this->pgin->getConfig ()->get ( "skywars_lobby_x" );
		$sy = $this->pgin->getConfig ()->get ( "skywars_lobby_y" );
		$sz = $this->pgin->getConfig ()->get ( "skywars_lobby_z" );
		
		$event->getPlayer ()->setSpawn ( new Vector3 ( $sx, $sy, $sz ) );
		
		$spawnmods = $this->pgin->getConfig ()->get ( "spawnmods" );
		$skwlobby = $this->pgin->getConfig ()->get ( "skywars_lobby_world" );
		
		$this->log ( "spawnmods : " . $spawnmods );
		// skywars_lobby_world
		if ($spawnmods == "yes") {
			$builder = new BlockBuilder ( $this->pgin );			
			if ($event->getPlayer ()->level->getName () == $skwlobby) {
				// spawn mods
				// villger = 15
				$x1 = $this->pgin->getConfig ()->get ( "skywars_shop_sales1_x" );
				$y1 = $this->pgin->getConfig ()->get ( "skywars_shop_sales1_y" );
				$z1 = $this->pgin->getConfig ()->get ( "skywars_shop_sales1_z" );
				$builder->spawnMods ( 15, new Position ( $x1, $y1, $z1 ), $event->getPlayer () );				
				// zombie = 32
				$x2 = $this->pgin->getConfig ()->get ( "skywars_shop_sales2_x" );
				$y2 = $this->pgin->getConfig ()->get ( "skywars_shop_sales2_y" );
				$z2 = $this->pgin->getConfig ()->get ( "skywars_shop_sales2_z" );
				$builder->spawnMods ( 32, new Position ( $x2, $y2, $z2 + 6 ), $event->getPlayer () );				
				// player equipment display
				$builder->spawnShopPlayerWithEquipments ( $event->getPlayer () );
			}
		}
	}
	public function onPlayerRespawn(PlayerRespawnEvent $event) {
		$event->getPlayer ()->addAttachment ( $this->pgin, "mcg76.plugin.skywars", true );
		// Reverse player clicked green button
		$cmd = new SkyWarsCommand ( $this->pgin );
		$cmd->gotolobby ( $event->getPlayer () );
		$cmd->cleanupInGamePlayer ( $event->getPlayer () );
	}
	public function onDeath(PlayerDeathEvent $event) {
		if ($event->getEntity () instanceof Player) {
			// Reverse player clicked green button
			$cmd = new SkyWarsCommand ( $this->pgin );
			$cmd->cleanupInGamePlayer ( $event->getEntity () );
		}
	}
	public function onQuit(PlayerQuitEvent $event) {
		// Reverse player clicked green button
		$cmd = new SkyWarsCommand ( $this->pgin );
		$cmd->cleanupInGamePlayer ( $event->getPlayer () );
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