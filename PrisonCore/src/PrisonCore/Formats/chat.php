<?php
namespace PrisonCore\Formats;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use PrisonCore\Core;

class chat implements Listener{
	 public function __construct(Core $plugin, $ranks){
	   $this->plugin = $plugin;
	   $this->ranks = $ranks->getAll();
	   $this->temp = $ranks;
		}
	 
	 /* @getPlayer Rank */
	
	 public function getRank($player){
	      $rank = $this->ranks[strtolower($player->getName())]["format"];
		   return $rank;
		}
		
	 /* @hasRank */
	
	 public function hasRank($player){
		$name = strtolower($player->getName());
	   if(isset($this->ranks[$name])){
		    return true;
		  }else{
		    return false;
			}
		}
		 
	/* @Translate colors 
	 */
	
	  public function translateColors($symbol, $message){
	    	$message = str_replace($symbol."0", C::BLACK, $message);
    		$message = str_replace($symbol."1", C::DARK_BLUE, $message);
	    	$message = str_replace($symbol."2", C::DARK_GREEN, $message);
	    	$message = str_replace($symbol."3", C::DARK_AQUA, $message);
	    	$message = str_replace($symbol."4", C::DARK_RED, $message);
	      	$message = str_replace($symbol."5", C::DARK_PURPLE, $message);
	    	$message = str_replace($symbol."6", C::GOLD, $message);
    		$message = str_replace($symbol."7", C::GRAY, $message);
	    	$message = str_replace($symbol."8", C::DARK_GRAY, $message);
    		$message = str_replace($symbol."9", C::BLUE, $message);
	    	$message = str_replace($symbol."a", C::GREEN, $message);
	    	$message = str_replace($symbol."b", C::AQUA, $message);
	    	$message = str_replace($symbol."c", C::RED, $message);
	    	$message = str_replace($symbol."d", C::LIGHT_PURPLE, $message);
	    	$message = str_replace($symbol."e", C::YELLOW, $message);
	    	$message = str_replace($symbol."f", C::WHITE, $message);
	    	$message = str_replace($symbol."k", C::OBFUSCATED, $message);
	    	$message = str_replace($symbol."l", C::BOLD, $message);
	    	$message = str_replace($symbol."m", C::STRIKETHROUGH, $message);
	    	$message = str_replace($symbol."n", C::UNDERLINE, $message);
    		$message = str_replace($symbol."o", C::ITALIC, $message);
	    	$message = str_replace($symbol."r", C::RESET, $message);
    	return $message;
	 }
	/* Chat formats
	  * @priority HIGH
	  * @BuiltIn
	  */
	
    public function onChat(PlayerChatEvent $event){
         $player = $event->getPlayer();
         $msg = $event->getMessage();
         $this->plugin->reloadConfig();
         if($this->hasRank($player)){
	        $format = $this->getRank($player);
	        $format = str_replace("{name}", $player->getName(), $format);
	        $format = str_replace("{msg}", $msg, $format);
	        $event->setFormat($this->translateColors("&", $format));
	        return true;
	    }
	  }
	}