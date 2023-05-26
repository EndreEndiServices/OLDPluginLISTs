<?php

namespace athletic\game;

use pocketmine\Player;

use pocketmine\scheduler\Task;

use athletic\utils\PlayerModule;

class VoidLoopTask extends Task{

    private $player;

    public function __construct(Player $player){
        $this->player = $player;
    }

    public function onRun(int $currentTick){
        if (!($this->player->getLevel()->getName() === "world")){
            return;
        }

        if ($this->player->getFloorY() <= 3){
            $module = PlayerModule::getInstance();
            if (!($module->existsPlayerRepository($this->player))){
                return;
            }

            $repository = $module->getPlayerRepository();
            if ($repository->isPlaying()){
                $repository->onFailed();
            }
        }
    }
}
