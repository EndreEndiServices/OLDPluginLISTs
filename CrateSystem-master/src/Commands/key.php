<?php

namespace Commands;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\item\Item;

use pocketmine\inventory\Inventory;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

use pocketmine\level\Level;

use pocketmine\utils\TextFormat as C;

use CrateSystem\Main;

class key extends PluginCommand{

    public function __construct($name, Main $plugin){
        parent::__construct($name, $plugin);
        $this->setDescription("Get an key for crate");
        $this->setAliases(["Key"]);
        $this->setPermission("crate.key");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{

        if (!$sender->hasPermission("crate.key")) {
            $sender->sendMessage("§cYou are not allow to do that.");
            return false;
        }

        $e = Enchantment::getEnchantment((int) 0);

        if (count($args) < 1) {
            $sender->sendMessage("§b===>§eKeys§b<===");
            $sender->sendMessage("§a/key Common <player> §e: §bGet Common key.");
            $sender->sendMessage("§c/key Vote <player> §e: §bGet Vote key.");
            $sender->sendMessage("§6/key Rare <player> §e: §bGet Rare key.");
            $sender->sendMessage("§5/key Mythic <player> §e: §bGet Mythic key.");
            $sender->sendMessage("§9/key Legendary <player> §e: §bGet Legendary key.");
            $sender->sendMessage("§b===>§eKeys§b<===");
            return false;
        }
        switch ($args[0]){
            case "1":
            case "common":
            case "Common":
                    if (!$sender->hasPermission("crate.key")) {
                        $sender->sendMessage("§cYou are not allow to do that.");
                        return false;
                    }

                    if (count($args) < 2) {
                        $sender->sendMessage("Usage: /key Common <player> <amount>");
                        return false;
                    }

                    if (isset($args[1])) {
                        $player = $this->getPlugin()->getServer()->getPlayer($args[1]);
                    }
                        $player = $this->getPlugin()->getServer()->getPlayer($args[1]);
                    if (!$player instanceof Player) {
                        if ($player instanceof ConsoleCommandSender) {
                            $sender->sendMessage("§cPlease specify a player.");
                            return false;
                        }
                        $sender->sendMessage("§cCould not find player " . $args[1] . ".");
                        return false;
                    }

                    if (count($args) < 3) {
                        $sender->sendMessage("Usage: /key Common <player> <amount>");
                        return false;
                    }

                    if(isset($args[2])){
                        $amount = intval($args[2]);
                    }
            $amount = intval($args[2]);
            $common = Item::get(131,1,$amount);
            $common->addEnchantment(new EnchantmentInstance($e, (int) -0));
            $common->setCustomName("§aCommon");
            $player->getInventory()->addItem($common);

            if($this->getPlugin()->cfg->get("from-user") == true){
            $player->sendMessage("§eYou receive ". $args[2] . " §aCommon §eKey from §9" . $sender->getName());
            }

            if($this->getPlugin()->cfg->get("from-user") == false){
                $player->sendMessage("§eYou receive " . $args[2] . " §aCommon §eKey");
            }

            $sender->sendMessage("§9" . $player->getName() . " §ehas received " . $args[2] . " §aCommon §eKey");
            break;
            case "2":
            case "vote":
            case "Vote":
                    if (!$sender->hasPermission("crate.key")) {
                        $sender->sendMessage("§cYou are not allow to do that.");
                        return false;
                    }

                    if (count($args) < 2) {
                        $sender->sendMessage("Usage: /key Vote <player> <amount>");
                        return false;
                    }

                    if (isset($args[1])) {
                        $player = $this->getPlugin()->getServer()->getPlayer($args[1]);
                    }
                        $player = $this->getPlugin()->getServer()->getPlayer($args[1]);
                    if (!$player instanceof Player) {
                        if ($player instanceof ConsoleCommandSender) {
                            $sender->sendMessage("§cPlease specify a player.");
                            return false;
                        }
                        $sender->sendMessage("§cCould not find player " . $args[1] . ".");
                        return false;
                    }

                    if (count($args) < 3) {
                        $sender->sendMessage("Usage: /key Vote <player> <amount>");
                        return false;
                    }

                    if(isset($args[2])){
                        $amount = intval($args[2]);
                    }
            $amount = intval($args[2]);
            $vote = Item::get(131,2,$amount);
            $vote->addEnchantment(new EnchantmentInstance($e, (int) -0));
            $vote->setCustomName("§cVote");
            $player->getInventory()->addItem($vote);

            if($this->getPlugin()->cfg->get("from-user") == true){
            $player->sendMessage("§eYou receive " . $args[2] . " §cVote §eKey from §9" . $sender->getName());
            }

            if($this->getPlugin()->cfg->get("from-user") == false){
                $player->sendMessage("§eYou receive " . $args[2] . " §cVote §eKey");
            }

            $sender->sendMessage("§9" . $player->getName() . " §ehas received " . $args[2] . " §cVote §eKey");
            break;
            case "3":
            case "rare":
            case "Rare":
                    if (!$sender->hasPermission("crate.key")) {
                        $sender->sendMessage("§cYou are not allow to do that.");
                        return false;
                    }

                    if (count($args) < 2) {
                        $sender->sendMessage("Usage: /key Rare <player> <amount>");
                        return false;
                    }

                    if (isset($args[1])) {
                        $player = $this->getPlugin()->getServer()->getPlayer($args[1]);
                    }
                        $player = $this->getPlugin()->getServer()->getPlayer($args[1]);
                    if (!$player instanceof Player) {
                        if ($player instanceof ConsoleCommandSender) {
                            $sender->sendMessage("§cPlease specify a player.");
                            return false;
                        }
                        $sender->sendMessage("§cCould not find player " . $args[1] . ".");
                        return false;
                    }

                    if (count($args) < 3) {
                        $sender->sendMessage("Usage: /key Rare <player> <amount>");
                        return false;
                    }

                    if(isset($args[2])){
                        $amount = intval($args[2]);
                    }
            $amount = intval($args[2]);
            $rare = Item::get(131,3,$amount);
            $rare->addEnchantment(new EnchantmentInstance($e, (int) -0));
            $rare->setCustomName("§6Rare");
            $player->getInventory()->addItem($rare);

            if($this->getPlugin()->cfg->get("from-user") == true){
            $player->sendMessage("§eYou receive " . $args[2] . " §6Rare §eKey from §9" . $sender->getName());
            }

            if($this->getPlugin()->cfg->get("from-user") == false){
                $player->sendMessage("§eYou receive " . $args[2] . " §6Rare §eKey");
            }

            $sender->sendMessage("§9" . $player->getName() . " §ehas received " . $args[2] . " §6Rare §eKey");
            break;
            case "4":
            case "mythic":
            case "Mythic":
                    if (!$sender->hasPermission("crate.key")) {
                        $sender->sendMessage("§cYou are not allow to do that.");
                        return false;
                    }

                    if (count($args) < 2) {
                        $sender->sendMessage("Usage: /key Mythic <player> <amount>");
                        return false;
                    }

                    if (isset($args[1])) {
                        $player = $this->getPlugin()->getServer()->getPlayer($args[1]);
                    }
                        $player = $this->getPlugin()->getServer()->getPlayer($args[1]);
                    if (!$player instanceof Player) {
                        if ($player instanceof ConsoleCommandSender) {
                            $sender->sendMessage("§cPlease specify a player.");
                            return false;
                        }
                        $sender->sendMessage("§cCould not find player " . $args[1] . ".");
                        return false;
                    }

                    if (count($args) < 3) {
                        $sender->sendMessage("Usage: /key Mythic <player> <amount>");
                        return false;
                    }

                    if(isset($args[2])){
                        $amount = intval($args[2]);
                    }
            $amount = intval($args[2]);
            $mythic = Item::get(131,4,$amount);
            $mythic->addEnchantment(new EnchantmentInstance($e, (int) -0));
            $mythic->setCustomName("§5Mythic");
            $player->getInventory()->addItem($mythic);

            if($this->getPlugin()->cfg->get("from-user") == true){
            $player->sendMessage("§eYou receive " . $args[2] . " §5Mythic §eKey from §9" . $sender->getName());
            }

            if($this->getPlugin()->cfg->get("from-user") == false){
                $player->sendMessage("§eYou receive " . $args[2] . " §5Mythic §eKey");
            }

            $sender->sendMessage("§9" . $player->getName() . " §ehas received " . $args[2] . " §5Mythic §eKey");
            break;
            case "5":
            case "legendary":
            case "Legendary":
                    if (!$sender->hasPermission("crate.key")) {
                        $sender->sendMessage("§cYou are not allow to do that.");
                        return false;
                    }

                    if (count($args) < 2) {
                        $sender->sendMessage("Usage: /key Legendary <player><amount>");
                        return false;
                    }

                    if (isset($args[1])) {
                        $player = $this->getPlugin()->getServer()->getPlayer($args[1]);
                    }
                        $player = $this->getPlugin()->getServer()->getPlayer($args[1]);
                    if (!$player instanceof Player) {
                        if ($player instanceof ConsoleCommandSender) {
                            $sender->sendMessage("§cPlease specify a player.");
                            return false;
                        }
                        $sender->sendMessage("§cCould not find player " . $args[1] . ".");
                        return false;
                    }

                    if (count($args) < 3) {
                        $sender->sendMessage("Usage: /key Legendary <player> <amount>");
                        return false;
                    }

                    if(isset($args[2])){
                        $amount = intval($args[2]);
                    }
            $amount = intval($args[2]);
            $legendary = Item::get(131,5,$amount);
            $legendary->addEnchantment(new EnchantmentInstance($e, (int) -0));
            $legendary->setCustomName("§9Legendary");
            $player->getInventory()->addItem($legendary);

            if($this->getPlugin()->cfg->get("from-user") == true){
            $player->sendMessage("§eYou receive " . $args[2] . " §9Legendary §eKey from §9" . $sender->getName());
            }

            if($this->getPlugin()->cfg->get("from-user") == false){
                $player->sendMessage("§eYou receive " . $args[2] . " §9Legendary §eKey");
            }

            $sender->sendMessage("§9" . $player->getName() . " §ehas received " . $args[2] . " §9Legendary §eKey");
            break;
            default:
            $sender->sendMessage("§cPlease use /key <key> <player> <amount>");
            break;
            }
            return true;
        }
}
