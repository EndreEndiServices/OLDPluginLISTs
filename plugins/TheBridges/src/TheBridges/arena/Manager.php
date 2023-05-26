<?php

namespace TheBridges\arena;


abstract class Manager{

    /** @var Arena $arena */
    private $arena;

    public function __construct(Arena $arena){
        $this->arena = $arena;
    }

    public function getTeam($team){

    }

    public function getTeamByName(){

    }
}