<?php

namespace mcg76\skywars;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\format\anvil\Anvil;
use pocketmine\level\generator\GenerationChunkManager;
use pocketmine\level\format\LevelProviderManager;
use pocketmine\level\format\mcregion\McRegion;
use pocketmine\level\generator\Flat;
use pocketmine\level\generator\GenerationInstanceManager;
use pocketmine\level\generator\GenerationRequestManager;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\Normal;
use pocketmine\level\Level;
use pocketmine\level\Explosion;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityMoveEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\block\Block;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\entity\FallingBlock;
use pocketmine\command\defaults\TeleportCommand;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\HandlerList;
use pocketmine\event\level\LevelInitEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\utils\Binary;
use pocketmine\utils\TextWrapper;
use pocketmine\utils\Utils;
use mcg76\skywars\map\SkyBlockGenerator;
use mcg76\skywars\map\SuperFlat;
use mcg76\skywars\portal\PortalManager;
use mcg76\skywars\portal\Portal;
use mcg76\skywars\utils\FileUtil;

/**
 * MCG76 SkyWarsCommand
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class SkyWarsCommand {
	private $pgin;
	/**
	 *
	 * @param SkyBlock $pg        	
	 */
	public function __construct(SkyWarsPlugIn $pg) {
		$this->pgin = $pg;
		Generator::addGenerator ( SkyBlockGenerator::class, "skyblock" );
	}
	
	/**
	 * onCommand
	 *
	 * @param CommandSender $sender        	
	 * @param Command $command        	
	 * @param unknown $label        	
	 * @param array $args        	
	 * @return boolean
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		// $this->log( TextFormat::RED . "- onCommand :".$command->getName());
		// WARNING: WHEN USING LOADWORLD MAKE SURE YOU INPUT THE CORRECT ENVIRONMENT. IF YOU DON'T IT WILL OVERWRITE YOUR OLD WORLD
		// Disclaimer : While this plugin is very safe, it is possible to accidentally delete your worlds. Backups are recommended
		// check command names
		if ((strtolower ( $command->getName () ) == "skw" || strtolower ( $command->getName () ) == "skywars") && isset ( $args [0] )) {
			// $this->log ( $command->getName () . " " . count ( $args ) . " " . $args [0] );
			
			if (strtolower ( $args [0] ) == "help") {
				$sender->sendMessage ( TextFormat::BLUE . "skywars | skw [Commands]" );
				$sender->sendMessage ( TextFormat::BLUE . "-----------------------" );
				$sender->sendMessage ( TextFormat::BLUE . "/skw lobby -go to lobby" );
				$sender->sendMessage ( TextFormat::BLUE . "/skw blockon - turn dipslay block on" );
				$sender->sendMessage ( TextFormat::BLUE . "/skw blockff - turn display block off" );				
				$sender->sendMessage ( TextFormat::BLUE . "/skw tp [world name] - teleport to another world" );
				$sender->sendMessage ( TextFormat::BLUE . "/skw display help" );
				$sender->sendMessage ( TextFormat::BLUE . "----------------------" );
				return true;
			}
			
			if (strtolower ( $args [0] ) == "lobby") {
				$this->gotolobby ( $sender );
				return true;
			}
			
			if (strtolower ( $args [0] ) == "blockon") {
				$this->pgin->pos_display_flag = 1;
				$sender->sendMessage ( "block location display ON ");
				return true;
			}
			if (strtolower ( $args [0] ) == "blockoff") {
				$this->pgin->pos_display_flag = 0;
				$sender->sendMessage ( "block location display OFF ");
				return true;
			}
			
			if (strtolower ( $args [0] ) == "setlobby") {
				if ($sender instanceof Player) {
					$this->pgin->getConfig ()->set ( "skywars_lobby_world", $sender->level->getName () );
					$this->pgin->getConfig ()->set ( "skywars_lobby_x", $sender->getPosition ()->x );
					$this->pgin->getConfig ()->set ( "skywars_lobby_y", $sender->getPosition ()->y );
					$this->pgin->getConfig ()->set ( "skywars_lobby_z", $sender->getPosition ()->z );
					$sender->sendMessage ( "lobby set to " . $sender->level->getName () );
				}
				$this->gotolobby ( $sender );
				return true;
			}
						
			if (strtolower ( $args [0] ) == "tp" || strtolower ( $args [0] ) == "teleport") {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( TextFormat::BLUE . "You are not authorized to use this command. please contact server administrator [op]." );
					return;
				}
				if (! isset ( $args [1] )) {
					$sender->sendMessage ( TextFormat::BLUE . "Usage: skyblock [tp] [play world name]" );
					return;
				}
				$worldname = $args [1];
				$sender->sendMessage ( TextFormat::BLUE . "skyblock [tp] " . $worldname );
				$this->teleportWorld ( $sender, $worldname );
				return true;
			}

			$sender->sendMessage ( TextFormat::BLUE . "skywars mini-game version 1.00" );
			$sender->sendMessage ( TextFormat::BLUE . "-----------------------" );
			$sender->sendMessage ( TextFormat::BLUE . "created by" );
			$sender->sendMessage ( TextFormat::BLUE . "minecraftgenius76" );
			$sender->sendMessage ( TextFormat::BLUE . "----------------------" );			
		}
	}
	public function sharePlayworld($sender, $worldname, $enabled) {
		$map = null;
		$player = $sender->getPlayer ();
		if (! isset ( $this->pgin->maps [$worldname] )) {
			$sender->sendMessage ( TextFormat::BLUE . "playworld [" . $worldname . "] doesn't exist!" );
			return;
		}
		$map = $this->pgin->maps [$worldname];
		// @todo only owner can make change
		$sender->sendMessage ( TextFormat::BLUE . "owner name: " . $map->ownerName . " sender:" . $sender->getName () );
		if ($map->ownerName != $sender->getName ()) {
			$sender->sendMessage ( TextFormat::BLUE . "Unauthorized action, only play owner allow change sharing option." );
			return;
		}
		$map->shared = $enabled;
		$map->delete ();
		$map->save ();
		
		$sender->sendMessage ( TextFormat::BLUE . "playworld [" . $worldname . "] done!" );
	}
	
	/**
	 * Get World Spawn Location
	 *
	 * @param CommandSender $sender        	
	 * @param unknown $levelname        	
	 */
	public function getWorldSpawnLocation(CommandSender $sender, $levelname) {
		if ($levelname == null) {
			$sender->sendMessage ( "Warning, no world name specified!" );
			return;
		}
		
		if (! $sender->getServer ()->isLevelLoaded ( $levelname )) {
			$ret = $sender->getServer ()->loadLevel ( $levelname );
			if (! $ret) {
				$sender->sendMessage ( "Error, unable load World: " . $levelname . ". please contact server administrator." );
				return;
			}
		}
		if (! $sender->getServer ()->isLevelGenerated ( $levelname )) {
			$sender->sendMessage ( $levelname . " - world generation still running, not ready yet! try later." );
			return;
		}
		
		$level = $sender->getServer ()->getLevelByName ( $levelname );
		if ($level == null) {
			$sender->sendMessage ( "Error, unable access world: " . $levelname . ". please contact server administrator." );
			return;
		}
		// position
		$px = $level->getSpawnLocation ()->getX ();
		$py = $level->getSpawnLocation ()->getY ();
		$pz = $level->getSpawnLocation ()->getZ ();
		$sender->sendMessage ( "[" . $levelname . "] Spawn Location is at [" . round ( $px ) . " " . round ( $py ) . " " . round ( $pz ) . "]" );
	}
	
	/**
	 * Set World Spawn
	 *
	 * @param CommandSender $sender        	
	 * @param unknown $levelname        	
	 */
	public function setWorldSpawnLocation(CommandSender $sender, $levelname) {
		if ($levelname == null) {
			$sender->sendMessage ( "Warning, no world name specified!" );
			return;
		}
		// position
		$px = null;
		$py = null;
		$pz = null;
		
		if (! $sender->getServer ()->isLevelLoaded ( $levelname )) {
			$ret = $sender->getServer ()->loadLevel ( $levelname );
			if (! $ret) {
				$sender->sendMessage ( "Error, unable load World: " . $levelname . ". please contact server administrator." );
				return;
			}
		}
		if (! $sender->getServer ()->isLevelGenerated ( $levelname )) {
			$sender->sendMessage ( $levelname . " - world generation still running, not ready yet! try later." );
			return;
		}
		
		$level = $sender->getServer ()->getLevelByName ( $levelname );
		if ($level == null) {
			$sender->sendMessage ( "Error, unable access world: " . $levelname . ". please contact server administrator." );
			return;
		}
		
		// if run in-game, grab players location
		if ($sender instanceof Player) {
			$player = $sender->getPlayer ();
			$level->setSpawnLocation ( new Position ( $player->x, $player->y, $player->z ) );
			$sender->sendMessage ( "Set World " . $levelname . " spawn location to current: [" . round ( $player->x ) . " " . round ( $player->y ) . " " . round ( $player->z ) . "]" );
			return;
		}
		
		if (count ( $args ) == 5) {
			// $levelname = $args [1];
			$px = $args [2];
			$py = $args [3];
			$pz = $args [4];
			$this->loadWorld ( $sender, $levelname );
		} else {
			$sender->sendMessage ( "Console Usage: /spw setspawn [world name] [x] [y] [z]" );
			return;
		}
		
		// run in console mode
		if ($px == null || $py == null || $pz == null) {
			$sender->sendMessage ( "Missing x,y,z values. Console Usage: /spw setspawn [world name] [x] [y] [z]" );
			return;
		}
		$level->setSpawnLocation ( new Position ( $px, $py, $pz ) );
		$sender->sendMessage ( "Set World " . $levelname . " Spawn Location set [" . $px . " " . $py . " " . $pz . "]" );
	}
	
	/**
	 * Load World
	 *
	 * @param CommandSender $sender        	
	 * @param unknown $levelname        	
	 */
	public function loadWorld(CommandSender $sender, $levelname) {
		if ($levelname == null) {
			$sender->sendMessage ( "Warning, no world name specified!" );
			return;
		}
		// $sender->sendMessage("=SIMPLE WORLDS=");
		$sender->sendMessage ( "Load World: " . $levelname );
		if (! $sender->getServer ()->isLevelLoaded ( $levelname )) {
			$ret = $sender->getServer ()->loadLevel ( $levelname );
			if ($ret) {
				$sender->sendMessage ( "world loaded! " );
			} else {
				$sender->sendMessage ( "Error, unable load World: " . $levelname . " contact server administrator." );
			}
		}
		$this->listWorld ( $sender );
	}
	// public function unloadWorld(CommandSender $sender, $levelname) {
	// if ($levelname == null) {
	// $sender->sendMessage ( "Warning, no world name specified!" );
	// return;
	// }
	// // $sender->sendMessage("=SIMPLE WORLDS=");
	// $sender->sendMessage ( "unLoad World: " . $levelname );
	// if ($sender->getServer ()->isLevelLoaded ( $levelname )) {
	// $level = $sender->getServer ()->getLevelByName ( $levelname );
	// if ($level == null) {
	// $sender->sendMessage ( "Error, unable access world: " . $levelname . ". please contact server administrator." );
	// return;
	// }
	// $ret = $sender->getServer ()->unloadLevel ( $level );
	// if ($ret) {
	// $sender->sendMessage ( "world unloaded! " );
	// } else {
	// $sender->sendMessage ( "Error, unable unload World: " . $levelname . ". please contact server administrator." );
	// }
	// }
	// $this->listWorld ( $sender );
	// }
	public function deletePlayWorld($worldname) {
		// check if level exist
		$level = Server::getInstance ()->getLevelByName ( $worldname );
		if ($level == null) {
			$this->log ( TextFormat::BLUE . "Error: Skywars World [" . $worldname . "] doesn't exist!" );
			return;
		}
		// unload level
		Server::getInstance ()->unloadLevel ( $level, true );
		// delete folder
		$levelpath = Server::getInstance ()->getDataPath () . "worlds/" . $worldname . "/";
		// @unlink($levelpath);
		// rmdir($levelpath);
		$fileutil = new FileUtil ();
		$fileutil->unlinkRecursive ( $levelpath, true );
		$this->log ( TextFormat::BLUE . "Skywars play world [" . $worldname . "] deleted!" );
	}
	public function joinWorld(CommandSender $sender, $worldname) {
		// load level
		$sender->getServer ()->loadLevel ( $worldname );
		$level = $sender->getServer ()->getLevelByName ( $worldname );
		if ($level == null) {
			$sender->sendMessage ( TextFormat::BLUE . "Error: skyplay world [" . $worldname . "] doesn't exist!" );
			return;
		}
		$map = null;
		if (isset ( $this->pgin->maps [$worldname] )) {
			$map = $this->pgin->maps [$worldname];
		} else {
			$map = new SkyBlockMap ( $this->pgin, $worldname );
			if ($level != null) {
				$map->spawnLocation = $level->getSpawnLocation ();
			}
			$map->save ();
			$this->pgin->maps [$worldname] = $map;
		}
		
		// @TODO check here to see if this is a private world
		$map = $this->pgin->maps [$worldname];
		if (! $map->shared) {
			// check if assign to owner
			if ($sender->getName () != $map->ownerName) {
				$sender->sendMessage ( "Oops! [" . $worldname . "] is private, only owner allow to join!" );
				return;
			}
		}
		// @TODO - provide basics inventory to player
		if ($sender instanceof Player) {
			SkyBlockKit::addGameKit ( "skyblock", $sender );
		}
		// @TODO - teleporting to world
		$sender->sendMessage ( "teleporting player to skyblock world [" . $worldname . "]" );
		$this->teleportWorld ( $sender, $worldname );
		
		// @TODO - teleporting to player home location if exist
		if (isset ( $this->skyplayers [$sender->getName ()] )) {
			$py = $this->skyplayers [$sender->getName ()];
			if ($sender instanceof Player) {
				$sender->teleport ( new Vector3 ( $py->spawnLocation->x, $py->spawnLocation->y, $py->spawnLocation->z ) );
			}
		} else {
			// create new profile
			$this->savePlayerRecord ( $sender, $worldname );
		}
		$sender->sendMessage ( "done!" );
	}
	public function savePlayerRecord(Player $player, $worldname) {
		$map = new SkyBlockPlayer ( $this->pgin, $player->getName () );
		$map->spawnLocation = $player->getPosition ();
		$map->chestLocation = new Position ( $player->x + 1, $player->y, $player->z );
		$map->levelname = $worldname;
		$map->save ();
		$this->pgin->skyplayers [$player->getName ()] = $map;
	}
	public function gotolobby(CommandSender $sender) {
		// $sender->sendMessage ( TextFormat::BLUE . "skb [lobby]" );
		$sx = $this->pgin->getConfig ()->get ( "skywars_lobby_x" );
		$sy = $this->pgin->getConfig ()->get ( "skywars_lobby_y" );
		$sz = $this->pgin->getConfig ()->get ( "skywars_lobby_z" );
		$levelname = $this->pgin->getConfig ()->get ( "skywars_lobby_world" );
		
		if ($sx == null || $sy == null || $sz == null) {
			$sender->sendMessage ( TextFormat::BLUE . "missing skyblock lobby info. please check configuation file!" );
			return;
		}
		if (! $sender instanceof Player) {
			$sender->sendMessage ( TextFormat::BLUE . "this command is for in-game use only!" );
			return;
		}
		// load world
		$this->teleportWorld ( $sender, $levelname );
		
		//$sender->sendMessage ( TextFormat::BLUE . "return to Lobby at [".$sx." " .$sy." ".$sz."]");
		$sender->getPlayer ()->teleport ( new Vector3 ( $sx, $sy, $sz ) );
		
		// Reverse player clicked green button
		$this->cleanupInGamePlayer ( $sender->getPlayer () );
		
		// change player gamemode to speculator
		// $sender->getPlayer()->setGamemode(3);
	}
	public function addPlayWorld($baseworld, $worldname) {
		$status = false;
		// make a copy of the skyblock template
		$fileutil = new FileUtil ();
		$base = $baseworld;
		if ($baseworld==null) {
		 $base = $this->pgin->getConfig ()->get ( "skywars_base_world" );
		} 
		if ($base == null) {
			$base = "skywarsbase1";
		}
		$source = $this->pgin->getServer ()->getDataPath () . "worlds/" . $base . "/";
		$dest = $this->pgin->getServer ()->getDataPath () . "worlds/" . $worldname . "/";
		
		if ($fileutil->xcopy ( $source, $dest )) {
			$this->log ( "New Skywars play [" . $worldname . "] created!" );
			try {
				Server::getInstance ()->loadLevel ( $worldname );
				$level = Server::getInstance ()->getLevelByName ( $worldname );
				$status = true;
			} catch ( \Exception $e ) {
				$this->log ( "level loading error: " . $e->getMessage () );
			}
		} else {
			$this->log ( "problem creating skywars play world. please contact administrator." );
		}
		return $status;
	}
	public function resetWorld(CommandSender $sender, $levelname) {
		// if ($levelname == null) {
		// $sender->sendMessage ( "Warning, no world name specified!" );
		// return;
		// }
		// restrick one world per player except ops
		if (! $sender->isOp ()) {
			// @TODO check here to see if this is a private world
			$map = $this->pgin->maps [$levelname];
			// check if assign to owner
			if ($sender->getName () != $map->ownerName) {
				$sender->sendMessage ( "Oops! [" . $worldname . "] is private, only owner or admin allow to reset!" );
				return;
			}
		}
		
		if ($sender instanceof Player) {
			if ($levelname == $sender->getLevel ()->getName ()) {
				$sender->sendMessage ( "Warning, You can not delete world your currently on!" );
				return;
			}
		}
		
		$sender->sendMessage ( "Reset World: " . $levelname );
		if ($sender->getServer ()->isLevelLoaded ( $levelname )) {
			$level = $sender->getServer ()->getLevelByName ( $levelname );
			$ret = $sender->getServer ()->unloadLevel ( $level );
			if ($ret) {
				$sender->sendMessage ( "unloaded! " );
			} else {
				$sender->sendMessage ( "Error, unable unload World: " . $levelname . ". please contact server administrator." );
			}
		}
		// delete folder
		$levelpath = $sender->getServer ()->getDataPath () . "worlds/" . $levelname . "/";
		// @unlink($levelpath);
		// rmdir($levelpath);
		$fileutil = new FileUtil ();
		$fileutil->unlinkRecursive ( $levelpath, true );
		
		$base = $this->pgin->getConfig ()->get ( "skyblock_base_world" );
		if ($base == null) {
			$base = "skyblockbase";
		}
		$source = $sender->getServer ()->getDataPath () . "worlds/" . $base . "/";
		$dest = $levelpath;
		
		if ($fileutil->xcopy ( $source, $dest )) {
			$sender->sendMessage ( "World reset done!" );
			return;
		}
		$sender->sendMessage ( "problem resetting skyblock world. please contact administrator." );
	}
	
	/**
	 * List all worlds in server memory
	 *
	 * @param CommandSender $sender        	
	 */
	public function listMaps(CommandSender $sender) {
		// $levels = $sender->getServer ()->getLevels ();
		// $sender->sendMessage("=SIMPLE WORLDS=");
		$this->pgin->skbConfig->loadMapFiles ();
		
		$sender->sendMessage ( "Playworlds: " );
		$i = 1;
		foreach ( $this->pgin->maps as $map ) {
			$sender->sendMessage ( "  " . $i . "> " . $map->name );
			$i ++;
		}
	}
	
	/**
	 * List All Worlds in server folder
	 *
	 * @param CommandSender $sender        	
	 */
	public function listAllWorld(CommandSender $sender) {
		$out = "The following levels are available:";
		$i = 0;
		if ($handle = opendir ( $levelpath = $sender->getServer ()->getDataPath () . "worlds/" )) {
			while ( false !== ($entry = readdir ( $handle )) ) {
				if ($entry [0] != ".") {
					$i ++;
					$out .= "\n " . $i . ">" . $entry . " ";
				}
			}
			closedir ( $handle );
		}
		// $sender->sendMessage("List: ");
		$sender->sendMessage ( $out );
	}
	public function cleanupInGamePlayer(Player $player) {
		// Reverse player clicked green button
		if (isset ( $this->pgin->singleSkyPlayers [$player->getName ()] )) {
			// $b = $this->pgin->singleSkyPlayers[$player->getName()] ;
			// $builder = new BlockBuilder($this->pgin);
			// $block = $player->level->getBlock(new Position($b->x,$b->y,$b->z));
			// 51 fire, 133 emeral block
			// $builder->updateBlock($block, $player->level, 133);
			unset ( $this->pgin->singleSkyPlayers [$player->getName ()] );
			unset ( $this->pgin->skyplayers [$player->getName ()] );
			unset ( $this->pgin->skywarsPlayersWithShell [$player->getName ()] );
		}
	}
	
	/*
	 * Teleport to new world @param CommandSender $sender @param unknown $levelname
	 */
	public function teleportWorld(CommandSender $sender, $levelname) {
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
	private function hasCommandAccess(CommandSender $sender) {
		if ($sender->getName () == "CONSOLE") {
			return true;
		} elseif ($sender->isOp ()) {
			return true;
		}
		return false;
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