<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\UHC;
use ParadoxUHC\Commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;

class MainUHCCommand extends BaseCommand {
    private $plugin;
    public $config;
    public $player;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "uhc", "Allows for hosts to start UHCs", "/uhc [start|stop]", ["uhc"]);
        $this->setPermission("uhc.commands.uhc");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if($sender->hasPermission("uhc.commands.uhc")) {
            if (isset($args[0])) {
                if ($args[0] === 'start') {
                    $this->plugin->status = UHC::STATUS_COUNTDOWN;
                    $this->config = new Config($this->plugin->getDataFolder()."config.yml");
                    foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
                        $health = $player->getMaxHealth();
                        $hunger = $player->getMaxFood();
                        $player->setFood($hunger);
                        $player->setHealth($health);
                        
                        
                    }
                    return true;
                }
                if ($args[0] === 'stop') {
                    $this->plugin->status = UHC::STATUS_WAITING;
                    foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                        $spawn = $this->plugin->getServer()->getLevelByName("hubuhc")->getSpawnLocation();
                        $player->teleport($spawn);
                        $player->removeAllEffects();
                        $health = $player->getMaxHealth();
                        $hunger = $player->getMaxFood();
                        $player->setFood($hunger);
                        $player->setHealth($health);
                        $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' The UHC has stopped!');
                    }
                    return true;
                } else {
                    $sender->sendMessage(TF::RED . 'Usage: /uhc [start|stop]');
                    return true;
                }
            } else {
                $sender->sendMessage(TF::RED . 'Usage: /uhc [start|stop]');
                return false;
            }
        }
        else {
            $sender->sendMessage(TF::RED . 'You do not have permission to use this command!');
            return false;
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