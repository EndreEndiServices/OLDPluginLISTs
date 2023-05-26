<?php

namespace SAM\Commands;

use pocketmine\permission\Permission;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

use pocketmine\utils\TextFormat;

class Login implements CommandExecutor {

public function __construct(SAM $plugin){
        $this->plugin = $plugin;
    }
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    	$fcmd = strtolower($cmd->getName());
    	switch($fcmd){
    		case "login":
    			if($sender->hasPermission("sam.login")){
    				//Player Sender
    				if($sender instanceof Player){
    					$cfg = $this->plugin->getConfig()->getAll();
    					//Check if login is enabled
    					if($cfg["login"]["enabled"]){
    						//Check args
    						if(count($args) == 1){
    							$status = SAM::getAPI()->authenticatePlayer($sender, $args[0]);
    							if($status == SAM::SUCCESS){
    							$name->sendMessage("[SAM] Your password was correct, you may play now!
