<?php

namespace Richen\Clans;

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

class ClanMain extends PluginBase implements Listener {
	
	public $db;
	public $prefs;
	private static $instance;
	
	public function onEnable() {
		
		@mkdir($this->getDataFolder());
		
		$this->getServer()->getPluginManager()->registerEvents(new ClanListener($this), $this);
		$this->fCommand = new ClanCommands($this);
		$this->db = new \SQLite3($this->getDataFolder() . "database.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS master (player TEXT PRIMARY KEY COLLATE NOCASE, clan TEXT, rank TEXT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS confirm (player TEXT PRIMARY KEY COLLATE NOCASE, clan TEXT, invitedby TEXT, timestamp INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS motdrcv (player TEXT PRIMARY KEY, timestamp INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS motd (clan TEXT PRIMARY KEY, message TEXT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS plots(clan TEXT PRIMARY KEY, x1 INT, z1 INT, x2 INT, z2 INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS home(clan TEXT PRIMARY KEY, x INT, y INT, z INT);");
		
		self::$instance = $this;
	}
	
	public static function getInstance(){
		return self::$instance;
	}
		
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$this->fCommand->onCommand($sender, $command, $label, $args);
	}
	
	public function isInClan($player) {
		$player = strtolower($player);
		$result = $this->db->query("SELECT * FROM master WHERE player='$player';");
		$array = $result->fetchArray(SQLITE3_ASSOC);
		return empty($array) == false;
	}
	
	public function isLeader($player){
		$player = strtolower($player);
		$clan = $this->db->query("SELECT * FROM master WHERE player='$player';");
		$clanArray = $clan->fetchArray(SQLITE3_ASSOC);
		return $clanArray["rank"] == "Leader";
	}
	
	public function isMember($player) {
		$player = strtolower($player);
		$clan = $this->db->query("SELECT * FROM master WHERE player='$player';");
		$clanArray = $clan->fetchArray(SQLITE3_ASSOC);
		return $clanArray["rank"] == "Member";
	}
	
	public function getPlayerClan($player) {
		$player = strtolower($player);
		$clan = $this->db->query("SELECT * FROM master WHERE player='$player';");
		$clanArray = $clan->fetchArray(SQLITE3_ASSOC);
		return $clanArray["clan"];
	}
	
	public function getLeader($clan) {
		$leader = $this->db->query("SELECT * FROM master WHERE clan='$clan' AND rank='Leader';");
		$leaderArray = $leader->fetchArray(SQLITE3_ASSOC);
		return $leaderArray['player'];
	}
	
	public function clanExists($clan) {
		$result = $this->db->query("SELECT * FROM master WHERE clan='$clan';");
		$array = $result->fetchArray(SQLITE3_ASSOC);
		return empty($array) == false;
	}
	
	public function sameClan($player1, $player2) {
		$clan = $this->db->query("SELECT * FROM master WHERE player='$player1';");
		$player1Clan = $clan->fetchArray(SQLITE3_ASSOC);
		$clan = $this->db->query("SELECT * FROM master WHERE player='$player2';");
		$player2Clan = $clan->fetchArray(SQLITE3_ASSOC);
		return $player1Clan["clan"] == $player2Clan["clan"];
	}
	
	public function getNumberOfPlayers($clan) {
		$query = $this->db->query("SELECT COUNT(*) as count FROM master WHERE clan='$clan';");
		$number = $query->fetchArray();
		return $number['count'];
	}
	
	public function isNameBanned($name) {
		$bannedNames = explode(":", file_get_contents($this->getDataFolder() . "BannedNames.txt"));
		return in_array($name, $bannedNames);
	}
	
	public function formatMessage($string, $confirm = false) {
		return "(Кланы) $string";
	}
	
	public function motdWaiting($player) {
		$stmt = $this->db->query("SELECT * FROM motdrcv WHERE player='$player';");
		$array = $stmt->fetchArray(SQLITE3_ASSOC);
		$this->getServer()->getLogger()->info("\$player = " . $player);
		return !empty($array);
	}
	
	public function getMOTDTime($player) {
		$stmt = $this->db->query("SELECT * FROM motdrcv WHERE player='$player';");
		$array = $stmt->fetchArray(SQLITE3_ASSOC);
		return $array['timestamp'];
	}
	
	public function setMOTD($clan, $player, $msg) {
		$stmt = $this->db->prepare("INSERT OR REPLACE INTO motd (clan, message) VALUES (:clan, :message);");
		$stmt->bindValue(":clan", $clan);
		$stmt->bindValue(":message", $msg);
		$result = $stmt->execute();
		
		$this->db->query("DELETE FROM motdrcv WHERE player='$player';");
	}
	
	public function onDisable() {
		$this->db->close();
	}
}
