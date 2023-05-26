<?php
namespace PrisonCore\Commands;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use PrisonCore\Core;
use PrisonCore\BasicLib;
use onebone\economyapi\EconomyAPI;
class factory extends Command{
	
	public function __construct(Core $plugin){
	    $this->plugin = $plugin;
	    $this->cfg = $plugin->cfg;
	    $this->prefix = $plugin->prefix;
	    $this->basecost = $this->cfg["factory.base.cost"];
	    parent::__construct("factory", "Factory main command!");
		}
	public function getCore(){
	    return $this->plugin;
		}
	public function execute(CommandSender $sender, $label, array $args){
	     if(!$sender instanceof Player){
		    $sender->sendMessage($this->prefix."§r§c Run this command on game!");
		    return true;
		   }
		 if(isset($args[0])){
			 switch($args[0]){
			    case "create":
				   if(!$this->getCore()->factoryExists($sender)){
					  if(EconomyAPI::getInstance()->mymoney($sender) >= 2000){
						EconomyAPI::getInstance()->reduceMoney($sender, 2000);
					   $this->getCore()->createFactory($sender);
					   $sender->sendMessage($this->prefix."§r§7 Successfully created a factory!");
				      $sender->sendMessage("§6Factory Level:§e ".$this->getCore()->getFactoryLevel($sender));
                   return true;
	 		    	 }else{
				       $sender->sendMessage($this->prefix."§r§c Not enough money to make a factory! You need ".$this->basecost."$");
				    	}
			      }else{
			        $sender->sendMessage($this->prefix."§r§c You already have a factoy!");
				  }
			   break;
			  case "levelup":
		      if($this->getCore()->factoryExists($sender)){
			     if(EconomyAPI::getInstance()->mymoney($sender) >= ($this->getCore()->getFactoryLevel($sender) * $this->basecost)){
				    if($this->getCore()->getFactoryLevel($sender) < $this->cfg["max.factory.level"]){
			         EconomyAPI::getInstance()->reduceMoney($sender, $this->getCore()->getFactoryLevel($sender) * $this->basecost);
			         $this->getCore()->setFactoryLevel($sender, 1);
			         $sender->sendMessage($this->prefix."§r§a Levelup proccess was successful!");
			         $sender->sendMessage("§6Factory Level: §e".$this->getCore()->getFactoryLevel($sender));
			        $sender->sendMessage("§7Total value spent:§e ".$this->getCore()->getFactoryLevel($sender) * $this->basecost."$");
			         return true;
			       }else{
			          $sender->sendMessage($this->prefix."§r§c Your factory at max level!");
				      }
                  }else{
				       $sender->sendMessage($this->prefix."§r§c Not enough money to levelup factory! You need ".($this->getCore()->getFactoryLevel($sender) * $this->basecost)."$");
				    	}
			         }else{
			            $sender->sendMessage($this->prefix."§r§c Do you have a factory?!");
				  }
				  break;
				  case "seelevel":
				    if(isset($args[1])){
					  $factory = $this->getCore()->getServer()->getPlayer($args[1]);
					  if($factory !== null){
					    if($this->getCore()->factoryExists($factory)){
						 $sender->sendMessage($this->prefix."§r§7 ".$factory->getName()."'s factory level:§e ".$this->getCore()->getFactoryLevel($factory));
					}else{
					  $sender->sendMessage($this->prefix."§r§c That player doesn't have a factory!!");
					}
				}else{
				  $sender->sendMessage($this->prefix."§r§c That player isn't online!");
				}
			}else{
			  $sender->sendMessage($this->prefix."§r§c You must specify a player!");
			}
				break;
			}
		}else{
		  $sender->sendMessage($this->prefix."§r§c Usage:§7 /factory <create/levelup/seelevel>");
		}
	}
}