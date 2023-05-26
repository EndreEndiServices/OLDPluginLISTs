<?php

/* 
CaptainKenji productions
 */

namespace kenchat;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this ,$this);
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        if($sender instanceof Player){
            $name = $sender->getName();
            if(strtolower($cmd->getName()) == 'clearchat'){
                if(count($args) < 1){
                    $name = $sender->getName();
              $this->getServer()->broadcastMessage(".\n.\n.\n.\n.\n.\n.\n.\n.\n.\n.\n.\n.\n.\n.\n§cAdministrator $name have cleared the chat");
              return;
                }else{
                    $sender->sendMessage("no args required");
                    return;
                }
            }
        }
    }
}
