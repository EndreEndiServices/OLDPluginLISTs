<?php

namespace athletic\utils;

use pocketmine\Player;

use athletic\Main;
use athletic\game\Game;

class PlayerModule{

    private static $instance;

    public static function init(Main $plugin){
        self::$instance = new PlayerModule();
    }

    private $playing = [];

    public function setPlayer(Player $player, string $athletic): void{
        $this->playing[$player->getName()] = new Game($player, $athletic, $plugin);
    }

    public function existsPlayerRepository(Player $player): bool{
        if (!isset(self::$playing[$player->getName()])){
            return false;
        }
        return true;
    }

    public function getPlayerRepository(Player $player): Game{
        return $this->playing[$player->getName()];
    }

    public function removePlayerRepository(Player $player): void{
        unset($this->playing[$player->getName()]);
    }

    public static function getInstance(): self{
        return self::$instance;
    }
}
