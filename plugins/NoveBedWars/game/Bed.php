<?php

namespace Bedwars\game;

class Bed {
    
    private $team;

    public function __construct(Team $team){
        $this->team = $team;
    }
    
    public function setDestroyed(){
        $this->team->bed = null;
    }
    
}
