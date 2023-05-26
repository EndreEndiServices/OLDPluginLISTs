<?php

namespace Adrenaline\Commands;

use Adrenaline\CoreLoader;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class NickCommand extends BaseCommand{
    public function __construct(CoreLoader $plugin){
        parent::__construct($plugin, "nick", "Nickname a player", "/nick [set|remove|add] [player]", []);
    }

    public function execute(CommandSender $sender, $commandLabel, array $args){
        switch ($args[0]){
            case "remove":
                if($sender instanceof Player){
                    $sender->setNameTagVisible(false);
                    $sender->setDisplayName("HIDDEN");
                    $sender->sendMessage($this->getPlugin()->sendPrefix() . "Name has been removed!");
                }
                break;
            case "add":
                if($sender instanceof Player){
                    $sender->setDisplayName($sender->getName());
                    $sender->setNameTagVisible(true);
                    $sender->sendMessage($this->getPlugin()->sendPrefix() . "Your name has been restored!");
                }
                break;
            case "set":
                if($sender instanceof Player){

                }
        }
    }
}