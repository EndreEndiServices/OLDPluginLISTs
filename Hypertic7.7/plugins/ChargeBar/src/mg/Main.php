<?php
namespace mg;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\level\sound\ClickSound;
use mg\BLM;
//use pocketmine\nbt\tag\Byte;
//use pocketmine\nbt\tag\Compound;
//use pocketmine\nbt\tag\Double;
//use pocketmine\nbt\tag\Enum;
//use pocketmine\nbt\tag\Float;
//use pocketmine\nbt\tag\Short;
//use pocketmine\nbt\tag\String;
use pocketmine\utils\TextFormat as color;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\Entity;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\entity\Effect;

class Main extends PluginBase implements Listener{
   public $blm;
    public $f;
    public $blm2;
    public $blm3;
    public $f2;
    public $rew;
    public $rew3;
    public $rew2;
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->blm = array();
        $this->f = array();
        $this->blm3 = array();
        $this->rew = array();
        $this->rew3 = array();
        $this->rew2 = array();
    }
    public function onDisable() {
   
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

    }
    public function PRE(PlayerRespawnEvent $event){
   $event->getPlayer()->setMaxHealth(40);
   $event->getPlayer()->setHealth(40);       
    }

    public function PJE(PlayerJoinEvent $event){
 

        $task = new BLM($this, $event->getPlayer());
        $task2 = new BLM2($this, $event->getPlayer());
        $task3 = new BLM3($this, $event->getPlayer());
        $this->blm3[$event->getPlayer()->getName()] = $task3;
        $this->blm[$event->getPlayer()->getName()] = $task;
        if(array_search($event->getPlayer()->getName(), $this->rew)){}  else {
    $this->rew[$event->getPlayer()->getName()] = $task;
        $this->getServer()->getScheduler()->scheduleRepeatingTask($task, 15);
        }
        if(array_search($event->getPlayer()->getName(), $this->rew2)){}else{
    $this->rew2[$event->getPlayer()->getName()] = $task2;
            $this->getServer()->getScheduler()->scheduleRepeatingTask($task2, 20);
        }
        if(array_search($event->getPlayer()->getName(), $this->rew3)){}else{
    $this->rew3[$event->getPlayer()->getName()] = $task3;
            $this->getServer()->getScheduler()->scheduleRepeatingTask($task3, 15);
        }
        }
    public function EDE(EntityDamageEvent $event){
       
        if($event instanceof EntityDamageByEntityEvent){
            
        $p = $event->getDamager();
        if($p instanceof \pocketmine\entity\Snowball){
            $event->setDamage($event->getEntity()->getHealth() / 2);
            foreach ($this->getServer()->getOnlinePlayers() as $pla){
                $dist = $pla->distance($event->getEntity()->getPosition());
                if($dist < 10 && $pla->getInventory()->contains(new Item(339, 0, 1)) == TRUE){
                   $event->getEntity()->setLastDamageCause(new EntityDamageByEntityEvent($pla, $event->getEntity(), 1, 10)); 
                }
            }
            
        }
       
       if($p instanceof Player){
                  if($this->f[$p->getName()] != NULL && $this->f[$p->getName()] == TRUE && $p->getInventory()->getItemInHand()->getId() == 287){

                  }
           if($p->getInventory()->getItemInHand()->getId() == 287 && $this->f[$p->getName()] == TRUE){
            $rh = $this->blm3[$p->getName()];
               if($rh instanceof BLM3){
                   $rh->SetCharge(0);
                   $this->f[$p->getName()] = false;
               }
               $eee = $event->getEntity();
               if($eee instanceof Player){
                   if($eee->getInventory()->getItemInHand() != NULL){
                       $i = $eee->getInventory()->getItemInHand();
                   $eee->getInventory()->remove($i);
                   $eee->getLevel()->dropItem($eee->getPosition(), $i);
                       $eee->sendPopup(color::GREEN."^$$$#@&&(^*(HACKED*(*^#$%");
                       $p->sendPopup(color::GREEN."Hack SuccessFul!");
                       }
               }
           }
           $re = rand(5, 100);
           if($re <= 25){
               $event->setDamage($event->getDamage() + 4);
               $crt = new \pocketmine\level\particle\CriticalParticle($p->getPosition());
               $p->getLevel()->addParticle($crt);
               $i = 0;
               while($i <= 5){
                   $i++;
                   $p->getLevel()->addParticle($crt);
               }
           }
           if($this->f[$p->getName()] != null && $this->f[$p->getName()] == TRUE){
               $this->f[$p->getName()] = FALSE;
              
              $s = $this->blm[$p->getName()];
              if($s instanceof BLM3){
                  $s->SetCharge(0);
     
                  $fg = new \pocketmine\level\particle\ExplodeParticle($p->getPlayer()->getPosition());
                  $p->getPlayer()->getLevel()->addParticle($fg);
                  $event->setDamage($event->getDamage() * 2);
               //   $event->setKnockBack(1);
                  $p1 = new \pocketmine\level\particle\FloatingTextParticle($event->getEntity()->getPosition(), color::RED."@".$p->getName(), color::BOLD.color::RED." critcal");
                  $p4 = new FloatingTextTask($this, $p1, $event->getEntity()->getLevel());
                  $event->getEntity()->getLevel()->addParticle($p1);
                  $this->getServer()->getScheduler()->scheduleDelayedTask($p4, 25);
              }
               $a = new \pocketmine\level\sound\AnvilFallSound($p->getPosition());
               $p->getLevel()->addSound($a);
               
           }  else {
           $s = $this->blm[$p->getName()];
           if($s instanceof BLM){
               $s->SetCharge(0);
           }
           }
       }

       if($event->getBlock()->getId() != 0){
        if($event->getPlayer()->getInventory()->getItemInHand()->getId() == 369 && $this->f[$event->getPlayer()->getName()] == TRUE){
            $p = $event->getPlayer();   
            $b = $event->getBlock();
            $rh = $this->blm3[$p->getName()];
               if($rh instanceof BLM3){
                   $rh->SetCharge(0);
                   $this->f[$p->getName()] = false;
               }
               $pl = $event->getPlayer();
        $speed = 3;
$pos = $pl->getPosition();
$light = new AddEntityPacket();
        $light->type = 93;
        $light->eid = Entity::$entityCount++;
        $light->metadata = array();
        $light->speedX = 0;
        $light->speedY = 0;
        $light->speedZ = 0;
        $light->x = $b->x;
        $light->y = $b->y;
        $light->z = $b->z;
    $vec = new \pocketmine\math\Vector3($b->x, $b->y, $b->z);
       $pl->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $light);
       foreach ($this->getServer()->getOnlinePlayers() as $player){
           $dist = $player->distance($vec);
           if($dist <= 5){
               if($player->getName() == $pl->getName()){
                   $player->setDamage($event->getDamage() + 15);
                   $player->sendPopup(color::GREEN."#%^(*^%Hacked Ur Health#&^%$#");
               }  else {

             $player->addEffect(Effect::getEffect(9)->setAmplifier(3)->setDuration(100)->setVisible(false));
    
               $player->setHealth($player->getHealth() - 20);
               $player->setLastDamageCause(new EntityDamageByEntityEvent($event->getPlayer(), $player, 1, 4));
               $player->knockBack($event->getPlayer(), 1, 1, 1);
               $player->sendPopup(color::GREEN."&%^#@^&*&^%$#@#$%^&^%$#@%");
             }
           
           }
         }
       }
     }
   }

    
           }
public function PIE(PlayerInteractEvent $event){
    if($event->getBlock()->getId() != 0){
        if($event->getPlayer()->getInventory()->getItemInHand()->getId() == 369 && $this->f[$event->getPlayer()->getName()] == TRUE){
            $p = $event->getPlayer();   
            $b = $event->getBlock();
            $rh = $this->blm3[$p->getName()];
               if($rh instanceof BLM3){
                   $rh->SetCharge(0);
                   $this->f[$p->getName()] = false;
               }
               $pl = $event->getPlayer();
        $speed = 3;
$pos = $pl->getPosition();
//$dir = $pl->getDirectionVector();
//$frontPos = $pl->add($pl->getDirectionVector()->multiply(1.5));
//$dir->x = $dir->x * $speed;
//$dir->y = $dir->y * $speed;
//$dir->z = $dir->z * $speed;
$light = new AddEntityPacket();
        $light->type = 93;
        $light->eid = Entity::$entityCount++;
        $light->metadata = array();
        $light->speedX = 0;
        $light->speedY = 0;
        $light->speedZ = 0;
        $light->x = $b->x;
        $light->y = $b->y;
        $light->z = $b->z;
   //$e = $event->getPlayer();
   //$t = 0;
    
   // $t++;
    $vec = new \pocketmine\math\Vector3($b->x, $b->y, $b->z);
       $pl->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $light);
       foreach ($this->getServer()->getOnlinePlayers() as $player){
           $dist = $player->distance($vec);
           if($dist <= 5){
               if($player->getName() == $pl->getName()){
                   $player->setHealth($player->getHealth() + 5);
                   $player->sendPopup(color::GREEN."#%^(*^%Hacked Ur Health#&^%$#");
               }  else {

             $player->addEffect(Effect::getEffect(9)->setAmplifier(3)->setDuration(100)->setVisible(false));
    
               $player->setHealth($player->getHealth() - 10);
               $player->setLastDamageCause(new EntityDamageByEntityEvent($event->getPlayer(), $player, 1, 4));
               $player->knockBack($event->getPlayer(), 1, 1, 1);
               $player->sendPopup(color::GREEN."&%^#@^&*&^%$#@#$%^&^%$#@%");
           }}
       }
        }
        if($event->getPlayer()->getInventory()->getItemInHand()->getId() == 339){
            if($this->f[$event->getPlayer()->getName()] != NULL && $this->f[$event->getPlayer()->getName()] == TRUE){
             $ttt = $this->blm3[$event->getPlayer()->getName()];
             if($ttt instanceof BLM3){
             $ttt->SetCharge(0);
             }
             $pl = $event->getPlayer();
$player = $event->getPlayer();
        $speed = 3;
$pos = $player->getPosition();
$dir = $player->getDirectionVector();
$frontPos = $player->add($player->getDirectionVector()->multiply(1.5));
$dir->x = $dir->x * $speed;
$dir->y = $dir->y * $speed;
$dir->z = $dir->z * $speed;
$b = $event->getBlock();
$i = 0;
while ($i <= 15){
    $i++;
$nbt =
new Compound("",
["Pos" => new Enum("Pos",
[new Double("", rand($b->getX() - 5, $b->getX() + 5)),
new Double("", $pl->getY()+6),
new Double("", rand($b->getZ() - 5, $b->getZ() + 5))]),
"Motion" => new Enum("Motion",
[new Double("",0),
new Double("", 1),
new Double("",0)]),
"Rotation" => new Enum("Rotation",
[new Float("", 0),
new Float("", 0)])]);
Entity::createEntity("Snowball", $pos->getLevel()->getChunk($pos->x >> 4, $pos->z >> 4),$nbt)->spawnToAll();
}
        }}
    }  if($event->getBlock()->getId() != 0){
    $p = $event->getPlayer();
    $b = $event->getBlock();
       if($this->f[$p->getName()] != NULL && $this->f[$p->getName()] == TRUE && $p->getInventory()->getItemInHand()->getId() == 287){

     
}
    }
}

    public function PLE(ProjectileLaunchEvent $event){
     $player = $event->getEntity();
     foreach ($this->getServer()->getOnlinePlayers() as $p){
         $dist = $p->distance($player->getPosition());
         if($dist < 6 && $p->getInventory()->getItemInHand()->getId() == \pocketmine\item\Item::BOW && $p->getInventory()->contains(new Item(46, 0, 1)) == TRUE && $this->f[$p->getName()] == TRUE){
             $ttt = $this->blm[$p->getName()];
             if($ttt instanceof BLM){ $ttt->SetCharge(0);}
        $speed = 3;
$pos = $player->getPosition();
$dir = $player->getDirectionVector();
$frontPos = $player->add($player->getDirectionVector()->multiply(1.5));
$dir->x = $dir->x * $speed;
$dir->y = $dir->y * $speed;
$dir->z = $dir->z * $speed;
$nbt =
new Compound("",
["Pos" => new Enum("Pos",
[new Double("", $frontPos->x),
new Double("", $frontPos->y),
new Double("", $frontPos->z)]),
"Motion" => new Enum("Motion",
[new Double("",$dir->x),
new Double("",$dir->y),
new Double("",$dir->z)]),
"Rotation" => new Enum("Rotation",
[new Float("", 0),
new Float("", 0)])]);
Entity::createEntity("PrimedTNT", $pos->getLevel()->getChunk($pos->x >> 4, $pos->z >> 4),$nbt)->spawnToAll();
    $event->setCancelled();            
             }
     }

    }
        public function EPE(ExplosionPrimeEvent $event){
            $event->setForce($event->getForce() + 1);
            $event->setBlockBreaking(FALSE);
            
        }
        public function BPE(BlockPlaceEvent $event){
            if($event->getBlock()->getId() == \pocketmine\block\Block::TNT){
                if($event->getPlayer()->isOp()){                    return;}
                $event->setCancelled();
                $event->getPlayer()->sendMessage(color::RED."you cannot place tnt!");
                
            }
        }

}