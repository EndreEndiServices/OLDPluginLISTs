<?php

namespace TheBridges\utils;

class Kit {

  const APPLE = 0;
  const ARCHER = 1;
  const MINER = 2;
  
    public function getItems($kit){
      switch ($kit){
        case 0:
          return [[Item::APPLE,0,3]];
        break;
        case 1:
          return [[Item::BOW,0,1],[Item::ARROW,0,3]];
        break;
        case 2:
          return [[Item::STONE_PICKAXE,0,1]];
        break;
        case default:
          $this->getLogger()->warning(TextFormat::DARK_RED."Invalid kit!");
        break;  
      }  
    }
    
    public function getName($id){
      switch ($id){
        case 0:
          return "Apple";
        break;
        case 1:
          return "Archer";
        break;
        case 2:
          return "Miner";
        break;
        case default;
          return "Invalid Kit";
        break;        
      }
    } 
  
}