<?php

namespace HotPotato;


use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;

class HotPotato extends PluginBase{

    public $arenas = [];
    public $data = [];

    public function onEnable(){
        $this->setArenasData();
        $this->registerArena("HP-1");
    }

    public function onDisable(){

    }

    private function setArenasData(){
        $this->data["HP-1"] = ["sign" => new Vector3(), "spawns" => [new Vector3()]];
        $this->data["HP-2"] = ["sign" => new Vector3(), "spawns" => [new Vector3()]];
    }

    private function registerArena($arena){
        $this->arenas[$arena] = new Arena($this, $arena);
        $this->getServer()->getPluginManager()->registerEvents($this->arenas["HP-1"], $this);
    }
}