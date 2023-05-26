<?php
 namespace IPECore;

 use pocketmine\utils\TextFormat as TF;
 use pocketmine\Player;
 use pocketmine\scheduler\PluginTask;

 Class Bar extends PluginTask{
     public function __construct($plugin)
     {
      $this->plugin = $plugin;
         parent::__construct($plugin);
     }
     public function onRun($currentTick)
     {
         foreach($this->getOwner()->getServer()->getOnlinePlayers() as $player){
             $player->setNameTag($player->getDisplayName()."\n"." ".round($player->getHealth() / 2, 1).TF::RED."HP");


         }
     }
 }