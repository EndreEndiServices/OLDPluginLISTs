<?php

namespace Adrenaline\Commands;

use Adrenaline\CoreLoader;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ClearInvCommand extends BaseCommand{
    public function __construct(CoreLoader $plugin){
        parent::__construct($plugin, "clearinv", "Clear a player's inventory!", "/clearinv [player]", []);
    }

    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        $targetPlayer = $sender;
        if (isset($args[0])) {
            $targetPlayer = $this->getPlugin()->getServer()->getPlayer($args[0]);
            if ($targetPlayer === null) {
                $sender->sendMessage($this->getPlugin()->sendPrefix() . TextFormat::RED . "Player not found!");

                return true;
            }
        }

        if ($targetPlayer === $sender) {
            if($sender instanceof Player) {
                $sender->getInventory()->clearAll();
                $sender->sendMessage($this->getPlugin()->sendPrefix() . "Your inventory has been cleared");
            }
        }else{
            $targetPlayer->getInventory()->clearAll();
            $targetPlayer->sendMessage($this->getPlugin()->sendPrefix() . "Your inventory has been cleared by " . $sender->getName() . "!");
            $sender->sendMessage($this->getPlugin()->sendPrefix() . "Successfully cleared " . $targetPlayer->getDisplayName() . "'s inventory!");
        }
    }
}