<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\Commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use ParadoxUHC\UHC;

class MainStatsCommand extends BaseCommand {
    private $plugin;
    public $config;
    public $player;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "stats", "This tells players how many kills they have, how many wins they have, and more.", "/stats", ["stat", "statss", "statt"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                $player = $this->plugin->getServer()->getPlayer($args[0]);
                $name = $player->getName();
                $offlineplayer = $this->plugin->getServer()->getOfflinePlayer($args[0]);
                $oname = $offlineplayer->getName();
                if(!$player) {
                    $this->player = new Config($this->plugin->getDataFolder()."players/".strtolower($offlineplayer->getName()).".yml", Config::YAML);
                        $sender->sendMessage(TF::GRAY . "---$oname's Stats---");
                        $sender->sendMessage(TF::GRAY . 'Kills:' . TF::GOLD . $this->player->get("Kills"));
                        $sender->sendMessage(TF::GRAY . 'Deaths:' . TF::GOLD . $this->player->get("Deaths"));
                        $sender->sendMessage(TF::GRAY . 'UHCs:' . TF::GOLD . $this->player->get("UHCs"));
                        $sender->sendMessage(TF::GRAY . 'Wins:' . TF::GOLD . $this->player->get("Wins"));
                        $sender->sendMessage(TF::GRAY . 'Diamonds Mined:' . TF::GOLD . $this->player->get("Diamonds"));
                        $sender->sendMessage(TF::GRAY . 'Amount of Heads Eaten:' . TF::GOLD . $this->player->get("Heads"));
                        $sender->sendMessage(TF::GRAY . "----------------");
                }
                else {
                    $this->player = new Config($this->plugin->getDataFolder()."players/".strtolower($name).".yml", Config::YAML);
                    if(file_exists($this->plugin->getDataFolder()."players/".strtolower($name).".yml")) {
                        $sender->sendMessage(TF::GRAY . "---$name's Stats---");
                        $sender->sendMessage(TF::GRAY . 'Kills:' . TF::GOLD . $this->player->get("Kills"));
                        $sender->sendMessage(TF::GRAY . 'Deaths:' . TF::GOLD . $this->player->get("Deaths"));
                        $sender->sendMessage(TF::GRAY . 'UHCs:' . TF::GOLD . $this->player->get("UHCs"));
                        $sender->sendMessage(TF::GRAY . 'Wins:' . TF::GOLD . $this->player->get("Wins"));
                        $sender->sendMessage(TF::GRAY . 'Diamonds Mined:' . TF::GOLD . $this->player->get("Diamonds"));
                        $sender->sendMessage(TF::GRAY . 'Amount of Heads Eaten:' . TF::GOLD . $this->player->get("Heads"));
                        $sender->sendMessage(TF::GRAY . "----------------");
                    }
                    else {
                        $sender->sendMessage(TF::RED.'That player does not exist!');
                    }
                }
            } else {
                $this->player = new Config($this->plugin->getDataFolder() . "players/" . strtolower($sender->getName()) . ".yml");
                $sender->sendMessage(TF::GRAY . '---Your Stats---');
                $sender->sendMessage(TF::GRAY . 'Kills: ' . TF::GOLD . $this->player->get("Kills"));
                $sender->sendMessage(TF::GRAY . 'Deaths: ' . TF::GOLD . $this->player->get("Deaths"));
                $sender->sendMessage(TF::GRAY . 'UHCs: ' . TF::GOLD . $this->player->get("UHCs"));
                $sender->sendMessage(TF::GRAY . 'Wins: ' . TF::GOLD . $this->player->get("Wins"));
                $sender->sendMessage(TF::GRAY . 'Diamonds Mined: ' . TF::GOLD . $this->player->get("Diamonds"));
                $sender->sendMessage(TF::GRAY . 'Amount of Heads Eaten: ' . TF::GOLD . $this->player->get("Heads"));
                $sender->sendMessage(TF::GRAY . "----------------");
            }
        } else {
            if (isset($args[0])) {
                $player = $this->plugin->getServer()->getPlayer($args[0]);
                $offlineplayer = $this->plugin->getServer()->getOfflinePlayer($args[0]);
                $oname = $offlineplayer->getName();
                $name = $player->getName();
                if(!$player) {
                    $this->player = new Config($this->plugin->getDataFolder() . "players/" . strtolower($oname) . ".yml");
                    $sender->sendMessage(TF::GRAY . "---$oname's Stats---'");
                    $sender->sendMessage(TF::GRAY . 'Kills:' . TF::GOLD . $this->player->get("Kills"));
                    $sender->sendMessage(TF::GRAY . 'Deaths:' . TF::GOLD . $this->player->get("Deaths"));
                    $sender->sendMessage(TF::GRAY . 'UHCs:' . TF::GOLD . $this->player->get("UHCs"));
                    $sender->sendMessage(TF::GRAY . 'Wins:' . TF::GOLD . $this->player->get("Wins"));
                    $sender->sendMessage(TF::GRAY . 'Diamonds Mined:' . TF::GOLD . $this->player->get("Diamonds"));
                    $sender->sendMessage(TF::GRAY . 'Amount of Heads Eaten:' . TF::GOLD . $this->player->get("Heads"));
                    $sender->sendMessage(TF::GRAY . "----------------");
                }
                else {
                    $this->player = new Config($this->plugin->getDataFolder() . "players/" . strtolower($oname) . ".yml");
                    $sender->sendMessage(TF::GRAY . "---$name's Stats---'");
                    $sender->sendMessage(TF::GRAY . 'Kills:' . TF::GOLD . $this->player->get("Kills"));
                    $sender->sendMessage(TF::GRAY . 'Deaths:' . TF::GOLD . $this->player->get("Deaths"));
                    $sender->sendMessage(TF::GRAY . 'UHCs:' . TF::GOLD . $this->player->get("UHCs"));
                    $sender->sendMessage(TF::GRAY . 'Wins:' . TF::GOLD . $this->player->get("Wins"));
                    $sender->sendMessage(TF::GRAY . 'Diamonds Mined:' . TF::GOLD . $this->player->get("Diamonds"));
                    $sender->sendMessage(TF::GRAY . 'Amount of Heads Eaten:' . TF::GOLD . $this->player->get("Heads"));
                    $sender->sendMessage(TF::GRAY . "----------------");

                }
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