<?php

namespace BedWars\task;

use BedWars\arena\Arena;
use BedWars\game\GamePlayer;
use pocketmine\scheduler\Task;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as TF;

class ArenaScheduler extends Task{
    
    public $gameTime = 3600;
    public $startTime = 120;
    public $sign = 0;
    public $arena;
    public $drop = 0;
    
    public function __construct(Arena $arena){
        $this->arena = $arena;
    }
    
    public function onRun($currentTick){
        if($this->arena->starting === true){
            $this->starting();
        }
        if($this->arena->getPhase() === Arena::RUNNING){
            $this->game();
        }
        if($this->sign === 2){
            if($this->arena->getPhase() === Arena::WAITING){
                $this->updateTeamSigns();
                $this->arena->checkLobby();
            }
            $this->sign = 0;
        }
        $this->sign++;
    }
    
    public function updateTeamSigns(){
        $blue = $this->arena->bw->lobby->getTile($this->arena->bw->wd->get("Lobby", "blue_sign"));
        $red = $this->arena->bw->lobby->getTile($this->arena->bw->wd->get("Lobby", 'red_sign'));
        $yellow = $this->arena->bw->lobby->getTile($this->arena->bw->wd->get("Lobby", 'yellow_sign'));
        $green = $this->arena->bw->lobby->getTile($this->arena->bw->wd->get("Lobby", 'green_sign'));
        if (!$blue instanceof Sign or !$red instanceof Sign or !$yellow instanceof Sign or !$green instanceof Sign){
            return;
        }
        $blue->setText("", TF::BOLD.TF::BLUE."[BLUE]", TF::GRAY.count($this->arena->teams["blue"]->getPlayers())." players", "");
        $red->setText("", TF::BOLD.TF::RED."[RED]", TF::GRAY.count($this->arena->teams["red"]->getPlayers())." players", "");
        $yellow->setText("", TF::BOLD.TF::YELLOW."[YELLOW]", TF::GRAY.count($this->arena->teams["yellow"]->getPlayers())." players", "");
        $green->setText("", TF::BOLD.TF::GREEN."[GREEN]", TF::GRAY.count($this->arena->teams["green"]->getPlayers())." players", "");
        
    }
    
    public function starting(){
        if($this->startTime === 5){
                $this->arena->selectMap();
            }
            if($this->startTime === 0){
                $this->arena->startGame();
                $this->startTime = 120;
                return;
            }
            foreach($this->arena->getPlayers() as $game) {
                if (!$game instanceof GamePlayer){
                    return;
                }
                $p = $game->getPlayer();
                $p->setExpLevel($this->startTime);
                $p->setExpBarPercent(100);
                //$p->sendPopup(TF::AQUA."Starting in $this->startTime seconds");
            }
            $this->startTime--;
    }
    
    public function game(){
        $this->gameTime--;
        switch($this->gameTime){
            case 900:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 15 minutes");
                break;
            case 600:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 10 minutes");
                break;
            case 300:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 5 minutes");
                break;
            case 240:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 4 minutes");
                break;
            case 180:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 3 minutes");
                break;
            case 120:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 2 minutes");
                break;
            case 60:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 1 minutes");
                break;
            case 5:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 5 seconds");
                break;
            case 4:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 4 seconds");
                break;
            case 3:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 3 seconds");
                break;
            case 2:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 2 seconds");
                break;
            case 1:
                $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ends in 1 seconds");
                break;
        }
        if($this->gameTime <= 0){
            $this->arena->messageAllPlayers($this->arena->bw->prefix.TF::RED.TF::BOLD."Game ended!");
            $this->arena->stopGame();
            return;
        }
        $this->arena->checkAlive();
            $this->arena->dropBronze();
            if($this->drop === 0){
                $this->arena->dropIron();
                $this->arena->dropGold();
            }
            $this->drop++;
            if($this->drop === 15){
                $this->arena->dropIron();
            }
            if($this->drop === 30){
                $this->arena->dropIron();
            }
            if($this->drop === 45){
                $this->drop = 0;
            }
    }
}