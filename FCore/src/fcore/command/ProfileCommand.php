<?php

declare(strict_types=1);

namespace fcore\command;

use fcore\FCore;
use fcore\form\Button;
use fcore\profile\ProfileManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

/**
 * Class ProfileCommand
 * @package fcore\command
 */
class ProfileCommand extends Command {

    /** @var FCore $plugin */
    public $plugin;

    /**
     * ProfileCommand constructor.
     * @param FCore $plugin
     */
    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        parent::__construct("profile", "Displays stats");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender instanceof Player) {
            return;
        }
        $form = $this->plugin->formApi->createSimpleForm(
            "§2§lYOUR PROFILE", "§7Stats:\n".
            "§bCoins: ".ProfileManager::$players[$sender->getName()]["coins"]."\n",
            [
                new Button("§aOK", null, null)
            ],
            null);
        $form->send($sender);
    }

}