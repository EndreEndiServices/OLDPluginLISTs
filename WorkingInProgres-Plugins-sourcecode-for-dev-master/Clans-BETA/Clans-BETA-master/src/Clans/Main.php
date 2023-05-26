<?php

namespace Clans;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\block\Snow;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use Clans\utils\Session;

class Main extends PluginBase implements Listener
{
	
	public $db;
	public $prefs;
	public $commands;
	public $clans = array();
	public $sessions = array();
	
	public function onEnable()
	{	
		@mkdir($this->getDataFolder());
		
		if(!file_exists($this->getDataFolder() . "Clans.fp"))
		{
			$file = fopen($this->getDataFolder() . "Clans.fp", "w");
			$txt = "";
			fwrite($file, $txt);
		}
		
		if(!file_exists($this->getDataFolder() . "BannedNames.txt"))
		{
			$file = fopen($this->getDataFolder() . "BannedNames.txt", "w");
			$txt = "Admin:admin:Staff:staff:Owner:owner:Builder:builder:Op:OP:op";
			fwrite($file, $txt);
		}
		
		$this->getServer()->getPluginManager()->registerEvents(new ClanListener($this), $this);
		$this->cCommand = new ClanCommands($this);
		
		$this->prefs = new Config($this->getDataFolder() . "Prefs.yml", CONFIG::YAML, array(
				"Leader Idenfitier" => "**",
				"Officer Identifier" => "*",
				"Factions In Overhead Nametag" => true,
				"Maximum Faction Name Length" => 20,
				"Maximum Players Per Faction" => 10,
				"Developer Mode" => false,
		));
		
		$this->commands = new Config($this->getDataFolder() . "Commands.yml", CONFIG::YAML, array(
				"/c create" => true,
				"/c delete" => true,
				"/c demote" => true,
				"/c desc" => true,
				"/c home" => true,
				"/c info" => true,
				"/c invite" => true,
				"/c kick" => true,
				"/c leader" => true,
				"/c leave" => true,
				"/c promote" => true,	
		));
		
		if($this->devModeEnabled())
		{
			$this->getServer()->getLogger()->info(TextFormat::RED . "Clans Developer Mode has been enabled, you may turn this off any time by setting 'DeveloperMode' to false in settings.");
		}
	
		if(!empty(file_get_contents($this->getDataFolder() . "Clans.fp")))
		{
			$this->loadAll();
		}
	}
		
	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
		$this->cCommand->onCommand($sender, $command, $label, $args);
	}
	
	public function addClan(Clan $clan)
	{
		array_push($this->clans, $clan);
		if($this->devModeEnabled())
		{
			$this->getServer()->getLogger()->info(TextFormat::GREEN . $clan->getName() . " has been registered");
		}
	}
	
	public function removeClan(Clan $clan)
	{
		$key = array_search($clan, $this->clan);
		if($key !== false)
		$name = $clan->getName();
		{
			unset($this->clans[$key]);
			{
				if($this->devModeEnabled())
				{
					$this->getServer()->getLogger(TextFormat::GREEN . "" . $name . " has been disbanded");
				}
			}
		}
	}
	
	public function getClans()
	{
		return $this->clans;
	}
	
	public function getFaction($name)
	{
		foreach($this->fclans as $clans)
		{
			if($clan->getName() == $name)
			{
				return $clans;
			}
		}
		return false;
	}
	
	public function clanExists($clan)
	{
		return isset($this->clans[$clan]);
	}
	
	public function sameClan($player1, $player2) 
	{
		$this->getSession($player1)->getClan() == $this->getSession($player2)->getClan();
	}
	
	public function isNameBanned($name) {
		$bannedNames = explode(":", file_get_contents($this->getDataFolder() . "BannedNames.txt"));
		return in_array($name, $bannedNames);
	}
	
	public function formatMessage($string, $confirm = false) {
		if($confirm) {
			return "[" . TextFormat::BLUE . "ClansPro" . TextFormat::WHITE . "] " . TextFormat::GREEN . "$string";
		} else {	
			return "[" . TextFormat::BLUE . "ClansPro" . TextFormat::WHITE . "] " . TextFormat::RED . "$string";
		}
	}
	
	public function saveAll()
	{
		$this->getServer()->getLogger()->info($this->formatMessage("Saving Clans..."));
		$exportArray = [];
		foreach($this->clans as $clans)
		{
			$exportArray[] = $clan->export();
		}
		$txt = implode("*", $exportArray);
		$file = fopen($this->getDataFolder() . "Clans.fp", "w");
		fwrite($file, $txt);
		$this->getServer()->getLogger()->info($this->formatMessage("Clans Saved!", true));
	}
	
	public function loadAll()
	{
		$this->getServer()->getLogger()->info($this->formatMessage("Loading Clans..."));
		$file = explode("*", file_get_contents($this->getDataFolder() . "Clans.fp"));
		foreach($file as $faction)
		{
			Faction::import($faction, $this);
		}
		$this->getServer()->getLogger()->info($this->formatMessage("Clans Loaded!", true));
	}
	
	public function addSession(Player $player)
	{
		$this->sessions[$player->getId()] = new Session($this, $player);
	}
	
	public function removeSession(Player $player)
	{
		if(isset($this->sessions[$id = $player->getId()])){
			unset($this->sessions[$id]);
		}
	}
	
	public function getSession(Player $player)
	{
		return isset($this->sessions[$id = $player->getId()]) ? $this->sessions[$id] : null;
	}
	
	public function getSessionFromName($playerName)
	{
		if($player = $this->getServer()->getPlayer($playerName) instanceof Player)
		{
			return $this->getSession($this->getServer()->getPlayer($playerName));
		}
		return false;
	}
	
	public function devModeEnabled()
	{
		return $this->prefs->get("Developer Mode");
	}
	
	public function onDisable()
	{
		$this->saveAll();
	}
}
