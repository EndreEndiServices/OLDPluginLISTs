<?php

namespace TheBridges;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use TheBridges\MySQLManager;

class TheBridges extends PluginBase {

    public $mysqlmgr;
    protected $arena;
 
    public function onLoad(){
      if ($this->getDataFolder() === null){
        @mkdir ($this->getDataFolder(), 0777);
      }
     $this->getLogger()->info(TextFormat::YELLOW."Loading TheBridges...");
    }
 
    public function onEnable(){
      $this->getLogger()->info(TextFormat::GREEN."TheBridges loaded!");
      $this->mysqlmgr = new MySQLManager($this);
      $this->mysqlmgr->createMySQLConnection();
      $this->mtcore = $this->getServer()->getPluginManager()->getPlugin("MTCore");
      $this->registerArena("bridges1");
    }
 
    public function onDisable(){
      $this->getLogger()->info(TextFormat::DARK_RED."TheBridges disabled!");
    }

    public function registerArena($arena_name){
      $this->arena = new Arena($this);
    }

    public function getArena($name){
      return $this->provider->arenas[$name];
    }
 
    public function getTeam($arena,$color){
      return $this->arena->get
    }
 
    public function getPrefix(){
      return (TextFormat::GRAY."[".TextFormat::BOLD.TextFormat::WHITE."The".TextFormat::YELLOW."Bridges".TextFormat::RESET.TextFormat::GRAY."] ");
    }

    public function msg($p, $value){
      $p->sendMessage(\str_replace("&","§",($this->getPrefix().$value)));
    }
    
    public function joinToArena($p, $arena){
      if ($arena->isFull() and !(\in_array($this->mtcore->mysqlmgr->getRank($p->getName()), ["VIP+","Sponzor","extra","owner","co-owner","builder","youtuber"]))){
        $p->sendMessage($this->getPrefix().TextFormat::DARK_RED."Sorry, but arena is full");
        $p->teleport(x,y,z,lv); //chybí souřadnice hlavního lobby
        return; 
      }
      if (!($arena->getPhase() === Arena::OFF) or !($arena->getPhase() === Arena::PRESTART)){
        $p->sendMessage($this->getPrefix().TextFormat::DARK_RED."Sorry, but arena is already running");
        $p->teleport(x,y,z,lv); //chybí souřadnice hlavního lobby
        return;
      }
      $arena->joinPlayer($p); 
    }
    

}