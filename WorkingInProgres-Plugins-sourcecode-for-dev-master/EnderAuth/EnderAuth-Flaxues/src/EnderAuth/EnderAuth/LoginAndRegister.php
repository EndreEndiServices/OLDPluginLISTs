<?php

namespace EnderAuth\EnderAuth;

use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
/*
- Logins/Registers players.
- Main core functions are done here.
*/
class LoginAndRegister implements Listener{
	public function __construct(Loader $plugin){
        $this->plugin = $plugin;
        $this->messageLoggedIn = $this->plugin->getConfig()->get("logged");
        $this->messageJoin = $this->plugin->getConfig()->get("join");
        $this->messageIP = $this->plugin->getConfig()->get("ipauthed");
        $this->messagePleaseRegistered = $this->plugin->getConfig()->get("please-registered");
        $this->messageAlreadyRegistered = $this->plugin->getConfig()->get("already-registered");
        $this->messageIncorrect = $this->plugin->getConfig()->get("incorrect");
        $this->messageKick = $this->plugin->getConfig()->get("kicked");
        $this->messageLogin = $this->plugin->getConfig()->get("login");
        $this->messageSimple = $this->plugin->getConfig()->get("simple");
        $this->messageShort = $this->plugin->getConfig()->get("short");
        $this->messageLong = $this->plugin->getConfig()->get("long");
        $this->messageError = $this->plugin->getConfig()->get("error");
        $this->messageDisabled = $this->plugin->getConfig()->get("disable");
        $this->messageRegistered = $this->plugin->getConfig()->get("registered");
        $this->messageWanted = $this->plugin->getConfig()->get("wanted");
        $this->messageNoSuccess = $this->plugin->getConfig()->get("no-success");
        
    }
    public function onQuit(PlayerQuitEvent $event){
    	$this->clearSession($event->getPlayer());
    }
    public function onKick(PlayerKickEvent $event){
    	if($event->getPlayer() === null){ //If a plugin stoped this event like VIPSlots.
    		$this->clearSession($event->getPlayer());
    	}
    }
    public function onJoin(PlayerJoinEvent $event){
    	//Messages
    	if($this->plugin->status === "enabled"){
    		$event->getPlayer()->sendMessage("[EnderAuth] This server is protected by EnderAuth.");
    		$event->getPlayer()->sendMessage($this->messageJoin);
    	}
        if($this->plugin->usernamestatus === true){
            $name = $event->getPlayer()->getName();
            $event->getPlayer()->setNameTag("[Processing..] $name");
        }
    	if($this->plugin->safemode && $this->plugin->status !== "enabled"){
    		$event->getPlayer()->sendMessage($this->messageDisabled);
    	}
    	if($this->plugin->status === "enabled"){
    		if($this->plugin->provider === "yml"){
    			if($this->plugin->registered->exists(strtolower($event->getPlayer()->getName()))){
    				if($this->plugin->ipAuth){
    					if($myuser->get("ip") === $event->getPlayer()->getAddress()){
    						$this->plugin->loginmanager[$event->getPlayer()->getId()] = true;
                    				$this->plugin->chatprotection[$event->getPlayer()->getId()] = $myuser->get("password");
                    				$event->getPlayer()->setNameTag("$name");
                    				$event->getPlayer()->sendMessage($this->messageIP);
    						$event->getPlayer()->sendMessage($this->messageLoggedIn);
    						return;
    					}
    				}
    				$event->getPlayer()->sendMessage($this->messageAlreadyRegistered);
    				$event->getPlayer()->sendMessage($this->messageLogin);
                    		$event->getPlayer()->setNameTag("[Not-Logged-In] $name");
    				$this->plugin->loginmanager[$event->getPlayer()->getId()] = 1;
    			}
    			else{
    				$event->getPlayer()->sendMessage($this->messagePleaseRegistered);
                    		$event->getPlayer()->sendMessage($this->messageWanted);
                    		$event->getPlayer()->setNameTag("[Not-Registered] $name");
    				$this->plugin->loginmanager[$event->getPlayer()->getId()] = 0;
    			}
    		}
    	}
    }
    public function onChat(PlayerChatEvent $event){
    	$message = $event->getMessage();
    	if($this->plugin->loginmanager[$event->getPlayer()->getId()] === 1){
    		if($this->plugin->provider === "yml"){
    			$myuser = new Config($this->plugin->getDataFolder() . "players/" . strtolower($event->getPlayer()->getName() . ".yml"), Config::YAML);
    			if(md5($message) === $myuser->get("password")){
    				$this->plugin->loginmanager[$event->getPlayer()->getId()] = true;
                    $this->plugin->chatprotection[$event->getPlayer()->getId()] = md5($message);
                    $event->getPlayer()->setNameTag("$name");
    				$event->getPlayer()->sendMessage($this->messageLoggedIn);
    			}
    			else{
    				$event->getPlayer()->sendMessage($this->messageIncorrect);
    				$this->protectForces($event->getPlayer());
    			}
    		}
    		return;
    	}
    	if($this->plugin->loginmanager[$event->getPlayer()->getId()] === 0 && !isset($this->plugin->chatprotection[$event->getPlayer()->getId()])){
    		if($this->plugin->provider === "yml"){
    			$this->plugin->chatprotection[$event->getPlayer()->getId()] = $this->proccessPassword($message, $event->getPlayer());
    			if(isset($this->plugin->chatprotection[$event->getPlayer()->getId()])){
    				$event->getPlayer()->sendMessage($this->plugin->getConfig()->get("success"));
    			}
    		}
    		return;
    	}
    	if($this->plugin->loginmanager[$event->getPlayer()->getId()] === 0 && isset($this->plugin->chatprotection[$event->getPlayer()->getId()])){
    		if(md5($message) === $this->plugin->chatprotection[$event->getPlayer()->getId()]){
    			$myuser = new Config($this->plugin->getDataFolder() . "players/" . strtolower($event->getPlayer()->getName() . ".yml"), Config::YAML);
    			$myuser->set("password", $this->plugin->chatprotection[$event->getPlayer()->getId()]);
    			$myuser->save();
                $this->plugin->registered->set(strtolower($event->getPlayer()->getName()));
                $this->plugin->registered->save();
    			if($myuser->get("password") === md5($message)){
    				$event->getPlayer()->sendMessage($this->messageRegistered);
    				$this->plugin->loginmanager[$event->getPlayer()->getId()] = true;
    			}
    			else{
    				$event->getPlayer()->sendMessage($this->messageError); //This should not happen anyways.
    			}
    		}
    		else{
    			$event->getPlayer()->sendMessage($this->messageNoSuccess);
    			unset($this->plugin->chatprotection[$event->getPlayer()->getId()]);
    		}
        }
    }
    private function proccessPassword($password, $player){
    	if(strlen($password) < $this->plugin->short){
    		$player->sendMessage($this->messageShort);
    		return;
    	}
    	if(strlen($password) > $this->plugin->max){
    		$player->sendMessage($this->messageLong);
    		return;
    	}
    	$simplepass = strtolower($password);
    	if($this->plugin->simplepassword === true && $this->plugin->status === "enabled"){
    		if($simplepass === 123456789 || $simplepass === 987654321 || $simplepass === "qwerty" || $simplepass === "asdfg" || $simplepass === "password"){
    			$player->sendMessage($this->messageSimple);
    			unset($this->plugin->chatprotection[$player->getId()]);
    			return;
    		}
    	}
    	$myuser = new Config($this->plugin->getDataFolder() . "players/" . strtolower($player->getName() . ".yml"), Config::YAML);
    	$myuser->set("password", md5($password));
    	if($this->plugin->ipAuth){
    		$myuser->set("ip", $player->getAddress());
    	}
    	$myuser->set("version", $this->plugin->version); //For compatibility in later updates.
    	$myuser->save();
    	return md5($password);
    }
    public function clearSession($player){
    	/*
    	- This function protects memory leaks, use it when a player leaves the game.
    	*/
    	unset($this->plugin->loginmanager[$player->getId()]);
    	unset($this->plugin->kicklogger[$player->getId()]);
    	unset($this->plugin->playerticks[$player->getId()]);
    	unset($this->plugin->chatprotection[$player->getId()]);
    	
    }
    private function protectForces($player){
    	if($this->protectForce){
    		if(!isset($this->plugin->kicklogger[$player->getId()])){ //Start protection.
    			$this->plugin->kicklogger[$player->getId()] = 1;
    		}
    		else{
    			$currentAttempts = $this->plugin->kicklogger[$player->getId()];
    			$currentAttempts++;
    			if($currentAttempts > $this->plugin->maxAttempts){
    				$player->kick($this->messageKick);
    				$this->clearSession($player);
    				return;
    			}
    			$this->plugin->kicklogger[$player->getId()] = $currentAttempts;
    			return $currentAttempts;
    		}
    	}
    }
}
