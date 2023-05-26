<?php
namespace ARCore\Auth\Commands;

use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class PinCommand extends VanillaCommand {
    public function __construct($name, $plugin) {
        parent::__construct($name, "Get your pin", "/pin", ["pins"]);
        $this->setPermission("auth.command.pin");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, $currentAlias, array $args) {
        if(!$this->testPermission($sender)) {
            return true;
        }
        if(!$sender instanceof Player) {
            $sender->sendMessage("§cYou must use the command in-game.");
            return false;
        }
        $sender->sendMessage(str_replace("{pin}", $this->plugin->getPin($sender), $this->plugin->auth->get("pin")));
        return true;
    }

}
