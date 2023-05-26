<?php

namespace ServerCore;



use pocketmine\math\Vector3;

use pocketmine\level\particle\CriticalParticle;

use pocketmine\level\particle\HappyVillagerParticle;

use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\level\Explosion;

use pocketmine\scheduler\PluginTask;

use pocketmine\command\Command;

use pocketmine\command\CommandSender;

use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\player\PlayerDeathEvent;

use pocketmine\event\player\PlayerItemConsumeEvent as Eat;

use pocketmine\entity\Effect;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\Player;

use pocketmine\Server;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\utils\TextFormat;

use pocketmine\utils\Config;

use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\item\Item;

use pocketmine\event\player\PlayerPreLoginEvent;

use pocketmine\event\player\PlayerRespawnEvent;

use pocketmine\event\player\PlayerCommandPreprocessEvent;

use pocketmine\event\player\PlayerDropItemEvent;

use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\event\entity\EntityLevelChangeEvent;

use pocketmine\block\Block;







class Main extends PluginBase implements Listener{







public function onEnable(){



        $this->getServer()->loadLevel("pvp2");



        $this->getServer()->loadLevel("parkour");



        $this->getServer()->loadLevel("nt");

        

$this->getServer()->getLogger()->info(TextFormat::BLUE."[Core]Plugin Enabled!");



@mkdir($this->getDataFolder());



@mkdir($this->getDataFolder()."players/");



$this->getServer()->getPluginManager()->registerEvents($this,$this);

	$this->getServer()->getScheduler()->scheduleRepeatingTask(new Checker($this),40); 



}







public function task(){



$x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getX();



$y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getY() + 1.5;



$z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getZ();



$r = mt_rand(0,225);



$g = mt_rand(0,225);



$b = mt_rand(0,225);



$center = new Vector3($x,$y,$z);



               $radius = 0.5;



                $count = 10;



                $particle = new HappyVillagerParticle($center,$r,$g,$b);



                for($yaw = 0, $y = $center->y; $y < $center->y + 2; $yaw += (M_PI * 2) / 20, $y += 1 / 20){



  $x = -sin($yaw) + $center->x;



  $z = cos($yaw) + $center->z;



 $particle->setComponents($x, $y, $z);



 $this->getServer()->getDefaultLevel()->addParticle($particle);







}



}



public function e_damage(EntityDamageEvent $event){

  if(!($event instanceof EntityDamageByEntityEvent) or !($event->getDamager() instanceof Player)) return;

  $player = $event->getDamager();

  $player1 = $event->getEntity();

$event->setKnockBack(0.3);

  $block = $player->getLevel()->getBlock($player->subtract(0, 1));

  if($player->speed->y < 0){

    // player is attacking in air





$event->setKnockBack(0.4);



$center = new Vector3($player1->getX(),$player1->getY(),$player1->getZ());



                $radius = 1;



                $count = 3;



                $particle = new CriticalParticle($center);



                for($yaw = 2, $y = $center->y; $y < $center->y + 1; $yaw += (M_PI * 2) / 5, $y += 1 / 4){



  $x = -sin($yaw) + $center->x;



  $z = cos($yaw) + $center->z;



  $particle->setComponents($x, $y, $z);



  $event->getEntity()->getLevel()->addParticle($particle);



   }
                   $group = $this->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($player);

        $groupname = $group->getName();
	$_damage = 1;
	switch($groupname){
		case "guest":
		$_damage = $_damage + 3;
		break;
		case "user":
		$_damage = $_damage + 1.5;
		break;
		case "gamer":
		$_damage = $_damage + 2;
		break;
		case "member":
		$_damage = $_damage + 3.5;
		break;
		case "vip":
		$_damage = $_damage + 2.5;
		break;
          case "membervip":
    $_damage = $_damage + 3.5;
    break;
	}
    $event->setDamage((int) ($event->getDamage() + $_damage)); // magnify the damage

  

}

}









  public function onChange(EntityLevelChangeEvent $ev){

     $p = $ev->getEntity();

        if($p instanceof Player){


                   $group = $this->getOwner()->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($p);

        $groupname = $group->getName();
  if($groupname == "member"){
    $p->setMaxHealth(48);
    $p->setHealth(48);
  }
  if($groupname == "gamer"){
    $p->setHealth(44);
    $p->setMaxHealth(44);
  }
    if($groupname == "guest"){
    $p->setHealth(20);
    $p->setMaxHealth(20);
  }
    if($groupname == "user"){
    $p->setHealth(40);
    $p->setMaxHealth(40);
  }
    if($groupname == "leadadmin"){
    $p->setHealth(48);
    $p->setMaxHealth(48);
  }
    if($groupname == "owner"){
    $p->setHealth(52);
    $p->setMaxHealth(52);
  }
    if($groupname == "mod"){
    $p->setHealth(44);
    $p->setMaxHealth(44);
  }
    if($groupname == "helper"){
    $p->setHealth(44);
    $p->setMaxHealth(44);
  }
        if($groupname == "youtube"){
    $p->setHealth(44);
    $p->setMaxHealth(44);
  }
      if($groupname == "yt1"){
    $p->setHealth(48);
    $p->setMaxHealth(48);
  }

      



  }

}






}



