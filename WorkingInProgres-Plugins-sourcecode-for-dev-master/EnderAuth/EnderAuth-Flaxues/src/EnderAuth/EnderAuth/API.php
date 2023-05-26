<?php

namespace EnderAuth\EnderAuth;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Server;
/*
- Here you can access some basic EnderAuth data so you can use it in your plugin.
- Open up an issue on the tracker if you think a function should be added.
*/
class API implements Listener{
	public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }
    
    #Returns the provider in lowercase, the result will always be mysql or yml.
    public function getProvider(){
      return $this->plugin->provider;
    }
    
    #Get EnderAuth, returns plugin.
    public function getEnderAuth(){
      return $this->plugin;
    }
    
    #Get EnderAuth version.
    public function getEnderAuthVersion(){
    	return $this->plugin->version;
    }
    
    #Returns a players ticks (If not logged in)
    public function getPlayerTick($player){
    	if($player !== null){
    		if(isset($this->plugin->playerticks[$player->getId()])){
    			return $this->plugin->playerticks[$player->getId()];
    		}
    		else{
    			return false;
    		}
    	}
    }
    
    #Get EnderAuth codename.
    public function getEnderAuthCodeName(){
    	return $this->plugin->codename;
    }
    
    #Gets a config option and returns it, returs false if denied.
    public function getEnderAuthConfigOption($option){
      $option = strtolower($option);
      if($option === "username" || $option === "port" || $option === "server" || $option === "password"){
      	$prefix = $this->plugin->prefix;
      	array_push($this->plugin->mainlogger, "$prefix Plugin tried to access protected data!");
      	return false; //Nice try, your not allowed to take these options.
      }
      if($option === "version"){
      	return $this->plugin->version; //Return the real version, plugin version can be edited.
      }
      $statement = $this->plugin->getConfig()->get($option);
      if($statement !== false && $statement !== true){
      	if($this->plugin->debug){
      		$prefix = $this->plugin->prefix;
      	 	array_push($this->plugin->mainlogger, "$prefix Plugin tried to get an invaild option!");
      	}
      	return false;
      }
      else{
      	return $statement;
      }
    }
    
    #Returns a true or false value depending on if a player has logged in.
    public function isAuthenticated($name){
      if($this->getPlayer($name) !== null){
      	$player = $this->getPlayer($name);
        if($this->plugin->loginmanager[$player->getId()] === ture){
          return true;
        }
        else{
          return false;
        }
      }
    }
    
    #Important! Always check the status on your plugins or EnderAuth may not function right.
    #Returns a true or false value.
    public function EnderAuthStatus(){
      if($this->plugin->status === "enabled"){
        return true;
      }
      else{
        return false;
      }
   }
   
   #Counts all logged-in players.
   public function countLoggedPlayers(){
   	$count = 0;
   	foreach($this->getServer()->getOfflinePlayers() as $p){
   		if($this->plugin->loginmanager[$p->getId()] === true){
   			$count++;
   		}
   	}
   	return $count;
   }
   
   #Counts all not logged-in players.
   public function countNotLoggedPlayers(){
   	$count = 0;
   	foreach($this->getServer()->getOfflinePlayers() as $p){
   		if($this->plugin->loginmanager[$p->getId()] === false){
   			$count++;
   		}
   	}
   	return $count;
   }
   
   #Returns true or false depending on if SafeMode is enabled.
   public function isSafeModeEnderAuth(){
   	return $this->owner->safemode;
   }
   
   #Registers a player.
   public function EnderAuthregisterPlayer($password, $player){
   	if($player !== null){
   		if(!$this->plugin->registered->exists($player->getName())){
   			$myuser->set("password", md5($password));
   			$myuser->set("ip", $this->getAddress()); 
   			$myuser->save();
   			$this->plugin->registered->set(strtolower($player->getName()));
   			$this->plugin->registered->save();
   			$player->sendMessage($this->plugin->getConfig()->get("registered"));
   		}
   		else{
   			if($this->debug){
   				array_push($this->plugin->mainlogger, "Cannot register offline players!"); //Don't want bad plugins to send inccorect data.
   			}
   			return false;
   		}
   	}
   }
   
   #Disables EnderAuth..Dangerous since auth will turn off, but safe-mode will force-fully kick in.
   #Returns false if already disabled, returns true if it has been disabled.
   public function disableEnderAuth(){
     if($this->plugin->status === "disabled"){
       return false; //If plugin is already disabled..
     }
     $this->plugin->safemode = true;
     $this->plugin->status = "disabled";
     if($this->plugin->status = "disabled"){
      return true;
     }
   }
}
  
