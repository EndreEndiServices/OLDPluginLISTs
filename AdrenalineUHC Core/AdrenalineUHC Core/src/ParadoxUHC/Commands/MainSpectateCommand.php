<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\Commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use ParadoxUHC\UHC;

class MainSpectateCommand extends BaseCommand {
    private $plugin;
    public $config;
    public $player;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "spectate", "Spectate players after dying.", "/spectate [player]", ["spectate", "spec", "sp"]);
        $this->setPermission("uhc.perms.spectate");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if($sender->hasPermission("uhc.perms.spectate")){
            if($sender instanceof Player) {
                if (isset($args[0])) {
                    if(!$sender->getGamemode() == Player::SURVIVAL) {
                        $player = $this->plugin->getServer()->getPlayer($args[0]);
                        if (!$player) {
                            $sender->sendMessage(TF::RED . "That player is not online!");
                            return false;
                        }
                        if (!$player->getGamemode() == Player::SURVIVAL) {
                            $sender->sendMessage(TF::RED . "You can't spectate this player!");
                            return false;
                        } else {
                            $position = $player->getPosition();
                            $level = $player->getLevel();
                            $sender->teleport($position);
                            $sender->sendMessage(TF::GREEN . "You are now spectating " . $player->getName()."!");
                        }
                    }
                    else {
                        $sender->sendMessage(TF::RED."You can't spectate while in survival!");
                        return false;
                    }
                }
                else {
                    $sender->sendMessage(TF::RED."Usage: ".$this->getUsage());
                    return false;
                }
            }
            else {
                $sender->sendMessage(TF::RED."Run this command in-game!");
                return false;
            }
        }
        else {
            $sender->sendMessage(TF::RED."You do not have permission to use this command!");
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