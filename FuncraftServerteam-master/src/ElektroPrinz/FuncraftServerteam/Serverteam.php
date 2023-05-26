<?php

namespace ElektroPrinz\FuncraftServerteam;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Serverteam extends PluginBase {
    
    public function onLoad() {
        $this->getLogger()->info("Laden...");
    }
    
    public function onEnable() {
        $this->getLogger()->info("Aktiviert");
    }
    
    public function onDisable() {
        $this->getLogger()->info("Deaktiviert");
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if($command->getName() === "serverteam"){
            if (!isset($args[0])) {
                $sender->sendMessage("§c[FuncraftServerteam] Benutzung: /serverteam <list|info|help>");
                return true;
            }
            if($args[0] == "list") {
                $sender->sendMessage("§e-------§1Das Serverteam§e-------");
                $sender->sendMessage("§aOwner: ElektroPrinz");
                $sender->sendMessage("§6Helferlein: SrSkyMelon");
                $sender->sendMessage("§4Admin: Lilliiii2305");
                $sender->sendMessage("§1Dev-Leitung: Exodia203");
                $sender->sendMessage("§Developer: ElektroPrinz");
                $sender->sendMessage("§9Moderator: Wasserfell22, ExpandedData967");
                $sender->sendMessage ("§eSupporter: LoewexKai, einer wird gesucht");
                $sender->sendMessage("§bBuilder: FireFoxy96, CyberWolf Blue");

                $sender->sendMessage("§5---------------------------");
                return true;
                
                } elseif($args[0] == "info") {
                $sender->sendMessage("§e-------------------------------------");
                $sender->sendMessage("§ePlugin von ElektroPrinz");
                $sender->sendMessage("§bName: FuncraftServerteam");
                $sender->sendMessage("§bVersion: 1.5.2");
                $sender->sendMessage("§bFür PocketMine-API: 3.0.0 - 3.x.x");
                $sender->sendMessage("§6Permissions: fcrserverteam.cmd");
                $sender->sendMessage("§eSpeziell für Funcraft entwickelt");
                $sender->sendMessage("§e-------------------------------------");
                return true;
                
                } elseif($args[0] == "help") {
                $sender->sendMessage("§9---§aServerteam-Plugin§9---");
                $sender->sendMessage("§a/serverteam list §b-> Zeigt das Serverteam an");
                $sender->sendMessage("§6/serverteam info §b-> Zeigt Details über das Plugin");
                $sender->sendMessage("§6/serverteam help §b-> Zeigt dieses Hilfemenü an");
                return true;
                }
            }
        }
    }
