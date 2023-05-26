<?php
namespace BedWars\manager;

use BedWars\arena\Arena;
use BedWars\game\GamePlayer;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class VoteManager{
    public $arena;
    
    public $players = [];
    
    public $allVotes = ['BedWars1', 'BedWars2', 'Chinese', 'Kingdoms', 'Nether', 'STW5'];
    public $currentTable = [];
    public $stats = [];
    
    public function __construct(Arena $arena) {
        $this->arena = $arena;
        $this->createVoteTable();
    }
    
    public function createVoteTable(){
        $keys = array_rand($this->allVotes, 3);
        $this->currentTable[0] = $this->allVotes[$keys[0]];
        $this->currentTable[1] = $this->allVotes[$keys[1]];
        $this->currentTable[2] = $this->allVotes[$keys[2]];
        $this->stats = [1 => 0, 2 => 0, 3 => 0];
    }
    
    public function voteForMap(Player $p, $vote){
        if($this->arena->getPhase() === Arena::ENDING or $this->arena->getPhase() === Arena::RUNNING or !$this->arena->getPlayer($p) instanceof GamePlayer){
            $p->sendMessage($this->arena->bw->prefix.TF::RED."You can not vote now");
            return;
        }
        if(is_numeric($vote)){
            if(!(intval($vote) >=1 || intval($vote) <= 3)){
                $p->sendMessage($this->arena->bw->prefix.TF::GRAY."use /vote [map| map number]");
                return;
            }
            if(isset($this->players[strtolower($p->getName())])){
                $this->stats[$this->players[strtolower($p->getName())]]--;
            }
            $this->stats[intval($vote)]++;
            $this->players[strtolower($p->getName())] = intval($vote);
            $p->sendMessage($this->arena->bw->prefix.TF::GOLD."Voted for ".$this->currentTable[intval($vote)-1]);
            return;
        }
        if(is_string($vote)){
            if(strtolower($vote) !== strtolower($this->currentTable[0]) && strtolower($vote) !== strtolower($this->currentTable[1]) && strtolower($vote) !== strtolower($this->currentTable[2])){
                $p->sendMessage($this->arena->bw->prefix.TF::GRAY."use /vote [map| map number]");
                return;
            }
            if(isset($this->players[strtolower($p->getName())])){
                $this->stats[$this->players[strtolower($p->getName())]]--;
            }
            $final = str_replace([strtolower($this->currentTable[0]), strtolower($this->currentTable[1]), strtolower($this->currentTable[2])], [1, 2, 3], strtolower($vote));
            $this->stats[$final]++;
            $this->players[strtolower($p->getName())] = $final;
            $p->sendMessage($this->arena->bw->prefix.TF::GOLD."Voted for ".$vote);
            return;
        }
    }
}


