<?php

declare(strict_types=1);

namespace BeatsCore\commands;

use BeatsCore\Core;
use pocketmine\command\{
    Command, CommandSender, PluginCommand
};
use pocketmine\Player;

class NickCommand extends PluginCommand{

    /** @var Core */
    private $plugin;

    public function __construct($name, Core $plugin){
        parent::__construct($name, $plugin);
        $this->setDescription("Change your nickname");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$sender instanceof Player) return false;
        if($sender->hasPermission("beats.nick")){
            if(count($args) > 0){
                if($args[0] == "off"){
                    $sender->setDisplayName($sender->getName());
                    $sender->sendMessage("§l§8(§a!§8)§r §7You have §l§cDISABLED§r§7!");
                }else{
                    $sender->setDisplayName($args[0]);
                    $sender->sendMessage("§l§8(§a!§8)§r §7You have §l§aENABLED§r§7!\n§l§8(§a!§8)§r §7Your nick is now§8:§a " . $args[0]);
                }
            }else{
                $sender->sendMessage("§l§8(§a!§8)§r §l§cUsage§8:§r§7 /nick <name|off>");
                return false;
            }
        }else{
            $sender->sendMessage(Core::PERM_RANK);
            return false;
        }
        return true;
    }
}