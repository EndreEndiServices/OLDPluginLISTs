<?php

namespace crashbang;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;

class Timer extends PluginTask {

    private $o, $tick;

    public function __construct(\pocketmine\plugin\Plugin $owner) {
        $this->o = $owner;
        $this->tick = 0;
        parent::__construct($owner);
    }

    public function onRun($tick) {
        if($this->tick === 0 and $this->o->status > 0) {
            if(--$this->o->timer == 0) $this->o->stop();
            if($this->o->status === 1 and $this->o->timer === CrashBang::GAME_TIME) $this->o->start();
        }
        foreach(Server::getInstance()->getOnlinePlayers() as $p) $this->process($p);
        $this->tick = ++$this->tick % 20;
    }

    public function process(Player $p) {
        $msg = "서버 상태 : ";
        switch($this->o->status) {
            case 0:
                $msg .= "게임 중이 아님";
                break;
            case 1:
                $msg .= "능력 추첨/선택 중\n";
                break;
            case 2:
                $msg .= TextFormat::AQUA."게임 중\n".TextFormat::RESET;
        }

        if($this->o->status === 1) {
            $msg .= TextFormat::GREEN."남은 시간 : " . TextFormat::GOLD . ($this->o->timer - CrashBang::GAME_TIME) . TextFormat::GREEN . "초";
        } elseif($this->o->status === 2) {
            if($this->o->timer < 60) {
                $msg .= TextFormat::GREEN."남은 시간 : " . TextFormat::RED . $this->o->timer . "/" . CrashBang::GAME_TIME . TextFormat::GREEN . "초";
            } else {
                $msg .= TextFormat::GREEN."남은 시간 : " . TextFormat::GOLD . $this->o->timer . "/" . CrashBang::GAME_TIME . TextFormat::GREEN . "초";
            }
        }

        if($this->tick === 0 && $this->o->status === 2) {
            $this->o->cooldown[$p->getName()]--;
        }

        if($this->o->status === 2) {
            if(Skills::$passive[$this->o->skill[$p->getName()]]) {
                $msg .= "\n스킬 상태 : " . TextFormat::GREEN . "패시브(항상 적용)";
            } else {
                if($this->o->cooldown[$p->getName()] > 0)
                    $msg .= "\n스킬 상태 : ".TextFormat::RED . "사용 불가(쿨타임 " . TextFormat::RESET . $this->o->cooldown[$p->getName()] . TextFormat::RED ."초)";
                else $msg .= "\n스킬 상태 : ".TextFormat::GREEN . "사용 가능";
            }
        }
        $p->sendTip($msg);
    }
}
