<?php

namespace NJob;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\level\sound\FizzSound;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\Player;
class NJob extends PluginBase implements Listener{

public $eco;

  public function onEnable(){
   $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
}

  public function NJob(BlockBreakEvent $event){
$b = $event->getBlock()->getID();
$p = $event->getPlayer();
if($b == 17){
$this->eco->addMoney($p, 20);
$p->sendPopup("§e+20$");
$p->getLevel()->addSound(new FizzSound($p));
$p->getLevel()->addParticle(new ExplodeParticle($p));
    }
 }
}