<?php

namespace BedWars\task;

use pocketmine\scheduler\Task;
use BedWars\arena\Arena;
use BedWars\game\GamePlayer;
use pocketmine\utils\TextFormat as TF;

class PopupTask extends Task{
    
    public $arena;
    public $task;
    public $ending = 0;
    
    public function __construct(Arena $arena){
        $this->arena = $arena;
    }
    
    public function onRun($currentTick){
        if($this->arena->getPhase() === Arena::RUNNING){
            $this->sendStatus();
        }
        if($this->arena->getPhase() === Arena::ENDING){
            if($this->ending === 30){
                $this->arena->stopGame();
                $this->ending = 0;
                return;
            }
            $this->ending++;
            $this->sendEnding();
        }
        if($this->arena->getPhase() === Arena::PRESTART){
            $this->sendVotes();
        }
    }
    
    public function sendVotes(){
        foreach($this->arena->lobbyp as $game){
            if ($game instanceof GamePlayer) {
                $p = $game->getPlayer();
                $vm = $this->arena->votemgr;
                $votes = [$vm->currentTable[0], $vm->currentTable[1], $vm->currentTable[2]];
                $p->sendTip("                                                   §8Voting §f| §6/vote <name>"
                    . "\n                                                 §b[1] §8$votes[0] §c» §a{$vm->stats[1]} Votes"
                    . "\n                                                 §b[2] §8$votes[1] §c» §a{$vm->stats[2]} Votes"
                    . "\n                                                 §b[3] §8$votes[2] §c» §a{$vm->stats[3]} Votes");
            }
        }                        //    |
    }
    
    public function sendStatus(){
        foreach(array_merge($this->arena->players, $this->arena->spectators) as $game){
            if (!$game instanceof GamePlayer){
                return;
            }
            $p = $game->getPlayer();
            $p->sendTip($this->arena->getGameStatus());
        }
    }
    
    public function sendEnding(){
        $team = $this->arena->getWinningTeam();
        $name = $team->getChat().$team->getColor();
        foreach(array_merge($this->arena->spectators, $this->arena->players) as $game){
            if (!$game instanceof GamePlayer) {
                return;
            }
            $p = $game->getPlayer();
            $p->sendTip(TF::GRAY."                    ================[ ".TF::DARK_AQUA."Progress".TF::GRAY." ]================\n"
                                                  . "                               ".TF::BOLD.$name.TF::GREEN." team won the game\n"
                        .TF::GRAY         . "======================================================");
            //$this->arena->map->addSound(new FizzSound(new Vector3($p->x, $p->y, $p->z)));
        }
    }
}