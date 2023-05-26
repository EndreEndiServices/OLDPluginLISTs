<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\Commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use ParadoxUHC\UHC;

class MainHelpOpCommand extends BaseCommand {
    private $plugin;
    public $config;
    public $player;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "helpop", "Use this to send messages to hosts if you need anything.", "/helpop [message]", ["heloop", "helop"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if($sender instanceof Player){
            if(count($args) < 1 ){
                $sender->sendMessage(TF::RED.'Usage: '. $this->getUsage());
                return false;
            }
                $sname = $sender->getName();
                $sender->sendMessage(TF::GREEN."Your message has been sent!");
                foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                        if($player->hasPermission("uhc.perms.report")){
                            $player->sendMessage(TF::BOLD.TF::DARK_GRAY.'['.TF::RESET.TF::BLUE.'HelpOp'.TF::BOLD.TF::DARK_GRAY.'] '.TF::RESET.TF::GOLD."From ".$sname.TF::RESET.TF::GRAY.': '.implode(" ", $args));
                        }
                    }
        }
        else {
            $sender->sendMessage(TF::RED."Run this command in-game!");
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