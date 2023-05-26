<?php

declare(strict_types=1);

namespace fcore\command;

use fcore\FCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

/**
 * Class FactionpeCommand
 * @package fcore\command
 */
class FactionpeCommand extends Command {

    public $plugin;

    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        parent::__construct("factionpe", "FactionPE commands", null, ["fpe"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        // TODO: no
    }

}