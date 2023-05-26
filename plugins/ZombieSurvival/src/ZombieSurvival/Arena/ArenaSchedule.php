<?php

namespace ZombieSurvival\Arena;

class ArenaSchedule extends Task{
    
    private $plugin;
    private $data;
    private $time = 0;
    private $starttime = 60;
    
    public function __construct(Arena $a){
        $this->plugin = $a;
    }
    
    public function onRun($currentTick){
        if($this->plugin->game === 0){
            if(count($this->plugin->lobbyp) >= 1){
                $this->plugin->starting = true;
            }
            if($this->plugin->starting === true){
                if($this->starttime <= 0){
                    $this->plugin->starting = false;
                    $this->plugin->startGame();
                }
                foreach($this->plugin->lobbyp as $p){
                    $p->sendPopup("Hra startuje za $this->starttime sekund");
                    $this->starttime--;
                }
            }
        }
        if($this->plugin->game === 1){
        if($time >= 180){
            $this->plugin->newWave($this->plugin->wave+1);
            $time = 0;
        }
        foreach($this->plugin->level->getEntities() as $entity){
            $entities = 0;
            if($entity instanceof Zombie){
                $entities++;
            }
            if($entities <= 10){
                $wave = $this->plugin->wave;
                switch($wave){
                    case $wave >= 1 && $wave <= 2:
                        $this->plugin->spawnZombies();
                        break;
                    case $wave >= 3 && $wave <= 5:
                        $this->plugin->spawnZombies();
                        $this->plugin->spawnZombies();
                        break;
                    case $wave >= 6:
                        $this->plugin->spawnZombies();
                        $this->plugin->spawnZombies();
                        $this->plugin->spawnZombies();
                        break;
                }
            }
        }
        $time++;
    }
    }
}