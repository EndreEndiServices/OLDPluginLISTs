<?php

namespace athletic\game;

use pocketmine\Player;

interface Athletic{

    public function onStart();
    public function onFailed();
    public function onClear();

    public function getPlayer(): Player;
    public function getAthletic(): string;
}
