<?php
namespace ffa;

class StateFFA{
    const WAITING = 1;
    const LOADING = 2;
    const INGAME = 3;
    const RESTARTING = 4;
    public static $gameState;

    public function StateFFA(){
        
    }
    public static function GetState(){
        return StateFFA::$gameState;
    }
    public static function SetState($state){
       StateFFA::$gameState = $state;
    }
    public static function IsState($state){
      if(StateFFA::$gameState == $state){
          return TRUE;
      }  else {
          return FALSE;    
      }
      return FALSE;
    }

}