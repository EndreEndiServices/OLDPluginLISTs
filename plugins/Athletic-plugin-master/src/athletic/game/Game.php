<?php

namespace athletic\game;

use pocketmine\Player;

use athletic\Main;
use athletic\utils\PlayerModule;
use athletic\utils\Head;

class Game extends Head implements Athletic{

    private $player;
    private $athletic;
    private $task;
    private $loop;

    private $plugin;

    public function __construct(Player $player = null, string $athletic = null, Main $plugin){
        parent::__construct($player);
        $this->player = $player;
        $this->athletic = $athletic;
        $this->task = new TimerTask($this->getPlayer());
        $this->loop = new VoidLoopTask($this->getPlayer());
    }

    private $isPlaying = false;

    public function onStart(): void{
        $this->getPlayer()->sendMessage("チャレンジ開始");
        $this->isPlaying = true;
        $this->getScheduler()->scheduleRepeatingTask($this->task, 20);
    }

    public function onFailed(): void{
        $this->getPlayer()->sendMessage("チャレンジ失敗");
        $this->isPlaying = false;
        $this->task->resetTimer();
    }

    public function onClear(): void{
        // クリアタイムが上位なら PlayerHead APIで頭を設置する処理に移行する
        $time = $this->getTime();
        $minutes = $time["minutes"];
        $seconds = $time["seconds"];
        $this->getPlayer()->sendMessage(
            "チャレンジ成功\n".
            $minutes." 分 ".$seconds." 秒"
        );
        $this->isPlaying = false;
        PlayerModule::getInstance()->removePlayerRepository($this->getPlayer());
    }

    public function isPlaying(): bool{
        return $this->isPlaying;
    }

    public function getPlayer(): Player{
        return $this->player;
    }

    public function getAthletic(): string{
        return $this->athletic;
    }

    public function getTime(): array{
        $result = $this->task->stopTimer();
        $time["minutes"] = floor(($result / 60) % 60);
        $time["seconds"] = $result % 60;
        return $time;
    }

    private function getPlugin(): Main{
        return $this->plugin;
    }
}
