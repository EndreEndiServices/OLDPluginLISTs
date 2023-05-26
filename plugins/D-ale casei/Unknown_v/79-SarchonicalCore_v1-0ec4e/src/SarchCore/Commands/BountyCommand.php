<?php

namespace SarchCore\Commands;

use SarchCore\SarchCore;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use onebone\economyapi\EconomyAPI;

class BountyCommand extends BaseCommand{

    private $plugin;

    public function __construct(SarchCore $plugin) {
        parent::__construct("bounty", $plugin);
        $this->setUsage(TextFormat::RED . "Usage: /bounty help");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, $commandLabel, array $args) {
        if(!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "  You must be a player to run this command!");
            return false;
        }
        if(!isset($args[0]) or !isset($args[1])) {
            $sender->sendMessage(TextFormat::RED . "Usage: /bounty <player> <price>");
            return false;
        }
        $player = $this->plugin->getServer()->getPlayer($args[0]);
        $price = intval($args[1]);

        if(strtolower($args[0]) === strtolower($sender->getName())) {
            $sender->sendMessage(TextFormat::RED . "Unable to place bounty on yourself!");
            return;
        }
        if(!$player instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Unable to place bounty on inactive player!");
            return;
        }
        if(abs($price) != $price or $price < 0) {
            $sender->sendMessage(TextFormat::RED . "Invalid Amount!");
            return;
        }
        if(EconomyAPI::getInstance()->myMoney($sender->getName()) < intval($price)) {
            $sender->sendMessage(TextFormat::RED . "You don't have enough money for this bounty!");
            return;
        }

        EconomyAPI::getInstance()->reduceMoney($sender->getName(), $price, true);

        if(isset($this->plugin->getBountyManager()->getBounties()[$player->getName()])) {
            $sender->sendMessage(TextFormat::GREEN . "Incresed bounty on " . TextFormat::WHITE . $player->getName() . TextFormat::GREEN . " by " . TextFormat::WHITE . "$" . $price);
            $this->plugin->getBountyManager()->addBounty($player, $price);
            return;
        }

        $this->plugin->getBountyManager()->addBounty($player, $price);

        $sender->sendMessage(TextFormat::GREEN . "Placed bounty on " . TextFormat::WHITE . $player->getName() . TextFormat::GREEN . " for " . TextFormat::WHITE . "$" . $price);
    }
}
