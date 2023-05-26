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
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;

class BaseCommand extends Command implements PluginIdentifiableCommand
{

    private $plugin;

    public function __construct(CoreLoader $plugin, $name, $description, $usageMessage, $aliases)
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if ($this->testPermission($sender)) {
            $result = $this->onExecute($sender, $args);
            if (is_string($result)) {
                $sender->sendMessage($result);
            }
            return true;
        }
        return false;
    }

    public function onExecute(CommandSender $sender, array $args)
    {

    }

    public function getPlugin()
    {
        return $this->plugin;
    }
}