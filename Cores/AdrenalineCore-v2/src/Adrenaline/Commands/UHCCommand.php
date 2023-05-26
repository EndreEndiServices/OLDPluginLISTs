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

class UHCCommand extends BaseCommand {
    public function __construct(CoreLoader $plugin) {
        parent::__construct($plugin, "uhc", "Main UHC command!", "/uhc [help]", []);
        //chmod($this, 777);
    }

    public function execute(CommandSender $sender, $commandLabel, array $args) {
        if (count($args) < 1) {
            $sender->sendMessage($this->getPlugin()->sendPrefix() . TextFormat::RED . $this->usageMessage);
            return true;
        }

        if (isset($args[0])) {
            switch ($args[0]) {
                case "start":
            }
        }
    }
}