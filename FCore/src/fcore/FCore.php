<?php

declare(strict_types = 1);

namespace fcore;

use fcore\command\BanmanagerCommand;
use fcore\command\LanguageCommand;
use fcore\command\LobbyCommand;
use fcore\command\ProfileCommand;
use fcore\command\SetrankCommand;
use fcore\command\VipCommand;
use fcore\event\ListenerManager;
use fcore\float\FloatingTextManager;
use fcore\form\FormAPI;
use fcore\lobbyutils\LobbyUtilsManager;
use fcore\profile\ProfileManager;
use fcore\pvp\KitManager;
use fcore\pvp\PvPManager;
use fcore\shop\ShopManager;
use fcore\slots\SlotsUpdate;
use fcore\task\ScheduleManager;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

/**
 * Class FCore
 * @package fcore
 * @author VixikCZ
 * @version 1.0
 * @api ALL
 */
class FCore extends PluginBase implements Settings {

	/** @var array $commands */
	public $commands = [];

	/** @var int $barEid */
	public $barEid = null;

	/** @var \fcore\form\FormAPI $formApi */
	public $formApi;

	/** @var LobbyUtilsManager $lobbyUtilsMgr */
	public $lobbyUtilsMgr;

	/** @var KitManager $kitMgr */
	public $kitMgr;

	/** @var ProfileManager $profileMgr */
	public $profileMgr;

	/** @var ScheduleManager */
	public $scheduleMgr;

	/** @var ShopManager $shopMgr */
	public $shopMgr;

	/** @var ListenerManager $listenerMgr */
	public $listenerMgr;

	/** @var FloatingTextManager $floatingTextMgr */
	public $floatingTextMgr;

	/** @var PvPManager $pvpMgr */
	public $pvpMgr;

	/** @var SlotsUpdate $slotsMgr */
	public $slotsMgr;

	/** @var FCore $instance */
	public static $instance;

	public function onEnable(){
		self::$instance = $this;
		$this->registerManagers();
		$this->registerCommands();

		self::loadFirst(self::DEFAULT_LEVEL_NAME);
		self::loadFirst(self::PVP_LEVEL_NAME);
		self::loadFirst(self::PARKOUR_LEVEL);
		$this->getServer()->getNetwork()->setName(self::SERVER_NAME);
	}

	public static function loadFirst(string $levelName, bool $load = true){
		Server::getInstance()->generateLevel($levelName);
		if($load){
			Server::getInstance()->loadLevel($levelName);
		}
	}

	public function onDisable(){
		$this->profileMgr->save();
	}

	public function registerManagers(){
		$this->formApi = new FormAPI($this);
		$this->lobbyUtilsMgr = new LobbyUtilsManager($this);
		$this->kitMgr = new KitManager($this);
		$this->profileMgr = new ProfileManager($this);
		$this->scheduleMgr = new ScheduleManager($this);
		$this->shopMgr = new ShopManager($this);
		$this->listenerMgr = new ListenerManager($this);
		$this->floatingTextMgr = new FloatingTextManager($this);
		$this->pvpMgr = new PvPManager($this);
		$this->slotsMgr = new SlotsUpdate($this);
	}

	public function registerCommands(){
		$this->commands["lobbyCommand"] = new LobbyCommand($this);
		$this->commands["profileCommand"] = new ProfileCommand($this);
		$this->commands["vipCommand"] = new VipCommand($this);
		$this->commands["setRankCommand"] = new SetrankCommand($this);
		$this->commands["banManagerCommand"] = new BanmanagerCommand($this);
		$this->commands["languageCommand"] = new LanguageCommand();
		foreach($this->commands as $alias => $command){
			$this->getServer()->getCommandMap()->register("fcore", $command);
		}
	}

	/**
	 * @return string
	 */
	public static function getPrefix(): string{
		return self::PREFIX;
	}

	/**
	 * @param $msg
	 */
	public static function dbg($msg){
		if(!is_string($msg)){
			$msg = strval($msg);
		}
		self::$instance->getLogger()->critical("§aFCORE DEBUG :: §9{$msg}");
	}
}