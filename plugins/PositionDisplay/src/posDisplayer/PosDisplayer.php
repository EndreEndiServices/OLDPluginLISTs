<?php

namespace posDisplayer;

use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\block\Block;

class PosDisplayer extends PluginBase implements Listener{
    
    public function onEnable(){
        $this->getLogger()->info("PosDisplayer enabled");
        $this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
            }
    
    public function onDisable(){
        $this->getLogger()->info("PosDisplayer disabled");
    }
    
    public function onPlayerInteract(PlayerInteractEvent $event){
        $p = $event->getPlayer();
        $b = $event->getBlock();
        $p->sendMessage(TextFormat::BLUE."X: ".TextFormat::GREEN.$b->x.TextFormat::BLUE." Y: ".TextFormat::GREEN.$b->y.TextFormat::BLUE." Z: ".TextFormat::GREEN.$b->z." yaw: ".$p->yaw." Pitch: ".$p->pitch.TextFormat::BLUE." ".$b->getId().":".$b->getDamage());
    }
}