<?php

namespace PrestigeSociety\Core;

use _64FF00\PurePerms\PurePerms;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use PrestigeSociety\AntiCheat\CheckFlyingTask;
use PrestigeSociety\AntiCheat\PrestigeSocietyAntiCheat;
use PrestigeSociety\Chat\Broadcaster\BroadcasterTask;
use PrestigeSociety\Chat\PrestigeSocietyChat;
use PrestigeSociety\Core\Commands\BanCommand;
use PrestigeSociety\Core\Commands\ClearInventoryCommand;
use PrestigeSociety\Core\Commands\EatCommand;
use PrestigeSociety\Core\Commands\FlyCommand;
use PrestigeSociety\Core\Commands\GodCommand;
use PrestigeSociety\Core\Commands\HUDCommand;
use PrestigeSociety\Core\Commands\LSDCommand;
use PrestigeSociety\Core\Commands\OpHelpCommand;
use PrestigeSociety\Core\Commands\RepairCommand;
use PrestigeSociety\Core\Commands\RPPCommand;
use PrestigeSociety\Core\Commands\SBanCommand;
use PrestigeSociety\Core\Commands\Server\AddInfoCommand;
use PrestigeSociety\Core\Commands\WorldCommand;
use PrestigeSociety\Core\Task\HUDUpdateTask;
use PrestigeSociety\Core\Utils\ConsoleUtils;
use PrestigeSociety\Core\Utils\CoreInfo;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Credits\Commands\AddCreditsCommand;
use PrestigeSociety\Credits\Commands\CreditsCommand;
use PrestigeSociety\Credits\Commands\SetCreditsCommand;
use PrestigeSociety\Credits\Commands\SubtractCreditsCommand;
use PrestigeSociety\Credits\PrestigeSocietyCredits;
use PrestigeSociety\Economy\Commands\AddCoinsCommand;
use PrestigeSociety\Economy\Commands\BalanceCommand;
use PrestigeSociety\Economy\Commands\PayCommand;
use PrestigeSociety\Economy\Commands\SetCoinsCommand;
use PrestigeSociety\Economy\Commands\SubtractCoinsCommand;
use PrestigeSociety\Economy\PrestigeSocietyEconomy;
use PrestigeSociety\Enchantments\PrestigeSocietyEnchantments;
use PrestigeSociety\Enchants\EnchantCommand;
use PrestigeSociety\Enchants\EnchantListener;
use PrestigeSociety\Enchants\PrestigeSocietyEnchants;
use PrestigeSociety\Experience\PrestigeSocietyExperience;
use PrestigeSociety\Experience\PrestigeSocietyNeeded;
use PrestigeSociety\Factions\PrestigeSocietyFactions;
use PrestigeSociety\Factions\TpWorldCommand;
use PrestigeSociety\Jobs\Commands\JobCommand;
use PrestigeSociety\Jobs\Commands\SpawnCommand;
use PrestigeSociety\Jobs\JobsListener;
use PrestigeSociety\Jobs\PrestigeSocietyJobs;
use PrestigeSociety\Lang\Commands\LangCommand;
use PrestigeSociety\Lang\PrestigeSocietyLang;
use PrestigeSociety\Levels\Commands\SetDeathsCommand;
use PrestigeSociety\Levels\Commands\SetKillsCommand;
use PrestigeSociety\Levels\Commands\SetLevelCommand;
use PrestigeSociety\Levels\Commands\StatsCommand;
use PrestigeSociety\Levels\LevelsListener;
use PrestigeSociety\Levels\PrestigeSocietyLevels;
use PrestigeSociety\Nicknames\NickCommand;
use PrestigeSociety\Nicknames\PrestigeSocietyNicknames;
use PrestigeSociety\Optimizer\Commands\LagCommand;
use PrestigeSociety\Optimizer\OptimizeTask;
use PrestigeSociety\Ranks\Commands\SeeRankCommand;
use PrestigeSociety\Ranks\Commands\SetRankCommand;
use PrestigeSociety\Ranks\PrestigeSocietyRanks;
use PrestigeSociety\Restarter\PrestigeSocietyRestarter;
use PrestigeSociety\Restarter\Task\BroadcastTask;
use PrestigeSociety\Restarter\Task\RestartTask;
use PrestigeSociety\Shop\Commands\AddShopCommand;
use PrestigeSociety\Shop\Commands\RemoveShopCommand;
use PrestigeSociety\Shop\Commands\ShopCommand;
use PrestigeSociety\Shop\PrestigeSocietyShop;
use PrestigeSociety\Shop\ShopListener;
use PrestigeSociety\Signs\PrestigeSocietySigns;
use PrestigeSociety\Signs\SignsListener;
use PrestigeSociety\StaffMode\PrestigeSocietyStaffMode;
use PrestigeSociety\StaffMode\StaffModeCommand;
use PrestigeSociety\StaffMode\StaffModeListener;
use PrestigeSociety\Teleport\PrestigeSocietyCountHomes;
use PrestigeSociety\Teleport\PrestigeSocietyTeleport;
use PrestigeSociety\TradeUI\PrestigeSocietyTradeUI;
use SQLite3;

class PrestigeSocietyCore extends PluginBase {

	private static $instance = null;
	/** @var Player[] */
	public $colorfulConfirm = [];
	/** @var SQLite3 */
	public $db;
	/** @var PrestigeSocietyAntiCheat */
	public $PrestigeSocietyAntiCheat;
	/** @var PrestigeSocietyChat */
	public $PrestigeSocietyChat;
	/** @var PrestigeSocietyRestarter */
	public $PrestigeSocietyRestarter;
	/** @var PrestigeSocietySigns */
	public $PrestigeSocietySigns;
	/** @var PrestigeSocietyLevels */
	public $PrestigeSocietyLevels;
	/** @var PrestigeSocietyStaffMode */
	public $PrestigeSocietyStaffMode;
	/** @var PrestigeSocietyEconomy */
	public $PrestigeSocietyEconomy;
	/** @var PrestigeSocietyCredits */
	public $PrestigeSocietyCredits;
	/** @var PrestigeSocietyFactions */
	public $PrestigeSocietyFaction;
	/** @var PrestigeSocietyNeeded */
	public $PrestigeSocietyNeeded;
	/** @var PrestigeSocietyExperience */
	public $PrestigeSocietyExperience;
	/** @var PrestigeSocietyEnchantments */
	public $PrestigeSocietyEnchantments;
	/** @var PrestigeSocietyTradeUI */
	public $PrestigeSocietyTradeUI;
	/** @var PrestigeSocietyTeleport */
	public $PrestigeSocietyTeleport;
	/** @var PrestigeSocietyJobs */
	public $PrestigeSocietyJobs;
	/** @var InfoParticles */
	public $PrestigeSocietyParticle;
	/** @var PrestigeSocietyLang */
	public $PrestigeSocietyLang;
	/** @var PrestigeSocietyShop */
	public $PrestigeSocietyShop;
	/** @var FunBox */
	public $FunBox;
	/** @var PrestigeSocietyRanks */
	public $PrestigeSocietyRanks;
	/** @var PrestigeSocietyNicknames */
	public $PrestigeSocietyNicks;
	/** @var PrestigeSocietyCountHomes */
	public $PrestigeSocietyCountHomes;
	/** @var PrestigeSocietyEnchants */
	public $PrestigeSocietyEnchants;
	/** @var HUD */
	public $HUD;
	/** @var Player[] */
	protected $inLobby = [];
	/** @var CoreInfo */
	private $coreInfo;
	private $coreInfoArray = [];
	/** @var Config */
	private $messages;

	/**
	 *
	 * @return PrestigeSocietyCore
	 *
	 */
	public static function getInstance(): PrestigeSocietyCore{
		return self::$instance;
	}

	public function onLoad(){
		while(!self::$instance instanceof $this){
			self::$instance = $this;
		}
	}

	public function onEnable(){

		$this->coreInfo = new CoreInfo();
		$this->loadCoreInfoArray();
		$this->logInitialPluginInfo();
		$this->messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML, RandomUtils::getCoreMessagesArray());
		ConsoleUtils::logWithOpts("%%aChecking latest version...");

		$this->saveDefaultConfig();

		if(!file_exists($this->databasesFolder())){
			mkdir($this->databasesFolder());
		}

		$this->PrestigeSocietyAntiCheat = new PrestigeSocietyAntiCheat();
		$this->PrestigeSocietyChat = new PrestigeSocietyChat($this);
		$this->PrestigeSocietySigns = new PrestigeSocietySigns();
		$this->PrestigeSocietyLevels = new PrestigeSocietyLevels($this, new \SQLite3($this->databasesFolder() . "levels.db"));
		$this->PrestigeSocietyStaffMode = new PrestigeSocietyStaffMode($this);
		$this->PrestigeSocietyEconomy = new PrestigeSocietyEconomy(new SQLite3($this->databasesFolder() . "economy.db"));
		$this->PrestigeSocietyCredits = new PrestigeSocietyCredits(new SQLite3($this->databasesFolder() . "credits.db"));
		$this->PrestigeSocietyFaction = new PrestigeSocietyFactions(new SQLite3($this->databasesFolder() . "factions.db"));
		$this->PrestigeSocietyExperience = new PrestigeSocietyExperience(new SQLite3($this->databasesFolder() . "experience.db"));
		$this->PrestigeSocietyNeeded = new PrestigeSocietyNeeded(new SQLite3($this->databasesFolder() . "needed.db"), $this);
		$this->PrestigeSocietyEnchantments = new PrestigeSocietyEnchantments(new SQLite3($this->databasesFolder() . "enchantments.db"));
		$this->PrestigeSocietyTradeUI = new PrestigeSocietyTradeUI($this);
		$this->PrestigeSocietyTeleport = new PrestigeSocietyTeleport($this);
		$this->PrestigeSocietyRanks = new PrestigeSocietyRanks(new SQLite3($this->databasesFolder() . "ranks.db"), $this, yaml_parse_file($this->getDataFolder() . "ranks.yml"));
		$this->PrestigeSocietyNicks = new PrestigeSocietyNicknames(new \SQLite3($this->databasesFolder() . "nicknames.db"));
		$this->PrestigeSocietyJobs = new PrestigeSocietyJobs(new \SQLite3($this->databasesFolder() . "jobs.db"), $this);
		$this->PrestigeSocietyEnchants = new PrestigeSocietyEnchants($this);
		$this->PrestigeSocietyParticle = new InfoParticles($this);
		$this->PrestigeSocietyCountHomes = new PrestigeSocietyCountHomes(new SQLite3($this->databasesFolder() . "counthomes.db"));
		$this->PrestigeSocietyLang = new PrestigeSocietyLang(new SQLite3($this->databasesFolder() . "lang.db"));
		$this->PrestigeSocietyTradeUI->init();
		$this->PrestigeSocietyTeleport->init();
		$this->PrestigeSocietyShop = new PrestigeSocietyShop(new SQLite3($this->databasesFolder() . "shops.db"), $this, $this->PrestigeSocietyTeleport);

		$this->FunBox = new FunBox($this);
		$this->HUD = new HUD($this);

		$t = intval($this->getConfig()->getAll()["restarter"]["time"]);
		$this->PrestigeSocietyRestarter = new PrestigeSocietyRestarter($t);

		$this->getServer()->getPluginManager()->registerEvents(new EvListener($this), $this);

		$commandMap = $this->getServer()->getCommandMap();
		$command = $commandMap->getCommand('ban');
		$command->setLabel("ban_disabled");
		$command->unregister($commandMap);

		$this->getServer()->getCommandMap()->register("ophelp", new OpHelpCommand($this));
		$this->getServer()->getCommandMap()->register("eat", new EatCommand($this));
		$this->getServer()->getCommandMap()->register("repair", new RepairCommand($this));
		$this->getServer()->getCommandMap()->register("world", new WorldCommand($this));
		$this->getServer()->getCommandMap()->register("fly", new FlyCommand($this));
		$this->getServer()->getCommandMap()->register('setdeaths', new SetDeathsCommand($this));
		$this->getServer()->getCommandMap()->register('setkills', new SetKillsCommand($this));
		$this->getServer()->getCommandMap()->register('setlevel', new SetLevelCommand($this));
		$this->getServer()->getCommandMap()->register('stats', new StatsCommand($this));
		$this->getServer()->getCommandMap()->register('staffmode', new StaffModeCommand($this));
		$this->getServer()->getCommandMap()->register('addcoins', new AddCoinsCommand($this));
		$this->getServer()->getCommandMap()->register('balance', new BalanceCommand($this));
		$this->getServer()->getCommandMap()->register('subtractcoins', new SubtractCoinsCommand($this));
		$this->getServer()->getCommandMap()->register('pay', new PayCommand($this));
		$this->getServer()->getCommandMap()->register('setmoney', new SetCoinsCommand($this));
		$this->getServer()->getCommandMap()->register('addcredits', new AddCreditsCommand($this));
		$this->getServer()->getCommandMap()->register('credits', new CreditsCommand($this));
		$this->getServer()->getCommandMap()->register('subtractcredits', new SubtractCreditsCommand($this));
		$this->getServer()->getCommandMap()->register('setcredits', new SetCreditsCommand($this));
		$this->getServer()->getCommandMap()->register('sban', new SBanCommand($this));
		$this->getServer()->getCommandMap()->register('ban', new BanCommand($this));
		$this->getServer()->getCommandMap()->register('addshop', new AddShopCommand($this));
		$this->getServer()->getCommandMap()->register('removeshop', new RemoveShopCommand($this));
		$this->getServer()->getCommandMap()->register('shop', new ShopCommand($this));
		$this->getServer()->getCommandMap()->register('lsd', new LSDCommand($this));
		$this->getServer()->getCommandMap()->register('rpp', new RPPCommand($this));
		//$this->getServer()->getCommandMap()->register('rankup', new RankUpCommand($this));
		$this->getServer()->getCommandMap()->register('god', new GodCommand($this));
		$this->getServer()->getCommandMap()->register('addinfo', new AddInfoCommand($this));
		$this->getServer()->getCommandMap()->register('nick', new NickCommand($this));
		$this->getServer()->getCommandMap()->register('buyenchant', new EnchantCommand($this));
		$this->getServer()->getCommandMap()->register('clearinventory', new ClearInventoryCommand($this));
		//$this->getServer()->getCommandMap()->register('sellall', new SellAllCommand($this));
		$this->getServer()->getCommandMap()->register('hud', new HUDCommand($this));
		$this->getServer()->getCommandMap()->register('setrank', new SetRankCommand($this));
		$this->getServer()->getCommandMap()->register('seerank', new SeeRankCommand($this));
		$this->getServer()->getCommandMap()->register('lag', new LagCommand($this));
		$this->getServer()->getCommandMap()->register("jobs", new JobCommand($this));
		$this->getServer()->getCommandMap()->register("xpinfo", new \PrestigeSociety\Factions\XpInfoCommand($this));
		$this->getServer()->getCommandMap()->register("spawn", new SpawnCommand($this));
		$this->getServer()->getCommandMap()->register("lang", new LangCommand($this));
		$this->getServer()->getCommandMap()->register("tpworld", new TpWorldCommand($this));

		$this->getServer()->getPluginManager()->registerEvents(new SignsListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new LevelsListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new StaffModeListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new ShopListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new EnchantListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new JobsListener($this), $this);

		$this->getScheduler()->scheduleRepeatingTask(new OptimizeTask($this), 3 * 60 * 20);
		if($this->getConfig()->getAll()["broadcaster"]["enable"]){
			$this->getScheduler()->scheduleRepeatingTask(new BroadcasterTask($this), intval($this->getConfig()->getAll()["broadcaster"]["interval_seconds"]) * 20);
		}
		if($this->getConfig()->getAll()["restarter"]["enable"]){
			$this->getScheduler()->scheduleRepeatingTask(new RestartTask($this), 20);
			$this->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this), intval($this->getConfig()->getAll()["restarter"]["broadcast_time"]) * 20);
		}
		if($this->getConfig()->getAll()["anti_cheat"]["block_flying_hack"]){
			$this->getScheduler()->scheduleRepeatingTask(new CheckFlyingTask($this), 20 * 5);
		}

		if($this->getConfig()->getAll()["HUD"]["enable"]){
			$this->getScheduler()->scheduleRepeatingTask(new HUDUpdateTask($this), 20);
		}

		$this->getInfoParticles()->spawnToAll();

	}

	public function loadCoreInfoArray(){
		$this->coreInfoArray = [
			"author"      => $this->coreInfo->getCreator(),
			"version"     => $this->coreInfo->getVersion(),
			"company"     => $this->coreInfo->getCompany(),
			"server_ip"   => $this->coreInfo->getServerIp(),
			"server_port" => $this->coreInfo->getServerPort(),
		];
	}

	public function logInitialPluginInfo(){
		ConsoleUtils::logArray(["", RandomUtils::textOptions("%%aPrestigeSociety Core"),
			RandomUtils::textOptions("%%e----------------"),
			RandomUtils::textOptions("%%eAuthor: {$this->coreInfoArray["author"]}"),
			RandomUtils::textOptions("%%eVersion: {$this->coreInfoArray["version"]}"),
			RandomUtils::textOptions("%%eServer: {$this->coreInfoArray["company"]}"),
			RandomUtils::textOptions("%%eServer IP: {$this->coreInfoArray["server_ip"]}"),
			RandomUtils::textOptions("%%eServer Port: {$this->coreInfoArray["server_port"]}\n"),
		]);
	}

	/**
	 *
	 * @return string
	 *
	 */
	public function databasesFolder(){
		return $this->getDataFolder() . "database/";
	}

	/**
	 *
	 * @return InfoParticles
	 *
	 */
	public function getInfoParticles(): InfoParticles{
		return $this->PrestigeSocietyParticle;
	}

	public function onDisable(){
		foreach($this->getServer()->getLevelByName("world")->getEntities() as $entity){
			if($entity->namedtag->hasTag("text")){
				if($entity->namedtag->getByte("text") == 1) $entity->close();
			}
		}

		$this->getLogger()->info("[FTP] Closed");
	}

	public function reloadGroupsConfig(){
		$pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms");

		if($pp instanceof PurePerms){
			$groupsConfig = yaml_parse_file($this->getDataFolder() . "chat_format.yml");
			$groups = $pp->getGroups();
			foreach($groups as $group){
				if(!isset($groupsConfig[$name = $group->getName()])){
					$groupsConfig['chat_format'][$name]['chat'] = "&d[@rank][@level] &e<@group> &6@player: &7@message";
					$groupsConfig['chat_format'][$name]['display'] = "&d[@rank][@level] &e<@group> &6@player";
				}
			}
			yaml_emit_file($this->getDataFolder() . "chat_format.yml", $groupsConfig);
		}
	}

	public function pruneGroupsConfig(){
		$pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms");

		if($pp instanceof PurePerms){
			$groupsConfig = yaml_parse_file($this->getDataFolder() . "chat_format.yml");
			foreach($groupsConfig as $key => $value){
				if($pp->getGroup($key) !== null){
					unset($groupsConfig['chat_format'][$key]);
				}
			}
			yaml_emit_file($this->getDataFolder() . "chat_format.yml", $groupsConfig);
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function isInLobby(Player $player): bool{

		if(!isset($this->inLobby[$player->getXuid()])){
			return false;
		}

		return $this->inLobby[$player->getXuid()];
	}

	/**
	 *
	 * @param Player $player
	 * @param bool $bool
	 *
	 */
	public function setIsInLobby(Player $player, $bool = true){
		$this->inLobby[$player->getXuid()] = $bool;
	}

	/**
	 *
	 * @return PrestigeSocietyAntiCheat
	 *
	 */
	public function PrestigeSocietyAntiCheat(): PrestigeSocietyAntiCheat{
		return $this->PrestigeSocietyAntiCheat;
	}

	/**
	 * @return PrestigeSocietyCountHomes
	 */
	public function getCountHomes(): PrestigeSocietyCountHomes{
		return $this->PrestigeSocietyCountHomes;
	}

	/**
	 *
	 * @return PrestigeSocietyChat
	 *
	 */
	public function PrestigeSocietyChat(): PrestigeSocietyChat{
		return $this->PrestigeSocietyChat;
	}

	/**
	 *
	 * @return PrestigeSocietyRestarter
	 *
	 */
	public function PrestigeSocietyRestarter(): PrestigeSocietyRestarter{
		return $this->PrestigeSocietyRestarter;
	}

	/**
	 *
	 * @return mixed
	 *
	 */
	public function getCoreInfoArray(): array{
		return $this->coreInfoArray;
	}

	/**
	 *
	 * @return array
	 *
	 */
	public function getMessages(){
		return $this->messages->getAll();
	}

	/**
	 *
	 * @param $from
	 *
	 * @param $message
	 *
	 * @return mixed
	 *
	 */
	public function getMessage($from, $message){
		return $this->messages->getAll()[$from][$message];
	}

	/**
	 *
	 * @return PrestigeSocietySigns
	 *
	 */
	public function PrestigeSocietySigns(): PrestigeSocietySigns{
		return $this->PrestigeSocietySigns;
	}

	/**
	 *
	 * @return PrestigeSocietyLevels
	 *
	 */
	public function PrestigeSocietyLevels(): PrestigeSocietyLevels{
		return $this->PrestigeSocietyLevels;
	}

	public function PrestigeSocietyJobs(): PrestigeSocietyJobs{
		return $this->PrestigeSocietyJobs;
	}

	public function getPrestigeSocietyFaction(): PrestigeSocietyFactions{
		return $this->PrestigeSocietyFaction;
	}

	public function getPrestigeSocietyLang(): PrestigeSocietyLang{
		return $this->PrestigeSocietyLang;
	}

	/**
	 * @return PrestigeSocietyStaffMode
	 *
	 */
	public function getPrestigeSocietyStaffMode(): PrestigeSocietyStaffMode{
		return $this->PrestigeSocietyStaffMode;
	}

	/**
	 *
	 * @return PrestigeSocietyEconomy
	 *
	 */
	public function getPrestigeSocietyEconomy(): PrestigeSocietyEconomy{
		return $this->PrestigeSocietyEconomy;
	}

	/**
	 * @return PrestigeSocietyCredits
	 */
	public function getPrestigeSocietyCredits(): PrestigeSocietyCredits{
		return $this->PrestigeSocietyCredits;
	}


	/**
	 * @return PrestigeSocietyExperience
	 */
	public function getPrestigeSocietyExperience(): PrestigeSocietyExperience{
		return $this->PrestigeSocietyExperience;
	}

	/**
	 * @return PrestigeSocietyNeeded
	 */
	public function getPrestigeSocietyNeeded(): PrestigeSocietyNeeded{
		return $this->PrestigeSocietyNeeded;
	}

	/**
	 * @return PrestigeSocietyEnchantments
	 */
	public function getPrestigeSocietyEnchantments(): PrestigeSocietyEnchantments{
		return $this->PrestigeSocietyEnchantments;
	}

	/**
	 *
	 * @return PrestigeSocietyTradeUI
	 *
	 */
	public function getPrestigeSocietyTradeUI(): PrestigeSocietyTradeUI{
		return $this->PrestigeSocietyTradeUI;
	}

	/**
	 *
	 * @return FunBox
	 *
	 */
	public function getFunBox(): FunBox{
		return $this->FunBox;
	}

	/**
	 *
	 * @return PrestigeSocietyRanks
	 *
	 */
	public function getPrestigeSocietyRanks(): PrestigeSocietyRanks{
		return $this->PrestigeSocietyRanks;
	}

	/**
	 *
	 * @return PrestigeSocietyNicknames
	 *
	 */
	public function getPrestigeSocietyNicks(): PrestigeSocietyNicknames{
		return $this->PrestigeSocietyNicks;
	}

	/**
	 *
	 * @return HUD
	 *
	 */
	public function getHUD(): HUD{
		return $this->HUD;
	}
}