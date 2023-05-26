<?php

declare(strict_types=1);

namespace BeatsCore\commands;

use BeatsCore\Core;
use pocketmine\command\{
    Command, CommandSender, PluginCommand
};
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

class RulesCommand extends PluginCommand{

    /** @var Core */
    private $plugin;

    public function __construct($name, Core $plugin){
        parent::__construct($name, $plugin);
        $this->setDescription("Show you the rules.");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$sender instanceof Player){
            $sender->sendMessage(C::RED . "Please use this ingame.");
            return false;
        }

        $form = $this->plugin->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function (Player $sender, array $data){
            $result = $data[0];
        });

        $form->setTitle("§l§dBeats§bPE §cRules§r");
        $form->addLabel(file_get_contents($this->plugin->getDataFolder() . "rules.txt"));
        $form->sendToPlayer($sender);
        return true;
    }
}