<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\Commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use ParadoxUHC\UHC;

class MainReportCommand extends BaseCommand {
    private $plugin;
    public $config;
    public $player;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "report", "This allows players to report hackers.", "/report [name] [reason]", ["repor", "reportt", "reporrt"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if($sender instanceof Player){
            if(count($args) < 2 ){
                $sender->sendMessage(TF::RED.'Usage: '. $this->getUsage());
                return false;
            }
                $name = array_shift($args);
                $sname = $sender->getName();
                $player = $this->plugin->getServer()->getPlayer($name);
                $pname = $player->getName();
                if(!$player){
                    $sender->sendMessage(TF::BOLD.TF::DARK_GRAY.'['.TF::RESET.TF::BLUE.'Report'.TF::BOLD.TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' That player is not online!');
                    return false;
                }
                if($sname === $pname){
                    $sender->sendMessage(TF::BOLD.TF::DARK_GRAY.'['.TF::RESET.TF::BLUE.'Report'.TF::BOLD.TF::DARK_GRAY.']'.TF::RESET.TF::GRAY." You cannot report yourself!");
                    return false;
                }
                else {
                    $sender->sendMessage(TF::BOLD.TF::DARK_GRAY.'['.TF::RESET.TF::BLUE.'Report'.TF::BOLD.TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' You have successfully reported '. TF::GOLD.$pname.TF::RESET.TF::GRAY.' for '.implode(" ", $args));
                    foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                        if($player->hasPermission("uhc.perms.report")){
                            $player->sendMessage(TF::BOLD.TF::DARK_GRAY.'['.TF::RESET.TF::BLUE.'Report'.TF::BOLD.TF::DARK_GRAY.'] '.TF::RESET.TF::GOLD.$sname.TF::RESET.TF::GRAY.' has reported '.TF::GOLD.$pname.TF::RESET.TF::GRAY.' for '.implode(" ", $args));
                        }
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