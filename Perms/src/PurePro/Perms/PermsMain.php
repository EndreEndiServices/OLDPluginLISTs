<?php

/**
 *     
 *    ███  ███  █      ███  █   █ ████   ███   ███   ███
 *        █     █     █   █ ██  █ █   █ █   █ █     █      ███  █  █
 *     █   ███  █     █████ █ █ █ █   █ █████ █ ██  ███    █ █  █  █
 *     █      █ █   █ █   █ █  ██ █   █ █   █ █   █ █      ██   █  █
 *    ███  ███  ████  █   █ █   █ ████  █   █  ███   ███ █ █ █  ███
 *     
**/

namespace Richen\Perms;

use Richen\Perms\Commands\{Groups,SetGroup,Donate,Prefix,Nick};

use pocketmine\{Player,IPlayer};

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;

use pocketmine\scheduler\CallbackTask;

class PermsMain extends PluginBase
{
    const CORE_PERM = "\x70\x70\x65\x72\x6d\x73\x2e\x63\x6f\x6d\x6d\x61\x6e\x64\x2e\x70\x70\x69\x6e\x66\x6f";
	
	public $flood, $groups = [];
	
	private $attachments = [];
	
	private static $instance;
    
    public function onEnable(){
		$this->db = new \SQLite3($this->getDataFolder() . "players.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS players(name TEXT PRIMARY KEY, info TEXT);");
		
        $this->getServer()->getPluginManager()->registerEvents(new PermsListener($this), $this);
		
		self::$instance = $this;
		
		foreach($this->getServer()->getOnlinePlayers() as $player) $this->registerPlayer($player);
		
		foreach(array_keys($this->getConfig()->getAll()) as $groupName) $this->groups[] = mb_strtolower($groupName);
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "antiFlood")), 20 * 3);
		
        $this->registerCommands();
    }
	
	public function onLoad(){
		$this->saveDefaultConfig();
	}
	
	public static function getInstance(){
		return self::$instance;
	}
    
    private function registerCommands(){
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->register("groups",	new Groups($this, "groups", "Список привилегий", "perms.groups"));
        $commandMap->register("setgroup", new SetGroup($this, "setgroup", "Выдать привилегию", "perms.setgroup"));
        $commandMap->register("donate", new Donate($this, "donate", "Выдать привилегию", "perms.donate"));
        $commandMap->register("nick", new Nick($this, "nick", "Сменить ник", "perms.nick"));
        $commandMap->register("prefix", new Prefix($this, "prefix", "Сменить префикс", "perms.prefix"));
    }
	
	public function onDisable(){
		foreach($this->getServer()->getOnlinePlayers() as $player) $this->unregisterPlayer($player);
	}
	
	public function getGroup($name){
		return $this->getPlayerInfo($name)["group"];
	}
	
	public function getPrefix($name){
		return $this->getPlayerInfo($name)["prefix"];
	}
	
	public function getNick($name){
		return $this->getPlayerInfo($name)["nick"];
	}
	
	public function getPlayerInfo($name){
		($name instanceof Player) ? $name = mb_strtolower($name->getName()) : $name = mb_strtolower($name);
		$result = $this->db->query("SELECT * FROM players WHERE name='".$name."';")->fetchArray(SQLITE3_ASSOC);
		return $result["info"] == null 
		? json_decode("{\"group\":\"guest\",\"nick\":null,\"prefix\":null}", 1) 
		: json_decode($result["info"], 1);
	}

	public function setPlayerInfo($name, $info){
		($name instanceof Player) ? $name = mb_strtolower($name->getName()) : $name = mb_strtolower($name);
		$set = $this->db->prepare("INSERT OR REPLACE INTO players(name, info) VALUES (:name, :info);");
		$set->bindValue(":name", $name);
		$set->bindValue(":info", json_encode($info));
		return $set->execute();
	}

	public function remPlayerInfo($name){
		($name instanceof Player) ? $name = mb_strtolower($name->getName()) : $name = mb_strtolower($name);
		$set = $this->db->prepare("DELETE FROM players WHERE name='".$name."';");
		return $set->execute();
	}
	
    public function getGroups(){
        return $this->groups;
    }

    public function getPermissions($player){
        $group = $this->getGroup($player->getName());
		$perms = $this->getConfig()->getAll()[$group]["permissions"];
		
		foreach($this->getConfig()->getAll()[$group]["inheritance"] as $groupname){
			
			$otherperms = $this->getConfig()->getAll()[$groupname]["permissions"];
			
			foreach($otherperms as $perm){
				if(!in_array($perm, $perms)) $perms[] = $perm;
			}
		}
        return $perms;
    }

    public function getPlayer($name){
        $player = $this->getServer()->getPlayer($name);
        return $player instanceof Player ? $player : $this->getServer()->getOfflinePlayer($name);
    }

    public function registerPlayer(Player $player){
        $uniqueId = $player->getUniqueId()->toString();
        if(isset($this->attachments[$uniqueId])) $this->unregisterPlayer($player);
		
        $attachment = $player->addAttachment($this);
        $this->attachments[$uniqueId] = $attachment;
        $this->updatePermissions($player);
    }
   
	public function updatePermissions(IPlayer $player){
		if($player instanceof Player){
			$permissions = [];
			foreach($this->getPermissions($player) as $permission){
				if($permission === "*"){
					foreach($this->getServer()->getPluginManager()->getPermissions() as $tmp){ $permissions[$tmp->getName()] = true; }
				}else{
					$isNegative = substr($permission, 0, 1) === "-";
					if($isNegative) $permission = substr($permission, 1);
					$value = !$isNegative;
					if($permission === self::CORE_PERM) $value = true;
					$permissions[$permission] = $value;
				}
			}
			$attachment = $this->attachments[$player->getUniqueId()->toString()];
			$attachment->clearPermissions();
			$attachment->setPermissions($permissions);
        }
	}
	
	public function unregisterPlayer(Player $player){
		$uniqueId = $player->getUniqueId()->toString();
		
		if(isset($this->attachments[$uniqueId])) $player->removeAttachment($this->attachments[$uniqueId]);
		
		unset($this->attachments[$uniqueId]);
	}
	
	public function antiFlood(){
		foreach($this->getServer()->getOnlinePlayers() as $player) if(isset($this->flood[$player->getName()])) unset($this->flood[$player->getName()]);
	}
	
	public function formatMessage(Player $player, $message)
	{
        $group = $this->getGroup($player->getName());
		
		$format = $this->getConfig()->getNested($group.".chat");
		
		$marry = $this->getServer()->getPluginManager()->getPlugin("MarryPlus");
		$marry == null ? $marry = "" : $marry = $marry->getPrefix(mb_strtolower($player->getName()));
		
		$clan = $this->getServer()->getPluginManager()->getPlugin("Clans");
		$clan == null ? $clan = "" : $clan = "§8[§3" . $clan->getPlayerFaction($player->getName()) . "§8]§r";
		
		$info = $this->getPlayerInfo($player->getName());
		
		$info["nick"] == null ? $name = $player->getName() : $name = $info["nick"];
		$info["prefix"] == null ? $prefix = $this->getConfig()->getNested($group . ".prefix") : $prefix = $info["prefix"];
		
		$format = str_replace("{prefix}", $prefix, $format);
		$format = str_replace("{name}", $name, $format);
		$format = str_replace("{marry}", "", $format);
		$format = str_replace("{clan}", $clan, $format);
		$format = str_replace("{message}", $message, $format);
		
		return $format;
	}

	public function getNameTag(Player $player)
	{
        $group = $this->getGroup($player->getName());
		
		$format = $this->getConfig()->getNested($group . ".nametag");
		
		$marry = $this->getServer()->getPluginManager()->getPlugin("MarryPlus");
		$marry == null ? $marry = "" : $marry = $marry->getPrefix(mb_strtolower($player->getName()));
		
		$clan = $this->getServer()->getPluginManager()->getPlugin("Clans");
		$clan == null ? $clan = "" : $clan = $clan->getPrefix(mb_strtolower($player->getName()));
		
		$info = $this->getPlayerInfo($player->getName());
		
		$info["nick"] == null ? $name = $player->getName() : $name = $info["nick"];
		$info["prefix"] == null ? $prefix = $this->getConfig()->getNested($group . ".prefix") : $prefix = $info["prefix"];
		
		$format = str_replace("{prefix}", $prefix, $format);
		$format = str_replace("{name}", $name, $format);
		$format = str_replace("{clan}", $clan, $format);
		$format = str_replace("{marry}", $marry, $format);
		
		return $format;
	}
}