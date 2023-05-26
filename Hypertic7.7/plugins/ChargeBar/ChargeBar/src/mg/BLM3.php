<?php

namespace mg;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as color;
use pocketmine\plugin\Plugin;
use pocketmine\entity\Entity;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\item\Item;

class BLM3 extends PluginTask{
    private $player;
    private $charge;
    public function __construct(Plugin $plugin, Player $player) {
        $this->player = $player;
        $this->plugin = new $plugin;
        parent::__construct($plugin);
    }
    public function onRun($tick) {
        if($this->owner->getServer()->getPlayer($this->player->getName())){
            if($this->player->isOnline() && $this->player->getHealth() != 0){
      $it = $this->player->getInventory()->getItemInHand();
      $tw = array(268, 269, 272, 276);

      if($it->getId() == 339 || $it->getId() == 287 || $it->getId() == 369){
                    $this->ShowCharge();
         if($this->charge >= 10){
             $this->owner->f[$this->player->getName()] = TRUE;
             $this->charge = 10;
         }  else {
          $this->charge++;
      $this->owner->f[$this->player->getName()] = FALSE;            
         }
         
            }else{
          $this->charge = $this->charge;
      $this->owner->f[$this->player->getName()] = FALSE;
    }}}}
public function SetCharge($chargeAmount){
    
    if($this->charge = $chargeAmount){
        $this->charge = $chargeAmount;
    return TRUE;
    } else {
        return FALSE;
    }
}

public function ShowCharge(){
        switch ($this->charge){
            case 0:
                
            break;
        case 1:
            $this->player->sendTip(color::GREEN.color::BOLD."-".color::GRAY."---------");
            break;
        case 2:
            $this->player->sendTip(color::GREEN.color::BOLD."--".color::GRAY."--------");            
            break;
        case 3:
            $this->player->sendTip(color::GREEN.color::BOLD."---".color::GRAY."-------");           
            break;
        case 4:
            $this->player->sendTip(color::GREEN."----".color::GRAY."------"); 
            break;
        case 5:
            $this->player->sendTip(color::GREEN."-----".color::GRAY."-----");
            break;
        case 6:
            $this->player->sendTip(color::GREEN."------".color::GRAY."----");         
            break;
        case 7:
            $this->player->sendTip(color::GREEN."-------".color::GRAY."---");           
            break;
        case 8:
            $this->player->sendTip(color::GREEN."--------".color::GRAY."--");           
            break;
        case 9:
            $this->player->sendTip(color::GREEN."---------".color::GRAY."-");           
            break;
        case 10:
            $this->player->sendTip(color::RED."----------");           
            break;
        }
    }
    
         }