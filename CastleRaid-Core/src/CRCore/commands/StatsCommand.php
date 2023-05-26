<?php

declare(strict_types=1);

namespace CRCore\commands;

use CRCore\core\Loader;
use CRCore\commands\BaseCommand;
use onebone\economyapi\EconomyAPI;
use JackMD\KDR;
use pocketmine\command\{
    Command, CommandSender, PluginCommand
};
use pocketmine\level\{
    Level, Position
};
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

class StatsCommand extends BaseCommand{
    
            public function __construct(Loader $plugin) {
        parent::__construct($plugin , "stats", "show your stats", "/stats", ["stats"]);
        $this->plugin = $plugin;


    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
                    if($sender instanceof Player){
        $faction = $this->plugin->getServer()->getPluginManager()->getPlugin("FactionsPro")->getPlayerFaction($sender->getName());
        $kdrplugin = $this->plugin->getServer ()->getPluginManager()->getPlugin("KDR")->getProvider();
        $kills = $kdrplugin->getPlayerKillPoints($sender);
        $deaths = $kdrplugin->getPlayerDeathPoints($sender);
        $money = EconomyAPI::getInstance()->myMoney($sender);
        $ping = $sender->getPing();
        $online = count($this->plugin->getServer()->getOnlinePlayers());
        $ip = $sender->getAddress();
        $name = $sender->getDisplayName();
        $sender->sendMessage("§f--- §6Your Stats §f---\n§6- Name: §f$name\n§6- Faction: §f$faction\n§6- Kills: §f$kills\n§6- Deaths: §f$deaths\n§6- Money: §f$money\n§6- Ping: §f$ping \n§6- IP: §f$ip \n§6- Onine Players: §f$online");
        }else{
          $sender->sendMessage(C::RED . "You are not In-Game.");
        }
            return true;
    }
}
