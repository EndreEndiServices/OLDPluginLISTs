<?php

declare(strict_types=1);

namespace fcore\command;

use fcore\FCore;
use fcore\profile\ProfileManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class BanmanagerCommand extends Command {

    /** @var FCore $plugin */
    public $plugin;

    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        parent::__construct("b", "Banmanager commands", null, []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender instanceof Player) {
            return;
        }

        if(!(($sender->isOp()) || in_array(ProfileManager::$players[$sender->getName()]["rank"], ["helper", "builder", "admin"]))) {
            $sender->sendMessage("§cYou have not permissions to use this command!");
            return;
        }

        if(empty($args[0])) {
            $sender->sendMessage("§aBanManager commands: \n".
            "- /b ban <nick> <reason> Ban player\n".
            "- /b tempban <nick> <reason> <sec> Ban player\n".
            "- /b kick <nick> <reason> Kick player\n".
            "- /b mgkick <nick> Kick player from minigame\n".
            "- /b sp <nick> Spectate player\n".
            "- /b mute <nick> Mute|unmute player");
            return;
        }

        switch ($args[0]) {
            case "ban":
                if(empty($args[1])) {
                    $this->invalid($sender);
                    return;
                }
                $player = $this->plugin->getServer()->getPlayer($args[1]);
                if(!$player instanceof Player || $player == null) {
                    return;
                }
                $player->getServer()->getNameBans()->addBan($player->getName(), $args[2]);
                $player->kick("§cYou are banned: {$args[2]}");
                $sender->sendMessage("§aPlayer {$player->getName()} banned!");
                break;
            case "kick":
                if(empty($args[2])) {
                    $this->invalid($sender);
                    return;
                }
                $player = $this->plugin->getServer()->getPlayer($args[1]);
                if(!$player instanceof Player || $player == null) {
                    return;
                }
                $player->kick("§cYou are kicked: ".$args[2]);
                $sender->sendMessage("§aPlayer {$player->getName()} kicked!");
                break;
            case "sp":
                if(empty($args[1])) {
                    $sender->setGamemode($sender::SURVIVAL);
                    $sender->sendMessage("§aYou are leaved spectator mode!");
                }
                else {
                    $sender->setGamemode($sender::SPECTATOR);
                    $player = $sender->getServer()->getPlayer($args[1]);
                    if($player instanceof Player) {
                        $sender->teleport($player);
                    }
                    $sender->sendMessage("§aYou are leaved spectator mode!");
                }
            case "mute":
                if(isset($this->plugin->listenerMgr->listeners["main"]->muted[$player->getName()])) {

                }
        }
    }

    public function invalid(Player $player) {
        $player->sendMessage("invalid args (use /b help)");
    }
}
