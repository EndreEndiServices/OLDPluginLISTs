<?php

declare(strict_types=1);

namespace BeatsCore\commands;

use BeatsCore\Core;
use BeatsCore\tasks\HUDTask;
use pocketmine\command\{
    Command, CommandSender, PluginCommand
};
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;

class HUDCommand extends PluginCommand{

    /** @var Core */
    private $plugin;

    public function __construct(Core $plugin){
        parent::__construct("hud", $plugin);
        $this->setDescription("Enable OR Disable your HUD!");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$sender instanceof Player) return false;
        if(!empty($args[0])){
            switch($args[0]){
                case "on":
                    if(isset($this->plugin->hud[$sender->getName()])) return false;
                    array_push($this->plugin->hud, $sender->getName());
                    $sender->sendMessage("§8§l(§a!§8)§r §7HUD Enabled!");
                    break;
                case "off":
                    if(!isset($this->plugin->hud[$sender->getName()])) return false;
                    unset($this->plugin->hud[$sender->getName()]);
                    $sender->sendMessage("§8§l(§a!§8)§r §7HUD Disabled!");
                    break;
            }
        }else{
            $sender->sendMessage("§8§l(§6!§8)§r §7Usage§8:§a /hud <on|off>");
            return false;
        }
        return true;
    }
}