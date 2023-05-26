<?php
namespace Donate;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;

class Main extends PluginBase implements Listener{
	protected $staff;
	protected function reload() {
	}
		public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
		$this->getLogger()->info(TEXTFORMAT::GOLD . "[-=>Donatii<=-]" .TEXTFORMAT::RED. " --> -->" .TEXTFORMAT::AQUA.  " Plugin activat cu succes".$this->getDescription()->getVersion());
    }
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		switch ($cmd){
            case "donate":
                if (!($sender instanceof Player)){
                    $sender->sendMessage(TEXTFORMAT::GOLD . "-=>§aInfo-Donatii§6<=-");
                    $sender->sendMessage(TEXTFORMAT::GREEN . "- " . $this->getConfig()->get("donate1"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "- " . $this->getConfig()->get("donate2"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "- " . $this->getConfig()->get("donate3"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "- " . $this->getConfig()->get("donate4"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "- " . $this->getConfig()->get("donate5"));
                    $sender->sendMessage(TEXTFORMAT::GREEN . "- " . $this->getConfig()->get("donate6"));
                    return true;
                }
                $player = $this->getServer()->getPlayer($sender->getName());
                if ($player->hasPermission("glbilson.donate")){
                    $sender->sendMessage("§6-=>§aInfo-Donatii§6<=- ");
                    $sender->sendMessage($this->getConfig()->get("donate1"));
                    $sender->sendMessage($this->getConfig()->get("donate2"));
                    $sender->sendMessage($this->getConfig()->get("donate3"));
                    $sender->sendMessage($this->getConfig()->get("donate4"));
                    $sender->sendMessage($this->getConfig()->get("donate5"));
                    $sender->sendMessage($this->getConfig()->get("donate6"));
                    return true;
                }
                break;
		}
		return false;
	}
}
