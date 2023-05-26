<?php
namespace xFlare\xRulesx;

use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

class Main extends PluginBase implements Listener{
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
		$this->getLogger()->info(TEXTFORMAT::BLUE . "[xFlare]" .TEXTFORMAT::RED. " >>" .TEXTFORMAT::AQUA.  " Done! xRulesx is running on Version 1.0.0!");
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        $cmd = strtolower($command->getName());
        switch ($cmd){
            case "rules":
                if (!($sender instanceof Player)){
                    $sender->sendMessage(TEXTFORMAT::GOLD . "§e--------[§6H§7ypertic Rules§e]--------");
                    $sender->sendMessage(TEXTFORMAT::GREEN . "§c- " . $this->getConfig()->get("rule1"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "§c- " . $this->getConfig()->get("rule2"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "§c- " . $this->getConfig()->get("rule3"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "§c- " . $this->getConfig()->get("rule4"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "§c- " . $this->getConfig()->get("rule5"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "§c- " . $this->getConfig()->get("rule6"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "§c- " . $this->getConfig()->get("rule7"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "§c- " . $this->getConfig()->get("rule8"));
                    return true;
                }
                $player = $this->getServer()->getPlayer($sender->getName());
                if ($player->hasPermission("xflare.rules")){
                    $sender->sendMessage("§e--------[§6H§7ypertic Rules§e]--------");
                    $sender->sendMessage("§c- " . $this->getConfig()->get("rule1"));
                    $sender->sendMessage("§c- " . $this->getConfig()->get("rule2"));
                    $sender->sendMessage("§c- " . $this->getConfig()->get("rule3"));
                    $sender->sendMessage("§c- " . $this->getConfig()->get("rule4"));
                    $sender->sendMessage("§c- " . $this->getConfig()->get("rule5"));
                    $sender->sendMessage("§c- " . $this->getConfig()->get("rule6"));
                    $sender->sendMessage("§c- " . $this->getConfig()->get("rule7"));
                    $sender->sendMessage("§c- " . $this->getConfig()->get("rule8"));
                    return true;
                }
                break;
            }
        }
    }
?>