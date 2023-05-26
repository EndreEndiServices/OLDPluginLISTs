<?php

namespace ktplus;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\block\Block;

class RD extends PluginBase implements Listener{
    
    public function onEnable(){
        $this->getLogger()->info("Plugin Random Ore Make by StrafelessPvP");
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
    }
    
    public function onBreak(BlockBreakEvent $e){

        $p = $e->getPlayer();
        $b = $e->getBlock();
            if($b->getId() == 4){

        $block = new Vector3($b->getX(),$b->getY(),$b->getZ());
        $array = [14,15,16,21,56,73,129];
        $b->getLevel()->setBlock($block,Block::get($array[array_rand($array)]));
        return true;
}else{ return false;}
    }
}

