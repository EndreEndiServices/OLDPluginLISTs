<?php

namespace athletic\game;

use pocketmine\Player;

use pocketmine\scheduler\Task;

class TimerTask extends Task{

    private $time = [];
    private $player;

    public function __construct(Player $player){
        $this->player = $player;
        $this->time[$this->player->getName()] = 0;
    }

    public function onRun(int $currentTick){
        $this->time[$this->player->getName()]++;
    }

    public function stopTimer(): int{
        $this->getHander()->cancel();
        $result =  $this->time[$this->player->getName()];
        return $result;
    }

    public function resetTimer(): void{
        unset($this->time[$this->player->getName()]);
    }
}
