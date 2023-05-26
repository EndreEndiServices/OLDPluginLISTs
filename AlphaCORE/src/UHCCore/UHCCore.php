<?php
namespace UHCCore;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\Listener as L;
use pocketmine\plugin\PluginBase as PB;
use pocketmine\utils\TextFormat as TF;
use pocketmine\Player;
use pocketmine\Server;

class UHCCore extends PB implements L{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info(TF::GREEN. "UHC Core Enabled!");
    }

    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();
        $Group = $this->getPP()->getUserDataMgr()->getGroup($event->getPlayer());
        $event->setDeathMessage("§7[§l§3Alpha§r§7] §9" . $player->getName() ." has been eliminated");
        if($Group->getName() === "VIP" or $Group->getName() === "Host" or $Group->getName() === "Admin" or $Group->getName() === "Owner"){
            $player->setGamemode(3);
            return;
        } else {
            $player->close("", "§bThanks for playing! Follow @AlphaUHCs for Upcoming UHC!!");
            $player->setWhitelisted(false);
        }
    }

    public function onJoin(PlayerJoinEvent $event){
        $event->setJoinMessage("§8[§a+§8] §7" . $event->getPlayer()->getName());
    }

    public function onQuit(PlayerQuitEvent $event){
        $event->setQuitMessage("§8[§4-§8] §7" . $event->getPlayer()->getName());
    }

    public function getPP(){
        $pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        if($pp === null){
            $this->getLogger()->critical("PurePerms is not loaded! Plugin will not function properly");
        } else return $pp;
    }

}
?>