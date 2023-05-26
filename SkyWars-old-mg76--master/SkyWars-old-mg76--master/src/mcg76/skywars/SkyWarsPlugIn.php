<?php

namespace mcg76\skywars;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\Explosion;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityMoveEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\block\Block;
use pocketmine\block\WallSign;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\level\generator\Generator;
use mcg76\skywars\map\SkyBlockGenerator;
use mcg76\skywars\map\SuperFlat;
use mcg76\skywars\portal\PortalManager;
use mcg76\skywars\portal\Portal;
use mcg76\skywars\SkyWarsConfiguration;
use pocketmine\utils\Utils;
use pocketmine\utils\Binary;
use mcg76\skywars\utils\Timer;

/**
 * MCG76 SkyBlockPlugIn
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class SkyWarsPlugIn extends PluginBase implements CommandExecutor {
	public $pos_display_flag = 0;
	public $skbCommand;
	public $skbConfig;
	public $config;
	public $spawn;
	public $gamemode = 0;
	public $maps = [ ];
	public $skyplayers = [ ];
	public $portals = [ ];
	//in used
	public $arenas = [ ];	
	public $singleClickButtons = [];
	public $teamClickButtons = [];	
	public $arenaPlayerSpawnLocations = [];
	public $arenaPlayerSpawnLocationsSingle = [];
	public $arenaPlayerSpawnLocationsTeam = [];
	public $arenaChestSpawnLocations = [];
	//in-game
	public $singleSkyPlayers = [ ];
	public $teamSkyPlayers = [ ]; 	
	//waiting	
	public $skywarsPlayersWithShell = [ ];
	//mapping
	public $mappingClickButtonsToSpawnLocations = [ ];
	
	//game starting tme
	public $startPlayTime;
	
	public $countDownCounter=0;
	
	/**
	 * OnLoad
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onLoad()
	 */
	public function onLoad() {
		$this->skbCommand = new SkyWarsCommand ( $this );
		$this->skbConfig = new SkyWarsConfiguration ( $this );		
	}
	
	/**
	 * OnEnable
	 *
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onEnable()
	 */
	public function onEnable() {
				
		//custom generator
		Generator::addGenerator ( SkyBlockGenerator::class, "skyblock" );
		
		$this->skbConfig->loadConfiguration ();
		$this->getServer ()->getPluginManager ()->registerEvents ( new SkyWarsListener ( $this ), $this );
						
		$this->enabled = true;
		$this->log ( TextFormat::GREEN . "- mcg76_SkyWars_Minigame - Enabled!" );
		
// 		//preload worlds
		$skywarsbaseworld = $this->getConfig()->get("skywars_base_world");
		Server::getInstance()->loadLevel($skywarsbaseworld);
		
		$skywarslobbyworld = $this->getConfig()->get("skywars_lobby_world");
		Server::getInstance()->loadLevel($skywarslobbyworld);
		
		//self destroy it
		$task = new CountDownTimer($this, $this->startPlayTime);
		//run every minute
		$period = $this->getServer()->getTicksPerSecond() * 15;		
		$this->log ( TextFormat::GREEN . "-ticks per second ".$this->getServer()->getTicksPerSecond());		
		$this->getServer()->getScheduler()->scheduleRepeatingTask($task, $period);
		
	}
	
	/**
	 * OnDisable
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onDisable()
	 */
	public function onDisable() {
		$this->log ( TextFormat::RED . "mcg76_SkyWars - Disabled" );
		$this->enabled = false;
	}
	
	/**
	 * OnCommand
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onCommand()
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$this->skbCommand->onCommand ( $sender, $command, $label, $args );
	}
	
	/**
	 * Logging util function
	 *
	 * @param unknown $msg        	
	 */
	private function log($msg) {
		$this->getLogger ()->info ( $msg );
	}
}
