<?php
namespace PrisonCore\Commands;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use PrisonCore\Core;
use PrisonCore\BasicLib;

class fly extends Command{
	
	public function __construct(Core $plugin){
	    $this->plugin = $plugin;
	    $this->prefix = $this->plugin->prefix;
	    parent::__construct("fly", "Turn on or off flight!");
		}
	public function getCore(){
	    return $this->plugin;
		}
	public function execute(CommandSender $sender, $label, array $args){
	     if(!$sender instanceof Player){
		    $sender->sendMessage($this->prefix."§r§c Run this command on game!");
		    return true;
		   }
		 if($sender->hasPermission("fly.use")){
			if(isset($args[0])){
			  switch($args[0]){
			     case "on":
			      if(!$sender->isFlying()){
				    $sender->setAllowFlight(true);
				    $sender->sendMessage($this->prefix." §r§aSuccessfully enabled flight!");
				    //$this->getCore()->flying[strtolower($sender->getName())] = 1;
				   return true;
				  }else{
				    $sender->sendMessage($this->prefix." §r§cYou are already flying!");
					}
				  break;
				  case "off":
				     BasicLib::disableFlight($sender);
				     $sender->sendMessage($this->prefix."§r§e You have disabled the flight");
				     //unset($this->getCore()->flying[strtolower($sender->getName())]);
				   return true;
				   }
				}else{
				  $sender->sendMessage($this->prefix."§r§c Usage:§7 /fly <on/off>");
					}
			}else{
				 $sender->sendMessage($this->prefix."§r§c You don't have permission to fly!");
			}
    }
}