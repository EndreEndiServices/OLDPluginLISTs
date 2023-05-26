<?php 
  
 namespace src; 
  
 use pocketmine\command\Command; 
 use pocketmine\command\CommandSender; 
 use pocketmine\command\ConsoleCommandSender; 
 use pocketmine\plugin\PluginBase; 
 use pocketmine\utils\Config; 
 use pocketmine\utils\TextFormat; 
 use pocketmine\Player; 
  
 class Main extends PluginBase{ 
 	 
     public function onEnable(){ 
         $this->saveDefaultConfig(); 
     } 
  
     public function onCommand(CommandSender $sender, Command $command, $label, array $args){ 
         if(strtolower($command->getName()) == "bam"){ 
             if($sender instanceof Player){ 
             	if(!($sender->hasPermission("bambam") || $sender->hasPermission("bambam.command") || $sender->hasPermission("bambam.command.bam"))) { 
                     return false; 
     	    	} 
       	    	$chambers = $this->getConfig()->get("chambers"); 
             	if($chambers < 2){ 
         	    $sender->sendMessage(TextFormat::RED."You don't have enough chambers!"); 
         	    return true; 
       	    	} 
             	if(mt_rand(1, $chambers) == 1){ 
             	    $sender->setHealth(0); 
         	    $sender->sendMessage(TextFormat::RED."Unlucky."); 
       	    	}  
       	    	else{ 
                     $sender->sendMessage(TextFormat::GREEN."You survived."); 
                     foreach($this->getConfig()->get("commands") as $command){ 
                     	$this->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $sender->getName(), $command)); 
                     } 
             	} 
             }	 
             else{ 
             	$sender->sendMessage(TextFormat::RED."You can only play in-game!"); 
             } 
             return true; 
         } 
     } 
 } 
