<?php

namespace RTG\JDT;

/* Essentials */
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\network\protocol\TransferPacket;
use pocketmine\command\CommandExecutor;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;

class Loader extends PluginBase implements Listener {
    
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function onTransfer(Player $p, $ip, $port) {
        
        $pk = new \pocketmine\network\protocol\TransferPacket();
        $pk->address = $ip;
        $pk->port = (int) $port;
        $p->dataPacket($pk);
        $p->sendMessage("[Transfer] Executing...");
        $name = $p->getName();
        $this->getServer()->broadcastMessage(TF::GREEN . "[Server] Transfering " . TF::RED . "$name " . TF::GREEN . "to the server the player requested using JDTransfer!");
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        
        switch(strtolower($command->getName())) {
            
            case "transfer":
                
                if(isset($args[0])) {
                    switch($args[0]) {
                        
                        case "help":
                            
                            $sender->sendMessage(" -- JD Transfer 1.1 -- ");
                            $sender->sendMessage(" ");
                            $sender->sendMessage(TF::RED . "[Transfer] /transfer [ServerName]");
                            $sender->sendMessage(" ");
                            $sender->sendMessage(TF::YELLOW . " - Available Servers - ");
                            $sender->sendMessage(" ");
                            $sender->sendMessage(TF::RESET . " - JDC");
                            $sender->sendMessage(TF::RESET . " - BC");
                            $sender->sendMessage(TF::RESET . " - DC");
                            $sender->sendMessage(TF::RESET . " - IB");
                            $sender->sendMessage(TF::RESET . " - JDB");
                            $sender->sendMessage(TF::RESET . " - JDE");
                            $sender->sendMessage(TF::RESET . " - JDG");
                            $sender->sendMessage(TF::RESET . " - JDMulti");
                            $sender->sendMessage(TF::RESET . " - JDU");
                            $sender->sendMessage(TF::RESET . " - JDK");
                            $sender->sendMessage(" ");
                            $sender->sendMessage(TF::RED . "Please note that most of the servers are 'whitelisted' or some may not be online!. Please don't spam this feature!");
                            
                            return true;
                        break;
                        
                        case "JDC":
                            
                            $ip = "jdcraft.net";
                            $port = 19132;
                            $this->onTransfer($sender, $ip, $port);
                            
                            return true;
                        break;
                    
                        case "BC":
                            
                            $ip = "jdcraft.net";
                            $port = 20000;
                            $this->onTransfer($sender, $ip, $port);
                            
                            return true;
                        break;
                    
                        case "DC":
                            
                            $ip = "jdcaft.net";
                            $port = 41414;
                            $this->onTransfer($sender, $ip, $port);
                            
                            return true;
                        break;
                    
                        case "IB":
                            
                            $ip = "jdcraft.net";
                            $port = 25252;
                            $this->onTransfer($sender, $ip, $port);
                            
                            return true;
                        break;
                        
                        case "JDB":
                            
                            $ip = "jdcraft.net";
                            $port = 33333;
                            $this->onTransfer($sender, $ip, $port);
                            
                            return true;
                        break;
                        
                        case "JDE":
                            
                            $ip = "jdcraft.net";
                            $port = 19133;
                            $this->onTransfer($sender, $ip, $port);
                            
                            return true;
                        break;
                    
                        case "JDG":
                            
                            $ip = "jdcraft.net";
                            $port = 55555;
                            $this->onTransfer($sender, $ip, $port);
                            
                            return true;
                        break;
                    
                        case "JDMulti":
                            
                            $ip = "jdcraft.net";
                            $port = 17171;
                            $this->onTransfer($sender, $ip, $port);
                            
                            return true;
                        break;
                    
                        case "JDU":
                            
                            $ip = "jdcraft.net";
                            $port = 12321;
                            $this->onTransfer($sender, $ip, $port);
                            
                            return true;
                        break;
                    
                        case "JDK":
                            
                            $ip = "jdcraft.net";
                            $port = 11111;
                            $this->onTransfer($sender, $ip, $port);
                            
                            return true;
                        break;
                         
                    }
                       
                }
                else {
                    $sender->sendMessage("Usage: /transfer help");
                }
                
                return true;
            break;
   
        }
          
    }
    
    public function onDisable() {
        $this->getLogger()->warning("Turning off JDTransfer!");
    }
}