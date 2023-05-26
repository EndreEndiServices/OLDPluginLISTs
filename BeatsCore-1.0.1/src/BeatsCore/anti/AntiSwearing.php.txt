<?php

declare(strict_types=1);

namespace BeatsCore\anti;

use BeatsCore\Core;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;

class AntiSwearing implements Listener{

    /** @var Core */
    private $plugin;
    /** @var array */
    private $badwords;

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
        $this->badwords = ["anal", "anus", "ass", "bastard", "bitch", "boob", "cock", "cum", "cunt", "dick", "dildo", "dyke", "fag", "faggot", "fuck", "fuk", "fk", "hoe", "tits", "whore", "handjob", "homo", "jizz", "cunt", "kike", "kunt", "muff", "nigger", "penis", "piss", "poop", "pussy", "queer", "rape", "semen", "sex", "shit", "slut", "titties", "twat", "vagina", "vulva", "wank", "FUCK", "BITCH", "FAGGOT", "DICK", "CUNT", "ASS", "nigger", "nigga"];
    }

    public function onChat(PlayerChatEvent $event) : void{
        $msg = $event->getMessage();
        $player = $event->getPlayer();
        if(!$player instanceof Player) return;
        if($player->hasPermission("beats.anti.swear")){
        }else{
            foreach($this->badwords as $badwords){
                if(strpos($msg, $badwords) !== false){
                    $player->sendMessage("§l§dBeats§bChat §8»§r §cDo not swear, or you might get banned!");
                    $event->setCancelled();
                    return;
                }
            }
        }
    }
}