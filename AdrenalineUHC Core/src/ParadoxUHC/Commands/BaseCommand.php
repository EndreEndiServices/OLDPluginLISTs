<?php

namespace ParadoxUHC\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use ParadoxUHC\UHC;

class BaseCommand extends Command implements PluginIdentifiableCommand {

    private $plugin;

    public function __construct(UHC $plugin, $name, $description, $usageMessage, $aliases)
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->plugin = $plugin;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     *
     * @return mixed
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if($this->testPermission($sender)){
            $result = $this->onExecute($sender, $args);
            if(is_string($result)){
                $sender->sendMessage($result);
            }
            return true;
        }
        return false;
    }

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function onExecute(CommandSender $sender, array $args){

    }

    /**
     * @return \pocketmine\plugin\Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}