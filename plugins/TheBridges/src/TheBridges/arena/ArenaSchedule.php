<?php

namespace kvetinac97\arena;

use kvetinac97\TheBridges;
use kvetinac97\arena\Arena;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\block\Block;
use pocketmine\level\Level;

class ArenaSchedule extends PluginTask {

    private $arena;
    private $main;
    protected $data;
    protected $ticks = 0;

    public function __construct(TheBridges $main, Arena $arena){
      $this->main = $main;
      $this->arena = $arena;
      $this->data = $this->arena->getMapData();
      parent::__construct($arena);
    }

    public function onRun($t){
      if ($this->arena->getPhase() === $this->arena::OFF){
        if (\count($this->arena->getPlayers()) >= 20){
          $this->arena->setPhase($this->arena::PRESTART);
        foreach ($this->arena->getPlayers() as $p){
          $p->setExp(10);
          $p->setExpLevel(30);
        }
      }
    }
    elseif ($this->arena->getPhase() === $this->arena::PRESTART){
      $this->ticks++;
      foreach ($this->arena->getPlayers() as $p){
        $p->setExpLevel($this->ticks);
      }
      if ($this->ticks === 30){
        $this->arena->setPhase($this->arena::WAITING);
        $this->ticks = 0;
        foreach ($this->arena->getPlayers() as $p){
          $p->setExpLevel(5);
          $clr = $this->data[$this->arena->getPlayerTeam($p)->getColor()];
          $p->teleport($clr[0],$clr[1],$clr[2],$this->data["name"][3]);
          $this->arena->listener->freezePlayer($p);
          $this->arena->kitmgr->addKit($p);
        }
      }
    }
    elseif ($this->arena->getPhase() === $this->arena::WAITING){
      $this->ticks++;
      foreach ($this->arena->getPlayers() as $p){
        $p->setExpLevel($this->ticks);
      }
      if ($this->ticks === 5){
        $this->ticks = 0;
        $this->arena->setPhase($this->arena::NOBRIDGE);
        $this->startArena($this->arena);
        foreach ($this->arena->getPlayers() as $p){
          $p->setExp(0);
          $p->setExpLevel(0);
          $p->sendMessage($this->arena->main->getPrefix().TextFormat::GREEN."Arena started!");
          $p->sendTip($this->arena->main->getPrefix().TextFormat::RED."[WARNING]: ".TextFormat::GRAY."The water is cold today!");
          $this->arena->listener->unfreezePlayer($p);
        }
      }
    }
    elseif ($this->arena->getPhase() === $this->arena::NOBRIDGE){
      $this->ticks++;
      foreach (\array_merge($this->arena->getPlayers(),$this->arena->getSpecs()) as $p){
        $p->sendTip(TextFormat::RED."Red: ".TextFormat::YELLOW.$this->arena->getTeam("red")->getCount().TextFormat::AQUA." Blue: ".TextFormat::YELLOW.$this->arena->getTeam("blue")->getCount().TextFormat::GREEN." Green: ".TextFormat::YELLOW.$this->arena->getTeam("green")->getCount().TextFormat::YELLOW." Yellow: ".$this->arena->getTeam("yellow")->getCount());
        $p->sendPopup(TextFormat::GOLD."Bridges in: ".TextFormat::AQUA.$ticks.TextFormat::GOLD." seconds");
      }
      if ($this->ticks === 300){
        $this->fallBridges($this->arena);
        $this->arena->setPhase($this->arena::BRIDGES);
      }
    }
    elseif ($this->arena->getPhase() === $this->arena::BRIDGES){
      $this->ticks++;
      if ($this->ticks <== 15){
        $this->fallBridges($this->arena);
      }
      if ($this->ticks === 900){
        $this->arena->setPhase($this->arena::FALLING);
      }
    }
    elseif ($this->arena->getPhase() === $this->arena::FALLING){
      $this->ticks++;
      if ($this->ticks === 600){
        $this->ticks = 0;
        $this->arena->setPhase($this->arena::OFF);
        $this->endArena($this->arena);
      }
    }
  }                                

    public function fallBridges(Arena $arena){
      $borders = $this->data["border"];
      for($x = $borders[0]; $x <= $borders[1]; $x++){
        for ($y = $borders[4]; $y <= $this->data["red"][1]; $y++){
          for ($z = $bprders[2]; $z <= $borders[3]; $z++;){
            if ($arena->map->getBlockIdAt($x,$y,$z) == Block::WATER){
              $chancetospawn = \mt_rand(1,50);
              if ($chancetospawn == 5){
                $arena->map->setBlock(new Vector3($x,$y+5,$z),Block::get(Block::SAND,1));
              }
            }
          }  
        }      
      }
      foreach ($this->arena->getViewers() as $p){
        $p->sendMessage(TextFormat::DARK_GREEN."The Bridges are falling!");
      }
    }



}