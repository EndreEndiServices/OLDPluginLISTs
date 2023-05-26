<?php

namespace BeatsCore\Command;

use pocketmine\command\{Command, CommandSender, PluginIdentifiableCommand};
use pocketmine\plugin\Plugin;

use BeatsCore\Core;

class BaseCommand extends Command implements PluginIdentifiableCommand{

  private $plugin;
  
  public function __construct(Core $plugin, $name, $description, $usageMessage, $aliases){
    parent::__construct($name, $description, $usageMessage, $aliases);
    $this->plugin = $plugin;
  }

  public function getPlugin(): Plugin{
    return $this->plugin;
  }

  public function execute(CommandSender $sender, $commandLabel, array $args): bool{
    if($sender->hasPermission($this->getPermission())){
      $result = $this->onExecute($sender, $args);
      if(is_string($result)){
        $sender->sendMessage($result);
      }
      return true;
     }else{
      $sender->sendMessage("§l§8»§r §cYou don't have the permission to use this command!");
    }
    return false;
  }
}