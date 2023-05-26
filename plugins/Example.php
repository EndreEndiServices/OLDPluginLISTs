<?php

namespace MassiveEconomyExample;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

//MassiveEconomy API Call
use MassiveEconomy\MassiveEconomyAPI;

class Example extends PluginBase implements Listener{
	
	/* 
	 * This is an Example Plugin with MassiveEcomomyAPI implementation
	 * This plugin will pay player onJoin using MassiveEconomyAPI payPlayer function
	 */
    
    public function onEnable(){
    	if(MassiveEconomyAPI::getInstance()->getAPIVersion() == "0.90"){ //Checking API version. Important for API Functions Calls
    		$this->getLogger()->info(TextFormat::GREEN . "Example Plugin using MassiveEconomy (API v0.90)");
    	}else{
    		$this->getLogger()->alert(TextFormat::RED . "Plugin disabled. Please use MassiveEconomy (API v0.90)");
    		$this->getPluginLoader()->disablePlugin($this);
    	}
        $this->getServer()->getPluginManager()->registerEvents(new Example(), $this);
    }

    public function onJoin(PlayerJoinEvent $event){
    	$player = $event->getPlayer();
    	//Pay Player
    	if(MassiveEconomyAPI::getInstance()->payPlayer($player->getName(), 1)){ //Function return true: Success!
    		$player->sendMessage("Success! You received 1" . MassiveEconomyAPI::getInstance()->getMoneySymbol());
    	}else{ //Function return false: Player not registered to MassiveEconomy
    		$player->sendMessage("Payment Failed. You're not registered to MassiveEconomy");
    	}
    }
    
}
?>