<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\Commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use ParadoxUHC\UHC;

class MainLanguageCommand extends BaseCommand {
    private $plugin;
    public $config;
    public $player;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "language", "This allows players to set their language.", "/language [language]", ["lng", "lang", "languagee"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
           $this->player = new Config($this->plugin->getDataFolder()."players/".strtolower($sender->getName()).".yml");
           $language = $this->player->get("language");
           if(isset($args[0])){
               switch(strtolower($args[0])){
                    case "english":
                    case "eng":
                    case "ingles":
                    case "inglés":
                        if($language == "english"){
                            $sender->sendMessage(TF::RED."You already have English set as your language!");
                            return false;
                        }
                        $this->player->set("language", "english");
                        $this->player->save();
                        $sender->sendMessage(TF::GREEN."You have successfully set your language to ENGLISH.");
                        break;
                    case "spanish":
                    case "espanol":
                    case "español":
                        if($language == "spanish"){
                            $sender->sendMessage(TF::RED."Ya tiene conjunto español como su idioma!");
                            return false;
                        }
                        $this->player->set("language", "spanish");
                        $this->player->save();
                        $sender->sendMessage(TF::GREEN." Ha configurado con éxito su idioma a Español.");
                    default:
                        if($this->plugin->getLanguage($sender) == "spanish"){
                            $sender->sendMessage(TF::GOLD."---- Idiomas Disponibles ----");
                            $sender->sendMessage(TF::GOLD."Español");
                            $sender->sendMessage(TF::GOLD."Inglés");
                            $sender->sendMessage(TF::GOLD."------------------------------");
                        }
                        if($this->plugin->getLanguage($sender) == "english"){
                            $sender->sendMessage(TF::GOLD."---- Available Languages ----");
                            $sender->sendMessage(TF::GOLD."English");
                            $sender->sendMessage(TF::GOLD."Spanish");
                            $sender->sendMessage(TF::GOLD."------------------------------");
                        }
                        
               }
           }
           else {
               $sender->sendMessage(TF::RED."Usage: /language [language]");
           }
        }   
    }

    /**
     * @return mixed
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}