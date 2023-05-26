<?php

/*
 *               _                      _ _
 *      /\      | |                    | (_)
 *     /  \   __| |_ __ ___ _ __   __ _| |_ _ __   ___
 *    / /\ \ / _` | '__/ _ \ '_ \ / _` | | | '_ \ / _ \
 *   / ____ \ (_| | | |  __/ | | | (_| | | | | | |  __/
 *  /_/    \_\__,_|_|  \___|_| |_|\__,_|_|_|_| |_|\___|
 *
 * This plugin cannot be shared, or used by anyone else.
 * The only people allowed to use this, must have permission by AppleDevelops.
 * If you don't have permission, and use this plugin, I will not be afraid to take action.
 *
 * @author AppleDevelops
 *
 */

namespace Adrenaline\Commands;

use Adrenaline\CoreLoader;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ScaleCommand extends BaseCommand
{
    public function __construct(CoreLoader $plugin)
    {
        parent::__construct($plugin, "scale", "Set a player's scale!", "/scale [size] [player]", []);
    }

    public function execute(CommandSender $sender, $commandLabel, array $args){
        $targetPlayer = $sender;
        if (isset($args[1])) {
            $targetPlayer = $this->getPlugin()->getServer()->getPlayer($args[1]);
            if ($targetPlayer === null) {
                $sender->sendMessage($this->getPlugin()->sendPrefix() . TextFormat::RED . "Player not found!");

                return true;
            }
        } elseif (!($sender instanceof Player) or empty($args[0])) {
            $sender->sendMessage($this->getPlugin()->sendPrefix() . TextFormat::RED . $this->usageMessage);

            return true;
        }

        if ($targetPlayer === $sender) {
            if ($args[0] < 1 || $args[0] > 2) {
                $sender->sendMessage($this->getPlugin()->sendPrefix() . "Scale cannot be higher than 2 and lower than 1!");
            } else {
                $sender->setScale($args[0]);
                $sender->sendMessage($this->getPlugin()->sendPrefix() . "Set your scale size to " . $args[0] . "!");
            }
        } else {
            $targetPlayer->setScale($args[0]);
            $sender->sendMessage($this->getPlugin()->sendPrefix() . "Set " . $targetPlayer->getDisplayName() . "'s scale size to " . $args[0] . "!");
            $targetPlayer->sendMessage($this->getPlugin()->sendPrefix() . "Your scale size has been set to " . $args[0] . "!");
        }
    }
}