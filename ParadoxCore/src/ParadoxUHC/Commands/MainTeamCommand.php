<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\Commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use ParadoxUHC\UHC;

class MainTeamCommand extends BaseCommand {
    private $plugin;
    public $config;
    public $player;
    public $team;
    public $request = array();
    public $queue = array();

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "team", "This allows players to make teams.", "/team invite [name]", ["tea", "teamm", "tem"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        $this->config = new Config($this->plugin->getDataFolder()."players/".strtolower($sender->getName()).".yml", Config::YAML);
        if($sender instanceof Player){
            if(strtolower($args[0]) === "accept"){
                if(in_array($sender->getName(), $this->queue)){
                    $sender->sendMessage(TF::BLUE.TF::BOLD."[Team]".TF::RESET.TF::GRAY.' You have joined the team!');
                    unset($this->queue);
                    unset($this->request);
                }
                else {
                    $sender->sendMessage(TF::BLUE.TF::BOLD.'[Team]'.TF::RESET.TF::RED.' You have no requests!');
                }
            }
            if(strtolower($args[0]) === "deny"){
                unset($this->queue);
                unset($this->request);
            }
            else {
                $name = $args[0];
                $sname = $sender->getName();
                $player = $this->plugin->getServer()->getPlayer($name);
                if (!$player) {
                    $sender->sendMessage(TF::DARK_GRAY.TF::BOLD."[".TF::RESET.TF::BLUE."ParadoxUHC".TF::BOLD.TF::DARK_GRAY."]" . TF::RESET . TF::GRAY . ' That player is not online!');
                    return false;
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
    
    public function hasTeam(Player $player){
        $this->team = new Config($this->getDataFolder()."teams.yml");
        
    }

    public function addRequest(Player $requester, Player $requested){
        $rqrname = $requester->getName();
        $rqdname = $requested->getName();
        if($rqrname == $rqdname){
            $requester->sendMessage(TF::RED."You can't send a request to yourself!");
            return false;
        }
        if($this->hasTeam($requested)){
          return false;  
        }
        $requester->sendMessage(TF::DARK_GRAY.TF::BOLD."[".TF::RESET.TF::BLUE."ParadoxUHC".TF::BOLD.TF::DARK_GRAY."]". 'You have successfully sent an invite to' . $rqdname . '!');
        $requested->sendMessage(TF::DARK_GRAY.TF::BOLD."[".TF::RESET.TF::BLUE."ParadoxUHC".TF::BOLD.TF::DARK_GRAY."]" . TF::RESET . TF::GRAY . ' You have recieved an invite from ' . $rqrname . " to team! Do /team accept to join the team!");
        
    }
}