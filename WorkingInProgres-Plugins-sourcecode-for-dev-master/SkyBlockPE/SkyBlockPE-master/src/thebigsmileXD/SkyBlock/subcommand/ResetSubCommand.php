<?php
namespace thebigsmileXD\SkyBlock\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ResetSubCommand extends SubCommand
{
    public function canUse(CommandSender $sender) {
        return ($sender instanceof Player) and $sender->hasPermission("skyblock.command.reset");
    }

    public function getUsage() {
        return "";
    }

    public function getName() {
        return "reset";
    }

    public function getDescription() {
        return "Disposes and clears the island you're standing on";
    }

    public function getAliases() {
        return [];
    }

    public function execute(CommandSender $sender, array $args) {
        if (!empty($args)) {
            return false;
        }
        $player = $sender->getServer()->getPlayer($sender->getName());
        $island = $this->getPlugin()->getPlotByPosition($player->getPosition());
        if ($island === null) {
            $sender->sendMessage(TextFormat::RED . "You are not standing on an island");
            return true;
        }
        if ($island->owner !== $sender->getName() and !$sender->hasPermission("skyblock.admin.reset")) {
            $sender->sendMessage(TextFormat::RED . "You are not the owner of this island");
            return true;
        }

        $economy = $this->getPlugin()->getEconomyProvider();
        $price = $this->getPlugin()->getLevelSettings($island->levelName)->resetPrice;
        if ($economy !== null and !$economy->reduceMoney($player, $price)) {
            $sender->sendMessage(TextFormat::RED . "You don't have enough money to reset this island");
            return true;
        }

        if ($this->getPlugin()->resetPlot($island)) {
            $sender->sendMessage(TextFormat::GREEN . "Island reset");
        } else {
            $sender->sendMessage(TextFormat::RED . "Could not reset this island");
        }
        return true;
    }
}
