<?php

/*
__PocketMine Plugin__
name=Essentials
description=Essentials
version=2.1
author=KsyMC
class=Essentials
apiversion=10
*/

class Essentials implements Plugin{
	private $api, $server, $lang, $data, $motd, $lastafk, $afk;
	
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
		$this->data = array();
		$this->lastafk = array();
		$this->afk = array();
		$this->server = ServerAPI::request();
		EssentialsAPI::setEssentials($this);
	}
	
	public function init(){
		$this->api->event("server.close", array($this, "handler"));
		$this->api->addHandler("player.join", array($this, "handler"), 5);
		$this->api->addHandler("player.quit", array($this, "handler"), 5);
		$this->api->addHandler("player.chat", array($this, "handler"), 5);
		$this->api->addHandler("player.move", array($this, "handler"), 5);
		$this->api->addHandler("player.spawn", array($this, "handler"), 5);
		$this->api->addHandler("console.check", array($this, "handler"), 5);
		$this->api->addHandler("player.teleport", array($this, "handler"), 5);
		$this->api->addHandler("player.respawn", array($this, "handler"), 5);
		$this->api->addHandler("console.command", array($this, "handler"), 5);
		
		$this->api->console->register("afk", "", array($this, "defaultCommands"));
		$this->api->console->register("home", "<name>", array($this, "defaultCommands"));
		$this->api->console->register("sethome", "<name>", array($this, "defaultCommands"));
		$this->api->console->register("delhome", "<home>", array($this, "defaultCommands"));
		$this->api->console->register("mute", "<player>", array($this, "defaultCommands"));
		$this->api->console->register("back", "", array($this, "defaultCommands"));
		$this->api->console->register("tree", "<tree|brich|redwood>", array($this, "defaultCommands"));
		$this->api->console->register("setspawn", "", array($this, "defaultCommands"));
		$this->api->console->register("burn", "<player> <seconds>", array($this, "defaultCommands"));
		$this->api->console->register("kickall", "[reason]", array($this, "defaultCommands"));
		$this->api->console->register("killall", "[reason]", array($this, "defaultCommands"));
		$this->api->console->register("heal", "[player]", array($this, "defaultCommands"));
		$this->api->console->register("clearinventory", "[player] [item]", array($this, "defaultCommands"));
		$this->readConfig();
		
		console("[INFO] Essentials enabled!");
		$this->api->schedule(20, array($this, "checkAFK"), array(), true);
	}
	
	public function __destruct(){}
	
	public function readConfig(){
		$this->path = $this->api->plugin->createConfig($this, array(
			"login" => array(
				"timeout" => 60,
				"allow-register" => true,
				"allow-chat" => false,
				"allow-commands" => array(),
				"allow-move" => false,
				"pw-higher-then" => 4,
				"pw-less-then" => 15,
				"login-kick" => 5,
			),
			"chat-format" => "<{DISPLAYNAME}> {MESSAGE}",
			"blacklist" => array(
				"placement" => '',
				"usage" => '',
				"break" => '',
			),
			"kits" => array(),
			"auto-afk" => 300,
			"auto-afk-kick" => -1,
			"freeze-afk-players" => false,
			"newbies" => array(
				"kit" => "",
				"message" => "Welcome {DISPLAYNAME} to the server!",
			),
			"creative-item" => array(
				"op" => array(),
				"default" => array(),
			),
			"player-commands" => array(),
		));
		if(file_exists($this->path."messages.yml")){
			$this->lang = new Config($this->path."messages.yml", CONFIG_YAML);
		}else{
			console("[ERROR] \"messages.yml\" file not found!");
		}
		if(is_dir(DATA_PATH."/plugins/Essentials/userdata/") === false){
			mkdir(DATA_PATH."/plugins/Essentials/userdata/");
		}
		
		$this->motd = $this->server->motd;
		$this->server->motd = "";
		$this->config = $this->api->plugin->readYAML($this->path."config.yml");
	}
	
	public function checkAFK(){
		foreach($this->lastafk as $iusername => $time){
			$player = $this->api->player->get($iusername);
			if((time() - $time) == $this->config["auto-afk"]){
				$this->api->chat->broadcast($this->getMessage("userIsAway", array($player->username, "", "", "")));
				$this->afk[$player->iusername] = true;
			}
			if((time() - $time) == $this->config["auto-afk-kick"]){
				$this->api->ban->kick($iusername, "Auto AFK kick");
			}
		}
	}
	
	public function handler(&$data, $event){
		switch($event){
			case "player.join":
					$this->afk[$data->iusername] = false;
					$this->data[$data->iusername] = new Config(DATA_PATH."/plugins/Essentials/userdata/".$data->iusername.".yml", CONFIG_YAML, array(
						"ipAddress" => $data->ip,
						"mute" => false,
						"newbie" => true,
					));
				break;
			case "player.quit":
				if($this->data[$data->iusername] instanceof Config){
					$this->data[$data->iusername]->save();
				}
				unset($this->lastafk[$data->iusername]);
				unset($this->afk[$data->iusername]);
				break;
			case "player.respawn":
				$data->sendChat($this->getMessage("backAfterDeath"));
				break;
			case "player.teleport":
				$this->setData($data["player"], "lastlocation", array(
					"world" => $data["player"]->level->getName(),
					"x" => $data["player"]->entity->x,
					"y" => $data["player"]->entity->y,
					"z" => $data["player"]->entity->z,
				));
				break;
			case "player.chat":
				$message = str_replace(array("{DISPLAYNAME}", "{MESSAGE}", "{WORLDNAME}"), array($data["player"]->username, $data["message"], $data["player"]->level->getName()), $this->config["chat-format"]);
				$this->api->chat->broadcast($message);
				return false;
				break;
			case "player.move":
				if(EssentialsAPI::checkEssentialsLogin() and EssentialsAPI::checkLogin($data->player) === false){
					break;
				}
				$this->lastafk[$data->player->iusername] = time();
				if($this->afk[$data->player->iusername]){
					$this->afk[$data->player->iusername] = false;
					$this->api->chat->broadcast($this->getMessage("userIsNotAway", array($data->player->username, "", "", "")));
				}
				break;
			case "console.check":
				if(in_array($data["cmd"], $this->config["player-commands"]) or $this->api->ban->isOp($data["issuer"]->username)){
					return true;
				}
				break;
			case "console.command":
				if($data["issuer"] instanceof Player and $this->api->dhandle("get.player.permission", "") !== false){
					if(EssentialsAPI::checkEssentialsLogin() and EssentialsAPI::checkLogin($data["issuer"]) === false){
						break;
					}
					if(in_array($data["cmd"], $this->config["player-commands"]) or $this->api->ban->isOp($data["issuer"]->username)){
						return;
					}
					return false;
				}
				break;
			case "player.spawn":
				if($this->getData($data, "newbie")){
					switch($data->gamemode){
						case SURVIVAL:
							if(!array_key_exists($this->config["newbies"]["kit"], $this->config["kits"])){
								break;
							}
							$kits = $this->config["kits"][$this->config["newbies"]["kit"]];
							foreach($kits as $kit){
								$kit = explode(" ", $kit);
								$item = BlockAPI::fromString(array_shift($kit));
								$count = $kit[0];
								$data->addItem($item->getID(), $item->getMetadata(), $count);
							}
							break;
						case CREATIVE:
							break;
					}
					$data->sendChat(str_replace(array("{DISPLAYNAME}", "{WORLDNAME}"), array($data->username, $data->level->getName()), $this->config["newbies"]["message"]));
					$this->setData($data, "newbie", false);
				}else{
					$data->sendChat($this->motd);
				}
				if($data->gamemode === CREATIVE){
					$type = $this->api->ban->isOp($data->iusername) ? "op" : "default";
					$creative = $this->config["creative-item"][$type];
					foreach($creative as $item){
						$item = explode(" ", $item);
						$data->setSlot($item[0], BlockAPI::fromString($item[1]));
					}
				}
				break;
		}
	}
	
	public function getData(Player $player, $dataname){
		return $this->data[$player->iusername]->exists($dataname) ? $this->data[$player->iusername]->get($dataname) : false;
	}
	
	public function setData(Player $player, $dataname, $data){
		$this->data[$player->iusername]->set($dataname, $data);
	}
	
	public function delData(Player $player, $dataname){
		$this->data[$player->iusername]->remove($dataname);
	}
	
	public function defaultCommands($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "afk":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				$this->api->chat->broadcast($this->getMessage("userIsAway", array($issuer->username, "", "", "")));
				$this->afk[$issuer->iusername] = true;
				break;
			case "home":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				$homes = $this->getData($issuer, "home");
				if($homes === false){
					$output .= "You do not have a home.\n";
					break;
				}
				if($params[0] == ""){
					$output = "Homes: ";
					foreach($homes as $home => $data){
						$output .= "$home, ";
					}
					break;
				}
				if($homes[$params[0]]["world"] !== $issuer->level->getName()){
					$this->api->player->teleport($issuer->iusername, "w:".$homes[$params[0]]["world"]);
				}
				$this->api->player->tppos($issuer->iusername, $homes[$params[0]]["x"], $homes[$params[0]]["y"], $homes[$params[0]]["z"]);
				break;
			case "sethome":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				if($params[0] == ""){
					$output .= "Usage: /$cmd <name>\n";
					break;
				}
				$homes = $this->getData($issuer, "home");
				$homes[$params[0]] = array(
					"world" => $issuer->level->getName(),
					"x" => $issuer->entity->x,
					"y" => $issuer->entity->y,
					"z" => $issuer->entity->z
				);
				$this->setData($issuer, "home", $homes);
				$output .= "Your home has been saved.\n";
				break;
			case "delhome":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				if($params[0] == ""){
					$output .= "Usage: /$cmd <name>\n";
					break;
				}
				$homes = $this->getData($issuer, "home");
				unset($homes[$params[0]]);
				$this->setData($issuer, "home", $homes);
				$output .= "Your home has been deleted.\n";
				break;
			case "back":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				$pos = $this->getData($issuer, "lastlocation");
				if($pos !== false){
					$name = $issuer->iusername;
					if($$pos["world"] !== $issuer->level->getName()){
						$this->api->player->teleport($name, "w:".$$pos["world"]);
					}
					$this->api->player->tppos($name, $$pos["x"], $$pos["y"], $$pos["z"]);
					$output .= $this->getMessage("backUsageMsg");
				}
				break;
			case "tree":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				switch(strtolower($params[0])){
					case "redwood":
						$meta = 1;
						break;
					case "brich":
						$meta = 2;
						break;
					case "tree":
						$meta = 0;
						break;
					default:
						$output .= "Usage: /$cmd <tree|brich|redwood>\n";
						break 2;
				}
				TreeObject::growTree($issuer->level, new Vector3 (((int)$issuer->entity->x), ((int)$issuer->entity->y), ((int)$issuer->entity->z)), new Random(), $meta);
				$output .= $this->getMessage("treeSpawned");
				break;
			case "setspawn":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				$pos = new Vector3(((int)$issuer->entity->x + 0.5), ((int)$issuer->entity->y), ((int)$issuer->entity->z + 0.5));
				$output .= "Spawn location set.\n";
				$issuer->level->setSpawn($pos);
				break;
			case "mute":
				if($params[0] == ""){
					$output .= "Usage: /$cmd <player>\n";
					break;
				}
				$target = $this->api->player->get($params[0]);
				if($target === false){
					$output .= $this->getMessage("playerNotFound");
					break;
				}
				if($this->getData($target, "mute") === false){
					$output .= "Player ".$target->username." muted.\n";
					$target->sendChat($this->getMessage("playerMuted"));
					$this->setData($issuer, "mute", true);
				}else{
					$output .= "Player ".$target->username." unmuted.\n";
					$target->sendChat($this->getMessage("playerUnmuted"));
					$this->setData($issuer, "mute", false);
				}
				break;
			case "burn":
				if($params[0] == "" or $params[1] == ""){
					$output .= "Usage: /$cmd <player> <seconds>\n";
					break;
				}
				$seconds = (int)$params[1];
				$target = $this->api->player->get($params[0]);
				if($target === false){
					$output .= $this->getMessage("playerNotFound");
					break;
				}
				$target->entity->fire = $seconds * 20;
				$target->entity->updateMetadata();
				$output .= $this->getMessage("burnMsg", array($target->username, $seconds, "", ""));
				break;
			case "kickall":
				$reason = "";
				if($params[0] != ""){
					$reason = $params[0];
				}
				foreach($this->api->player->online() as $username){
					$this->api->ban->kick($username, $reason);
				}
				break;
			case "killall":
				$reason = "";
				if($params[0] != ""){
					$reason = $params[0];
				}
				foreach($this->api->player->online() as $username){
					$target = $this->api->player->get($username);
					$this->api->entity->harm($target->eid, 3000, $reason);
				}
				break;
			case "heal":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				$target = $issuer;
				if($params[0] != ""){
					$target = $this->api->player->get($params[0]);
					if($target === false){
						$output .= $this->getMessage("playerNotFound");
						break;
					}
				}
				$this->api->entity->heal($target->eid, 20);
				break;
			case "clearinventory":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				$target = $issuer;
				if($params[0] != ""){
					$target = $this->api->player->get($params[0]);
					if($target === false){
						$output .= $this->getMessage("playerNotFound");
						break;
					}
				}
				if($target->gamemode === CREATIVE){
					$output .= "Player is in creative mode.\n";
					break;
				}
				if($params[1] != ""){
					$item = BlockAPI::fromString($params[1]);
				}
				foreach($issuer->inventory as $slot => $data){
					if(isset($item) and $item->getID() !== $data->getID()){
						continue;
					}
					$issuer->setSlot($slot, BlockAPI::getItem(AIR, 0, 0));
				}
				$output .= $params[0] == "" ? $this->getMessage("inventoryCleared") : $this->getMessage("inventoryClearedOthers", array($target->username, "", "", ""));
				break;
		}
		return $output;
	}
	
	public function getMessage($msg, $params = array("%1", "%2", "%3", "%4")){
		$msgs = array_merge($this->lang->get("Default"), $this->lang->get("Essentials"), $this->lang->get("Login"), $this->lang->get("Protect"));
		if(!isset($msgs[$msg])){
			$msgs[$msg] = "Undefined message: $msg";
		}
		return str_replace(array("%1", "%2", "%3", "%4"), array($params[0], $params[1], $params[2], $params[3]), $msgs[$msg])."\n";
	}
}

class EssentialsAPI{
	private static $essentials, $essentialsLogin, $essentialsProtect;
	public static function setEssentials(Essentials $plugin){
		if(EssentialsAPI::$essentials instanceof Essentials){
			return false;
		}
		EssentialsAPI::$essentials = $plugin;
	}
	
	public static function setEssentialsLogin(EssentialsLogin $plugin){
		if(EssentialsAPI::$essentialsLogin instanceof EssentialsLogin){
			return false;
		}
		EssentialsAPI::$essentialsLogin = $plugin;
	}
	
	public static function setEssentialsProtect(EssentialsProtect $plugin){
		if(EssentialsAPI::$essentialsProtect instanceof EssentialsProtect){
			return false;
		}
		EssentialsAPI::$essentialsProtect = $plugin;
	}
	
	public static function getPlayerData(Player $player, $dataname){
		if(in_array($dataname, array("home", "lastlocation", "mute", "newbie"))) return EssentialsAPI::$essentials->getData($player, $dataname);
		if(in_array($dataname, array("status", "registered", "lastconnected", "forget"))) return EssentialsAPI::$essentialsLogin->getData($player, $dataname);
		return false;
	}
	
	public static function getMessage($msg, $params = array("%1", "%2", "%3", "%4")){
		return EssentialsAPI::$essentials->getMessage($msg, $params);
	}
	
	public static function checkEssentialsLogin(){
		if(!(EssentialsAPI::$essentialsLogin instanceof EssentialsLogin)) return false;
		return true;
	}
	
	public static function checkLogin(Player $player){
		return EssentialsAPI::$essentialsLogin->getData($player, "status") == "login" ? true : false;
	}
}