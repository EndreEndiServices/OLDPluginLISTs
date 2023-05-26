<?php

namespace presentkim\inventorymonitor\command\subcommands;

use pocketmine\command\CommandSender;
use presentkim\inventorymonitor\InventoryMonitor as Plugin;
use presentkim\inventorymonitor\command\{
  PoolCommand, SubCommand
};

class ReloadSubCommand extends SubCommand{

    public function __construct(PoolCommand $owner){
        parent::__construct($owner, 'reload');
    }

    /**
     * @param CommandSender $sender
     * @param String[]      $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, array $args) : bool{
        $this->plugin->load();
        $sender->sendMessage(Plugin::$prefix . $this->translate('success'));

        return true;
    }
}