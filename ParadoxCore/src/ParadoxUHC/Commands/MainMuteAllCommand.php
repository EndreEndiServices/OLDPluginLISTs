<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\Commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use ParadoxUHC\UHC;

class MainMuteAllCommand extends BaseCommand {
    private $plugin;
    public $config;
    public $player;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "globalmute", "Turn global mute on and off", "/globalmute [on|off]", ["gmute"]);
        $this->setPermission("uhc.perms.gm");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if($sender->hasPermission("uhc.perms.gm")){
            if(isset($args[0])){
                if($args[0] === "on"){
                    if($this->plugin->config->get("mute") === "true"){
                        $sender->sendMessage(TF::RED.'Global mute is already on!');
                        return;
                    }
                    $this->plugin->config->set("mute", "true");
                    $this->plugin->config->save();
                    $this->plugin->getServer()->broadcastMessage(TF::DARK_GRAY.TF::BOLD.'['.TF::RESET.TF::RED.'AlphaUHC'.TF::DARK_GRAY.TF::BOLD.']'.TF::RESET.TF::GRAY.'Global mute has been enabled by an admin!');
                }
                if($args[0] === "off"){
                    if($this->plugin->config->get("mute") === "false"){
                        $sender->sendMessage(TF::RED.'Global mute is already off!');
                        return;
                    }
                    $this->plugin->config->set("mute", "false");
                    $this->plugin->config->save();
                    $this->plugin->getServer()->broadcastMessage(TF::DARK_GRAY.TF::BOLD.'['.TF::RESET.TF::RED.'AlphaUHC'.TF::DARK_GRAY.TF::BOLD.']'.TF::RESET.TF::GRAY.'Global mute has been disabled by an admin!');
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