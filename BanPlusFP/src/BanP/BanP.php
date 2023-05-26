<?php

namespace BanP;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\TextFormat as color;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerPreLoginEvent;

class BanP extends PluginBase implements Listener{

    public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("==========");
		$this->getLogger()->info("BanPlusFixPlug");
		$this->getLogger()->info("==========");
       $this->saveDefaultConfig();
       $config = $this->getConfig();
}

public function onBan(PlayerPreLoginEvent $event){
$player = $event->getPlayer();
$config = $this->getConfig();
$Ban = $this->getConfig()->get("Ban");
if($player->getServer()->getIPBans()->isBanned($player->getAddress())){ 
   $player->close("", "".$Ban."");
    }
}

public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        switch($command->getName()){
            case "banip":
$sender->sendMessage("§7[§6Ban§7]§fИспользование: §b/§fbanip §b<§fник§b> <§fпричина§b>");
                if(isset($args[0])){
                        $player = $this->getServer()->getPlayer($args[0]);
                        if($this->getServer()->getPlayer($args[0])){
            $reason = implode(" ", $args);
            $worte = explode(" ", $reason);
			 unset($worte[0]);
			  $reason = implode(" ", $worte);
$sender->getServer()->getIPBans()->addBan($player->getAddress(), $reason, null, $sender->getName());                  
            $player->kick("§r\n§e>§cВы были забанены§e<\n§7Администратор: §e ".$sender->getName()."\n§7Причина:§e [" .$reason ."]");
              $this->getServer()->broadcastMessage("§8§l======§6Ban§8======\n§6>§c".$args[0]." §eзабанен §c(а)§4!\n§6>§cАдминистратор: §e".$sender->getName()."\n§6>§cПричина: §e [".$reason."]\n§6>§cIP: §e".$player->getAddress()." \n§8§l======§6Ban§8======");
      } else {
            $player->kick("§r\n§e>§cВы были забанены§e<\n§7Администратор: §e ".$sender->getName()."\n§7Причина: §1[§eНе указана§1]");
              $this->getServer()->broadcastMessage("§8§l======§6Ban§8======\n§6>§c".$args[0]." §eзабанен §c(а)§4!\n§6>§cАдминистратор: §e".$sender->getName()."\n§6>§cПричина: §1[§eНе указана§1]\n§6>§cIP: §e".$player->getAddress()."\n§8§l======§6Ban§8======");
         return true;
                   }
break;
             }
       }
}
}