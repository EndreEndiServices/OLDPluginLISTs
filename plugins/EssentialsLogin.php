<?php

/*
__PocketMine Plugin__
name=EssentialsLogin
description=EssentialsLogin
version=2.1
author=KsyMC
class=EssentialsLogin
apiversion=10
*/

class EssentialsLogin implements Plugin{
	private $api, $config, $path, $data;
	
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
		$this->data = array();
		EssentialsAPI::setEssentialsLogin($this);
	}
	
	public function init(){
		$this->api->event("server.close", array($this, "handler"));
		$this->api->addHandler("player.join", array($this, "handler"), 50);
		$this->api->addHandler("player.chat", array($this, "handler"), 50);
		$this->api->addHandler("player.spawn", array($this, "handler"), 5);
		$this->api->addHandler("player.respawn", array($this, "handler"), 50);
		$this->api->addHandler("console.check", array($this, "handler"), 50);
		$this->api->addHandler("console.command", array($this, "handler"), 50);
		
		$this->api->console->register("logout", "", array($this, "commandHandler"));
		$this->api->console->register("changepassword", "<oldpassword> <newpassword>", array($this, "commandHandler"));
		$this->api->console->register("unregister", "<password>", array($this, "commandHandler"));
		$this->readConfig();
		
		console("[INFO] EssentialsLogin enabled!");
		$this->api->schedule(20, array($this, "checkTimer"), array(), true);
	}
	
	public function __destruct(){}
	
	public function readConfig(){
		$this->path = DATA_PATH."/plugins/Essentials/";
		$this->config = $this->api->plugin->readYAML($this->path."config.yml");
		if(file_exists($this->path."Logindata.dat")){
			$this->data = unserialize(file_get_contents($this->path."Logindata.dat"));
		}
	}
	
	public function checkTimer(){
		foreach($this->data as $iusername => $data){
			$player = $this->api->player->get($iusername);
			if($player === false) continue;
			
			if($this->getData($player, "status") == "logout"){
				if((time() - $this->getData($player, "lastconnected")) >= $this->config["login"]["timeout"]){
					$this->api->ban->kick($iusername, EssentialsAPI::getMessage("timeout"));
				}
				if($this->getData($player, "registered") === false){
					$player->sendChat("You must register using /register <password>");
				}else{
					$player->sendChat("You must authenticate using /login <password>");
				}
			}
		}
	}
	
	public function logout(Player $player){
		$this->data[$player->iusername]["spawnpos"] = array(
			$player->entity->x,
			$player->entity->y,
			$player->entity->z
		);
		foreach($player->inventory as $slot => $item){
			$this->data[$player->iusername]["inventory"][$slot] = $player->getSlot($slot);
			$player->setSlot($slot, BlockAPI::getItem(AIR, 0, 0));
		}
		$username = $player->username;
		$spawn = $player->level->getSpawn();
		$this->api->player->tppos($username, $spawn->x, $spawn->y, $spawn->z);
		if(!$this->config["login"]["allow-move"]){
			$player->blocked = true;
		}
	}
	
	public function handler($data, $event){
		switch($event){
			case "server.close":
				file_put_contents(DATA_PATH."/plugins/Essentials/Logindata.dat", serialize(array("password" => $this->data["password"], "registered" => $this->data["registered"])));
				break;
			case "player.join":
				$this->setData($data, "lastconnected", time());
				if($this->getData($data, "password") === false){
					$this->setData($data, "registered", false);
				}
				$this->setData($data, "status", "logout");
				$this->setData($data, "forget", 0);
				break;
			case "player.spawn":
				$this->api->schedule(35, array($this, "logout"), $data);
				break;
			case "player.respawn";
				if($this->getData($data, "status") == "logout") $data->blocked = true;
				break;
			case "console.check":
				if($this->getData($data["issuer"], "status") == "logout" and in_array($data["cmd"], $this->config["login"]["allow-commands"])){
					return true;
				}
				break;
			case "console.command":
				if($data["issuer"] instanceof Player and $this->getData($data["issuer"], "status") == "logout"){
					if($data["cmd"] == "register" and $this->config["login"]["allow-register"]){
						if($data["parameters"][0] == "" or $data["parameters"][1] == ""){
							$data["issuer"]->sendChat("Usage: /register <password> <password>");
							return true;
						}
						if($data["parameters"][0] !== $data["parameters"][1]){
							$data["issuer"]->sendChat(EssentialsAPI::getMessage("enterPasswordAgain"));
							return true;
						}
						if($this->register($data["issuer"], $data["parameters"][0]) === false){
							return false;
						}
						return true;
					}
					if($data["cmd"] == "login"){
						if($data["parameters"][0] == ""){
							$data["issuer"]->sendChat("Usage: /login <password>");
							return true;
						}
						if($this->login($data["issuer"], $data["parameters"][0]) === false){
							return false;
						}
						return true;
					}
					if(in_array($data["cmd"], $this->config["login"]["allow-commands"])){
						return;
					}
					return false;
				}
				break;
			case "player.chat":
				if($this->getData($data["player"], "status") == "logout" and !$this->config["login"]["allow-chat"]){
					return false;
				}
				break;
		}
	}
	
	public function getData(Player $player, $dataname){
		return isset($this->data[$dataname][$player->iusername]) ? $this->data[$dataname][$player->iusername] : false;
	}
	
	public function setData(Player $player, $dataname, $data){
		$this->data[$dataname][$player->iusername] = $data;
	}
	
	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "logout":
				if(!($issuer instanceof Player)){					
					$output .= "Please run this command in-game.\n";
					break;
				}
				if($this->getData($issuer, "status") == "logout"){
					$output .= EssentialsAPI::getMessage("notLogged");
					break;
				}
				$this->logout($issuer);
				
				$output .= EssentialsAPI::getMessage("logout");
				$this->api->handle("essentials.player.logout", $issuer);
				$this->setData($issuer, "lastconnected", time());
				$this->setData($issuer, "status", "logout");
				break;
			case "changepassword":
				if(!($issuer instanceof Player)){					
					$output .= "Please run this command in-game.\n";
					break;
				}
				if($params[0] == "" or $params[1] == ""){
					$output .= "Usage: /changepassword <oldpassword> <newpassword>\n";
					break;
				}
				$oldpassword = $params[0];
				$newpassword = $params[1];
				if($this->data["registered"][$issuer->iusername] === false){
					$output .= EssentialsAPI::getMessage("notRegistered");
					break;
				}
				if($this->getData($issuer, "status")){
					$output .= EssentialsAPI::getMessage("notLogged");
					break;
				}
				$realpassword = $this->data["password"][$issuer->iusername];
				if(!$this->checkPassword($oldpassword, $realpassword)){
					$output .= EssentialsAPI::getMessage("enterPasswordAgain");
				}
				$this->setPlayerPassword($issuer, $newpassword);
				
				$output .= EssentialsAPI::getMessage("changepassword");
				break;
			case "unregister":
				if(!($issuer instanceof Player)){					
					$output .= "Please run this command in-game.\n";
					break;
				}
				if($params[0] == ""){
					$output .= "Usage: /unregister <password>\n";
					break;
				}
				$password = $params[0];
				if($this->data["registered"][$issuer->iusername] === false){
					$output .= EssentialsAPI::getMessage("notRegistered");
					break;
				}
				$realpassword = $this->data["password"][$issuer->iusername];
				if(!$this->checkPassword($password, $realpassword)){
					$output .= EssentialsAPI::getMessage("notPasswordMatch", array($this->getData($issuer, "forget"), $this->config["login"]["kick-on-wrong-password"]["count"], "", ""));
					break;
				}
				$this->setPlayerPassword($issuer, false, true);
				$this->logout($issuer);
				
				$output .= EssentialsAPI::getMessage("unregister");
				$this->setData($issuer, "status", "logout");
				break;
		}
		return $output;
	}
	
	public function login(Player $player, $password){
		if($this->getData($player, "registered") === false){
			$player->sendChat(EssentialsAPI::getMessage("notRegistered"));
			return false;
		}
		$realpassword = $this->getData($player, "password");
		if(!$this->checkPassword($password, $realpassword)){
			if($this->config["login"]["login-kick"] > 0){
				$player->sendChat(EssentialsAPI::getMessage("notPasswordMatch", array($this->getData($player, "forget"), $this->config["login"]["login-kick"], "", "")));
				if($this->getData($player, "forget") >= $this->config["login"]["login-kick"]){
					$this->api->ban->kick($player, EssentialsAPI::getMessage("notPasswordMatch"));
					return false;
				}
			}else{
				$player->sendChat(EssentialsAPI::getMessage("notPasswordMatch"));
			}
			$this->setData($player, "forget", $this->getData($player, "forget") + 1);
			return false;
		}
		$player->blocked = false;
		$this->setData($player, "forget", 0);
		
		foreach($this->data[$player->iusername]["inventory"] as $slot => $item){
			$player->setSlot($slot, $item);
		}
		$pos = $this->data[$player->iusername]["spawnpos"];
		$username = $player->username;
		$this->api->player->tppos($username, $pos[0], $pos[1], $pos[2]);
		
		$player->sendChat(EssentialsAPI::getMessage("login"));
		$this->api->handle("essentials.player.login", $player);
		$this->setData($player, "status", "login");
		return true;
	}
	
	public function register(Player $player, $password){
		if(strlen($password) < $this->config["login"]["pw-higher-then"] or strlen($password) > $this->config["login"]["pw-less-then"]){
			$player->sendChat(EssentialsAPI::getMessage("passwordIncorrect", array($this->config["login"]["pw-higher-then"], $this->config["login"]["pw-less-then"], "", "")));
			return false;
		}
		if($this->getData($player, "registered") === true){
			$player->sendChat(EssentialsAPI::getMessage("alreadyRegistered"));
			return false;
		}
		$this->setPlayerPassword($player, $password);
		
		$player->sendChat(EssentialsAPI::getMessage("register"));
		$this->api->handle("essentials.player.register", $player);
		return true;
	}
	
	public function setPlayerPassword(Player $player, $password, $remove = false){
		if($remove === false){
			$this->data["password"][$player->iusername] = hash("sha256", $password);
			$this->data["registered"][$player->iusername] = true;
		}else{
			unset($this->data["password"][$player->iusername]);
			$this->data["registered"][$player->iusername] = false;
		}
	}
	
	public function checkPassword($password, $hash){
		if(hash("sha256", $password) === $hash){
			return true;
		}
		return false;
	}
}