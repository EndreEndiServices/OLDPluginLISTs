<?php

declare(strict_types=1);

namespace BeatsCore\anti;

use BeatsCore\Core;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;

class AntiAdvertising implements Listener{

    /** @var Core */
    private $plugin;
    /** @var array */
    private $links;

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
        $this->links = [".leet.cc", ".playmc.pe", ".net", ".com", ".us", ".co", ".co.uk", ".ddns", ".ddns.net", ".cf", ".pe", ".me", ".cc", ".ru", ".eu", ".tk", ".gq", ".ga", ".ml", ".org", ".1", ".2", ".3", ".4", ".5", ".6", ".7", ".8", ".9", "my server", "my sever", "ma server", "mah server", "ma sever", "mah sever"];
    }

    public function onChat(PlayerChatEvent $event) : void{
        $msg = $event->getMessage();
        $player = $event->getPlayer();
        if(!$player instanceof Player) return;
        if($player->hasPermission("beats.anti.advertise")){
        }else{
            foreach($this->links as $links){
                if(strpos($msg, $links) !== false){
                    $player->sendMessage("§l§dBeats§bChat §8»§r §cDo not advertise, or you might get banned!");
                    $event->setCancelled();
                    return;
                }
            }
        }
    }
}