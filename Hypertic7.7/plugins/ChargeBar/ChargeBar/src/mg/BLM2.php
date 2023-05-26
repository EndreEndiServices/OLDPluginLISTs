<?php


namespace mg;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as color;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\entity\Effect;

class BLM2 extends PluginTask{
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
     if($it->getId() == 322){
                    $this->ShowCharge();
         if($this->charge >= 8){
             
           $this->player->getInventory()->setItemInHand(new Item(322, 0, $this->player->getInventory()->getItemInHand()->getCount() - 1));
             $this->player->setHealth($this->player->getHealth() + 5);
             $this->player->addEffect(Effect::getEffect(10)->setAmplifier(1)->setDuration(200)->setVisible(false));
             $this->player->addEffect(Effect::getEffect(21)->setAmplifier(0)->setDuration(1000)->setVisible(false));
             $this->charge = 0;
             $s11 = new \pocketmine\level\sound\PopSound($this->player->getPosition());
             $this->player->getLevel()->addSound($s11);
             
         }  else {
          $this->charge = $this->charge + 3;
      $this->owner->f2[$this->player->getName()] = FALSE;            
         }
         
      }else{
          $this->charge = 0;
      $this->owner->f2[$this->player->getName()] = FALSE;
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
            $this->player->sendTip(color::GREEN."-".color::GRAY."-------");
            break;
        case 2:
            $this->player->sendTip(color::GREEN."--".color::GRAY."------");            
            break;
        case 3:
            $this->player->sendTip(color::GREEN."---".color::GRAY."-----");           
            break;
        case 4:
            $this->player->sendTip(color::GREEN."----".color::GRAY."----"); 
            break;
        case 5:
            $this->player->sendTip(color::GREEN."-----".color::GRAY."---");
            break;
        case 6:
            $this->player->sendTip(color::GREEN."------".color::GRAY."--");         
            break;
        case 7:
            $this->player->sendTip(color::GREEN."-------".color::GRAY."-");           
            break;
        case 8:
            $this->player->sendTip(color::RED."--------");           
            break;
        }
    }
    
         }