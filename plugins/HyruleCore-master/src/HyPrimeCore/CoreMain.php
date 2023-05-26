<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2017-2018, larryTheCoder, Hyrule Minigame Division
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * - Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace HyPrimeCore;

use HyPrimeCore\buttonInterface\ButtonInterface;
use HyPrimeCore\cosmetics\gadgets\Gadget;
use HyPrimeCore\cosmetics\gadgets\GadgetManager;
use HyPrimeCore\dependencies\Bounty\BountyHandler;
use HyPrimeCore\dependencies\PluginDispatch;
use HyPrimeCore\dependencies\RegionCore\RegionDispatch;
use HyPrimeCore\formAPI\FormAPI;
use HyPrimeCore\nms\CoreListener;
use HyPrimeCore\nms\CoreListenerSW;
use HyPrimeCore\nms\ListenerLegacy;
use HyPrimeCore\nms\ListenerUpdated;
use HyPrimeCore\panel\Panel;
use HyPrimeCore\player\PlayerData;
use HyPrimeCore\tasks\BroadcastingSystem;
use HyPrimeCore\tasks\IdleCheckTask;
use HyPrimeCore\tasks\SendMOTD;
use HyPrimeCore\utils\block\BlockTaskingManager;
use HyPrimeCore\utils\Settings;
use HyPrimeCore\utils\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\Config;

/**
 * This is the class for the Main-Prime-Core
 * for Hyrule Network. Rev#201-394-675d
 *
 * @package HPrimeCore
 */
class CoreMain extends PluginBase {

	const CONFIG_VERSION = 3;
	const OS = ["unknown", "Android", "iOS", "MacOS", "FireOS", "GearVR", "HoloLens", "Windows10", "Windows", "Dedicated", "Orbis", "NX"];

	/** @var CoreMain */
	private static $instance;
	/** @var int[] */
	public $idlingTime = [];
	/** @var bool[] */
	public $justJoined = [];
	/** @var bool[] */
	public $playersGadget = [];
	/** @var Player[] */
	public $fly = [];
	/** @var PlayerData[] */
	private $data;
	/** @var FormAPI */
	private $formAPI;
	/** @var Panel */
	private $panel;
	/** @var null|ButtonInterface */
	private $interface = null;
	/** @var Player[] */
	private $bypassDamage = [];

	/** @var BlockTaskingManager */
	private $task;
	/** @var Config */
	public $gameTimeCfg;

	/**
	 * Add bypass to player, ensure that player could kill and
	 * damage the player
	 *
	 * @param Player $p
	 */
	public static function addBypass(Player $p){
		CoreMain::get()->bypassDamage[$p->getName()] = $p;
	}

	public static function get(){
		return CoreMain::$instance;
	}

	/**
	 * Remove bypass protection for player, used for other
	 * plugin or features
	 *
	 * @param Player $p
	 * @return bool
	 */
	public static function removeBypass(Player $p): bool{
		if(isset(CoreMain::get()->bypassDamage[$p->getName()])){
			unset(CoreMain::get()->bypassDamage[$p->getName()]);

			return true;
		}

		return false;
	}

	public static function sendVersion(CommandSender $sender){
		$sender->sendMessage("§e--- §6Hyrule§cNetwork §3(C) §72015-2018 §e---");
		$sender->sendMessage("§6Owner: §dAlair069");
		$sender->sendMessage("§6Server Manager: §dMrPotato101");
		$sender->sendMessage("§6Head Management: §d@larryTheCoder");
		$sender->sendMessage("§3HyPrimeCore §aREV#201-394-675d §c(PRIVATE)");
		$sender->sendMessage("§3SkyWarsForPE §av1.9.8-Maya §c(PRIVATE)");
		$sender->sendMessage("§3ASkyBlock §av0.4.2 §6(OPEN SOURCE) ");
	}

	public function getBypasses(){
		return $this->bypassDamage;
	}

	public function onLoad(){
		CoreMain::$instance = $this;
		$this->getServer()->getLogger()->info("[H] §eStarting to load HyruleCore...");
		Utils::ensureDirectory($this);
		$this->saveResource("config.yml");
		$this->saveResource("language/en_US.yml", true);
		$this->saveResource("language/pt_BR.yml");
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		if($cfg->get("config-version") !== CoreMain::CONFIG_VERSION){
			$this->getServer()->getLogger()->info($this->getPrefix() . "[H] §cCONFIGURATION UPDATE! UPDATE CONFIG.YML");
			rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.yml.old");
			$this->saveResource("config.yml");
		}
		Settings::load(new Config($this->getDataFolder() . "config.yml", Config::YAML));
	}

	public function getPrefix(){
		return Settings::$prefix;
	}

	public function onEnable(){
		$this->getServer()->getLogger()->info($this->getPrefix() . "§7Starting booting sequence");
		$this->registerEnchantment();
		$this->registerScheduler();
		$this->startModuleStartup();
		$this->registerDependencies();

		$this->formAPI = new FormAPI($this);
		$this->panel = new Panel($this);
		$this->task = new BlockTaskingManager($this);

		$inj = $this->getServer()->getPluginManager()->getPlugin("SkyWarsForPE");

		$this->startListener(!is_null($inj));
	}

	public function onDisable(){
		$this->shutdownDependencies();
	}

	/** @var PluginDispatch[] */
	private $dependencies = [];

	private function registerDependencies(){
		/** @var PluginDispatch[] $dependencies */
		$dependencies = [];
		$dependencies[] = new BountyHandler();
		$dependencies[] = new RegionDispatch();

		foreach($dependencies as $pl){
			Utils::send("Starting dependency: " . $pl->getName());

			if(!is_dir($this->getDataFolder() . "/" . $pl->getName())){
				mkdir($this->getDataFolder() . "/" . $pl->getName(), 0777);
			}

			$pl->startDependency();
			$this->getServer()->getCommandMap()->registerAll($pl->getName(), $pl->getCommands());
		}

		$this->dependencies = $dependencies;
	}

	private function shutdownDependencies(){
		foreach($this->dependencies as $pl){
			Utils::send("Shutting down dependency: " . $pl->getName());

			$pl->shutdownDependency();
		}
	}

	public function startListener(bool $implementation){
		$eventLoader = $this->getServer()->getPluginManager();
		$eventLoader->registerEvents(new CoreListener($this), $this);
		if($implementation){
			$eventLoader->registerEvents(new CoreListenerSW($this), $this);
			$this->interface = new ButtonInterface($this);
			//new KitInjectionModule($this);
		}
		try{
			// Check if it a legacy, uh...
			$reflection = new \ReflectionClass(get_class($this->getServer()));
			foreach($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
				if(!$method->isStatic()){
					$parameters = $method->getParameters();
					if(count($parameters) === 0 && $method->getName() === "getScheduler"){
						$eventLoader->registerEvents(new ListenerLegacy(), $this);

						return;
					}
				}
			}
		}catch(\ReflectionException $e){
			// Ignored
		}
		$eventLoader->registerEvents(new ListenerUpdated(), $this);
	}

	/**
	 * This function is used to register some of the pocketmine unregistered enchantments
	 * May not be working fine
	 */
	private function registerEnchantment(){
		$this->getServer()->getLogger()->info($this->getPrefix() . "§7Registering Enchantments...");
		Enchantment::registerEnchantment(new Enchantment(Enchantment::THORNS, "%enchantment.protect.thorns", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, 0, 3));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::DEPTH_STRIDER, "%enchantment.waterspeed", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_FEET, 0, 3));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::AQUA_AFFINITY, "%enchantment.protect.wateraffinity", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_FEET, 0, 1));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::SHARPNESS, "%enchantment.weapon.sharpness", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, 0, 5));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::SMITE, "%enchantment.weapon.smite", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, 0, 5));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::BANE_OF_ARTHROPODS, "%enchantment.weapon.arthropods", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, 0, 5));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::KNOCKBACK, "%enchantment.weapon.knockback", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, 0, 2));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::FIRE_ASPECT, "%enchantment.weapon.fireaspect", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, 0, 2));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::LOOTING, "%enchantment.weapon.looting", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, 0, 3));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::FORTUNE, "%enchantment.mining.fortune", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_TOOL, 0, 3));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::POWER, "%enchantment.bow.power", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_BOW, 0, 5));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::PUNCH, "%enchantment.bow.knockback", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_BOW, 0, 2));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::FLAME, "%enchantment.bow.flame", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_BOW, 0, 1));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::INFINITY, "%enchantment.bow.infinity", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_BOW, 0, 1));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::LUCK_OF_THE_SEA, "%enchantment.fishing.fortune", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_FISHING_ROD, 0, 3));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::LURE, "%enchantment.fishing.lure", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_FISHING_ROD, 0, 3));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::FROST_WALKER, "%enchantment.waterwalk", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_ARMOR, 0, 2)); // TODO: verify name
		Enchantment::registerEnchantment(new Enchantment(Enchantment::MENDING, "%enchantment.mending", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_ARMOR, 0, 1)); // TODO: verify name
	}

	/**
	 * Register scheduler and task for the Core
	 */
	private function registerScheduler(){
		$this->getSchedulerForce()->scheduleRepeatingTask(new BroadcastingSystem($this), Settings::$messageInterval * 20);
		$this->getSchedulerForce()->scheduleRepeatingTask(new IdleCheckTask($this), 20);
		$this->getSchedulerForce()->scheduleRepeatingTask(new SendMOTD($this), $this->getConfig()->getNested("motd.delay"));
	}

	private function startModuleStartup(){
		$inj = $this->getServer()->getPluginManager()->getPlugin("SkyWarsForPE");

		// Check if injection is available
		if(is_null($inj)){
			$this->getServer()->getLogger()->debug("No SkyWars plugin were found, it is required to use this core.");

			return;
		}

		// Forcefully enable this plugin.
		if(!$inj->isEnabled()){
			Server::getInstance()->enablePlugin($inj);
		}
	}

	public function getFormAPI(){
		return $this->formAPI;
	}

	public function savePlayerData(Player $p, PlayerData $data){
		if(isset($this->data[$p->getName()])){
			unset($this->data[$p->getName()]);
		}
		$this->data[$p->getName()] = $data;
	}

	public function getPlayerData(Player $p): PlayerData{
		if(isset($this->data[$p->getName()])){
			return $this->data[$p->getName()];
		}
		$data = new PlayerData();
		$this->data[$p->getName()] = $data;

		return $data;
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		switch(strtolower($cmd->getName())){
			case "cloak":
				if(!($sender instanceof Player)){
					$sender->sendMessage("Please use this command in-game");
					break;
				}
				$this->getPanel()->showCloakConfiguration($sender);
				break;
			case "fly":
				if(!($sender instanceof Player)){
					$sender->sendMessage("Please use this command in-game");
					break;
				}
				if(!$sender->hasPermission("ability.fly")){
					$sender->sendMessage($this->getMessage($sender, "error.no-permission"));
					break;
				}

				if(isset($this->fly[$sender->getName()])){
					$sender->setAllowFlight(false);
					$sender->sendMessage($this->getPrefix() . $this->getMessage($sender, "general.flight-disabled"));
					unset($this->fly[$sender->getName()]);
				}else{
					$sender->setAllowFlight(true);
					$sender->sendMessage($this->getPrefix() . $this->getMessage($sender, "general.flight-enabled"));
					$this->fly[$sender->getName()] = [$sender, true];
				}
				break;
			case "setupinterface":
				if(!($sender instanceof Player)){
					$sender->sendMessage("Please use this command in-game");
					break;
				}
				if(!$sender->hasPermission("admin.setup")){
					$sender->sendMessage($this->getMessage($sender, "error.no-permission"));
					break;
				}
				if(is_null($this->interface)){
					$sender->sendMessage("There is no SkyWarsForPE plugin detected. Sorry man");
					break;
				}
				$this->interface->setupInterface($sender);
				break;
			case "showops":
				if(!$sender->hasPermission("admin.command")){
					$sender->sendMessage($this->getMessage($sender, "error.no-permission"));
					break;
				}
				$ops = Server::getInstance()->getOps();
				$var = array_keys($ops->getAll());
				$split = implode(", ", $var);
				$sender->sendMessage("Operators[" . count($var) . "]: " . $split);
				break;
			case "setblock":
				if(!($sender instanceof Player)){
					$sender->sendMessage("Please use this command in-game");
					break;
				}
				if(!$sender->hasPermission("admin.command")){
					$sender->sendMessage($this->getMessage($sender, "error.no-permission"));
					break;
				}
				$this->task->registerTemporary($sender, BlockTaskingManager::BLOCK_DIAMOND);
				$sender->sendMessage("Registered successfully");
				break;
			case "trampoline":
				if(!($sender instanceof Player)){
					$sender->sendMessage("Please use this command in-game");
					break;
				}
				if(!$sender->hasPermission("admin.command")){
					$sender->sendMessage($this->getMessage($sender, "error.no-permission"));
					break;
				}
				if(!isset($this->playersGadget[$sender->getName()])){
					GadgetManager::equipCloak($sender, Gadget::TRAMPOLINE);
				}else{
					GadgetManager::unequipCloak($sender);
				}
				break;
		}

		return true;
	}

	public function getPanel(){
		return $this->panel;
	}

	/**
	 * Get the translation for the user. Supports client localization
	 *
	 * @param Player|CommandSender|null $p
	 * @param $key
	 * @param string[] $replacement
	 * @return mixed
	 */
	public function getMessage($p, $key, array $replacement = []){
		if($p === null || !($p instanceof Player)){
			if(!$message = (new Config($this->getDataFolder() . "language/en_US.yml", Config::YAML))->getNested($key)){
				$this->getLogger()->warning("Message $key not found.");
				$message = "";
			}
		}else{
			switch(strtolower($p->getLocale())){
				case "en_us":
					$locale = new Config($this->getDataFolder() . "language/en_US.yml", Config::YAML);
					if(!$message = $locale->getNested($key)){
						$message = "";
					}
					break;
				case "pt_pt":
				case "pc_pt":
				case "pt_br":
					$locale = new Config($this->getDataFolder() . "language/pt_BR.yml", Config::YAML);
					if(!$message = $locale->getNested($key)){
						$message = "";
					}
					break;
				default:
					$locale = new Config($this->getDataFolder() . "language/en_US.yml", Config::YAML);
					if(!$message = $locale->getNested($key)){
						$message = "";
					}
					break;
			}
		}
		if($message === ""){
			$locale = new Config($this->getDataFolder() . "language/en_US.yml", Config::YAML);
			if(!$message = $locale->getNested($key)){
				$this->getLogger()->warning("$key not found in default locale.");
				$message = "";
			}
		}
		foreach($replacement as $index => $value){
			$message = str_replace('{' . $index . '}', $value, $message);
		}

		return $message;
	}

	/**
	 * Force to get the scheduler instance.
	 * Warning, using this to get async pool is NOT SUPPORTED
	 * create async pool by checking it by yourself.
	 *
	 * @return \pocketmine\scheduler\ServerScheduler|TaskScheduler
	 */
	public function getSchedulerForce(){
		try{
			$reflection = new \ReflectionClass(get_class($this->getServer()));
			foreach($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
				if(!$method->isStatic()){
					$parameters = $method->getParameters();
					if(count($parameters) === 0 && $method->getName() === "getScheduler"){
						return $this->getServer()->getScheduler();
					}
				}
			}
		}catch(\ReflectionException $e){
			// Ignored
		}

		return parent::getScheduler();
	}
}