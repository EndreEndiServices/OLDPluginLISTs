<?php
namespace PrisonCore\Commands;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\Config;
use PrisonCore\Core;

class setformat extends Command{
	  public function __construct(Core $plugin){
	       $this->plugin = $plugin;
	       parent::__construct("setformat", "Sets player formats!");
		}
	  public function getCore(){
	       return $this->plugin;
		}
	  public function execute(CommandSender $sender, $label, array $args){
	      if($sender->hasPermission("prison.formatter")){
	    	 if(isset($args[0])){
	    	   $player = $this->getCore()->getServer()->getPlayer($args[0]);
	    	   if(isset($args[1])){
			     if($player !== null){
	    		  $format = implode(" ", array_slice($args, 1));
	    		  $this->getCore()->setFormat($player, $format);
			     $this->getCore()->ranks->reload();
			     $sender->sendMessage("§7Format §e".$format." §7set for §6".$player->getName());
			  }else{
			    $sender->sendMessage("§cPlayer not found!");
				}
		   }else{
		    $sender->sendMessage("§cUsage: §7/setformat (player) <format>");
			}
       }else{
         $sender->sendMessage("§cUsage: §7/setformat (player) <format>");
	    }
	  }
   }
}