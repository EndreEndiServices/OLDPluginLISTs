<?php

namespace SIvanov;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\scheduler\CallbackTask;
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\entity\Entity;
use pocketmine\entity\Effect;

class Classes extends PluginBase implements Listener
{
	private $eco, $cfg1, $cfg2;
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if(is_dir($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}
		$this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		$this->cfg1 = new Config($this->getDataFolder() . "classes.yml", Config::YAML);
		$this->cfg2 = new Config($this->getDataFolder() . "xp.yml", Config::YAML);
		$this->cfg3 = new Config($this->getDataFolder() . "level.yml", Config::YAML);
          $this->effects = array("15", "20", "19");
	}
	public function onPreLog(PlayerPreLoginEvent $e){
		$p = $e->getPlayer();
          $cid = $p->getClientId();
		$nick = strtolower($p->getName());
		if(!$this->cfg1->get($nick)){
			$this->cfg1->set($nick, "player");
			$this->cfg1->save();
		}
		if(!$this->cfg2->get($nick)){
			$this->cfg2->set($nick, 0);
			$this->cfg2->save();
		}
		if(!$this->cfg3->get($nick)){
			$this->cfg3->set($nick, 1);
			$this->cfg3->save();
          }
}

	public function onCheck(BlockBreakEvent $e){
         $p = $e->getPlayer();
         $name = strtolower($p->getName());
           $xp = array('0', '1', '0', '0');
	       $this->cfg2->set($name, $this->cfg2->get($name) + $xp[array_rand($xp)]);
			$this->cfg2->save();
     }

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		$p = $sender;
          $name = strtolower($p->getName());
		switch(strtolower($cmd->getName())){
             case "class":
               if(empty($args[0])){
                $sender->sendMessage("§8(§cClass§8)§f Используйте:§a /class <help, info, upgrade>§f.");
             return;
             }
             if($args[0] == "help"){
                $sender->sendMessage("§8(§cClass§8)§f Информация про классы:");
                $sender->sendMessage("§71. §6Рыцарь§f - при убийстве игрока вы получаете регенерацию 2 на 5 секунд.");
                $sender->sendMessage("§7      • §fСтоимость - §a12000 монет§f.");
                $sender->sendMessage("§7      • §fКупить - §e/class knignt§f.");

                $sender->sendMessage("§72. §6Воин§f - при ударе игрока, игрок получает больше  больше урона.");
                $sender->sendMessage("§7      • §fСтоимость - §a8000 монет§f.");
                $sender->sendMessage("§7      • §fКупить - §e/class warrior§f.");

                $sender->sendMessage("§73. §6Волшебник§f - игрок которого вы бьёте получает негативные эффекты.");
                $sender->sendMessage("§7      • §fСтоимость - §a15000 монет§f.");
                $sender->sendMessage("§7      • §fКупить - §a/class wizard§f.");

                $sender->sendMessage("§74. §6Защитник§f - вы получаете меньше урона при бою.");
                $sender->sendMessage("§7      • §fСтоимость - §a6000 монет§f.");
                $sender->sendMessage("§7      • §fКупить - §e/class defender§f.");
                }
             if($args[0] == "knight"){
           $balance = $this->eco->myMoney($sender);
            if($balance >= 12000){
            $sender->sendMessage("§8(§cClass§8)§f Ты купил класс §6Рыцарь§f.");
              $this->eco->reduceMoney($sender, 12000);
			$this->cfg1->set($name, knight);
			$this->cfg1->save();
            }else{
                $p->sendMessage("§8(§cClass§8)§f У тебя нету денег на покупку данного класса..");
                 }
              }
             if($args[0] == "warrior"){
           $balance = $this->eco->myMoney($sender);
            if($balance >= 8000){
            $sender->sendMessage("§8(§cClass§8)§f Ты купил класс §6Воин§f.");
              $this->eco->reduceMoney($sender, 8000);
			$this->cfg1->set($name, warrior);
			$this->cfg1->save();
            }else{
                $p->sendMessage("§8(§cClass§8)§f У тебя нету денег на покупку данного класса..");
                 }
              }
             if($args[0] == "wizard"){
           $balance = $this->eco->myMoney($sender);
            if($balance >= 15000){
            $sender->sendMessage("§8(§cClass§8)§f Ты купил класс §6Волшебник§f.");
              $this->eco->reduceMoney($sender, 15000);
			$this->cfg1->set($name, wizard);
			$this->cfg1->save();
            }else{
                $p->sendMessage("§8(§cClass§8)§f У тебя нету денег на покупку данного класса..");
                 }
              }
             if($args[0] == "defender"){
           $balance = $this->eco->myMoney($sender);
            if($balance >= 6000){
            $sender->sendMessage("§8(§cClass§8)§f Ты купил класс §6Защитник§f.");
              $this->eco->reduceMoney($sender, 6000);
			$this->cfg1->set($name, defender);
			$this->cfg1->save();
            }else{
                $p->sendMessage("§8(§cClass§8)§f У тебя нету денег на покупку данного класса..");
                 }
              }
             if($args[0] == "info"){
            $p->sendMessage("Твой класс: §b{$this->cfg1->get($name)}");
            $p->sendMessage("Уровень твоего класса: §h{$this->cfg3->get($name)}");
            $p->sendMessage("Твой опыт: §b{$this->cfg2->get($name)}"); 
            $p->sendMessage("§7Улучшить свой класс§e /class upgrade§f."); 
                }
             if($args[0] == "upgrade"){
             if($this->cfg3->get($name) == 1){
			if($this->cfg2->get($name) >= 10){
	          $this->cfg2->set($name, $this->cfg2->get($name) - 10);
			$this->cfg3->set($name, 2);
			$this->cfg2->save();
			$this->cfg3->save();
                $p->sendMessage("§8(§cClass§8)§f Ты улучшил уровень своего класса до§a 2§f.");
               return;
               }else{
                $p->sendMessage("§8(§cClass§8)§f Ты не можешь улучшить уровень своего класса до§a 2§f, тебе надо§a 10§f опыта.");
                   }
              }
             if($this->cfg3->get($name) == 2){
			if($this->cfg2->get($name) >= 25){
	          $this->cfg2->set($name, $this->cfg2->get($name) - 25);
			$this->cfg3->set($name, 3);
			$this->cfg2->save();
			$this->cfg3->save();
                $p->sendMessage("§8(§cClass§8)§f Ты улучшил уровень своего класса до§a 3§f.");
               return;
               }else{
                $p->sendMessage("§8(§cClass§8)§f Ты не можешь улучшить уровень своего класса до§a 3§f, тебе надо§a 25§f опыта.");
                    }
                 }

             if($this->cfg3->get($name) == 3){
			if($this->cfg2->get($name) >= 50){
	          $this->cfg2->set($name, $this->cfg2->get($name) - 50);
			$this->cfg3->set($name, 4);
			$this->cfg2->save();
			$this->cfg3->save();
                $p->sendMessage("§8(§cClass§8)§f Ты улучшил уровень своего класса до§a 4§f.");
               return;
               }else{
                $p->sendMessage("§8(§cClass§8)§f Ты не можешь улучшить уровень своего класса до§a 4§f, тебе надо§a 50§f опыта.");
                    }
                 }
             if($this->cfg3->get($name) == 4){
			if($this->cfg2->get($name) >= 75){
	          $this->cfg2->set($name, $this->cfg2->get($name) - 75);
			$this->cfg3->set($name, 5);
			$this->cfg2->save();
			$this->cfg3->save();
                 $p->sendMessage("§8(§cClass§8)§f Ты улучшил уровень своего класса до§a 5§f.");
               return;
               }else{
                $p->sendMessage("§8(§cClass§8)§f Ты не можешь улучшить уровень своего класса до§a 5§f, тебе надо§a 75§f опыта.");
                    }
                 }
             if($this->cfg3->get($name) == 5){
                 $p->sendMessage("§8(§cClass§8)§f Ты улучшил уровени на максимум§f.");
               return;
                 }
                }
          }
	}

 public function onDamage(EntityDamageEvent $event){
$entity = $event->getEntity();
$damager = $event->getDamager();
$name1 = strtolower($entity->getName());
$name2 = strtolower($damager->getName());



   if($this->cfg1->get($name2) == "warrior"){
    if($this->cfg3->get($name2) == 1){
   $entity->setHealth($entity->getHealth() - 2);
    }
    if($this->cfg3->get($name2) == 2){
   $entity->setHealth($entity->getHealth() - 2.5);
    }
    if($this->cfg3->get($name2) == 3){
   $entity->setHealth($entity->getHealth() - 3);
    }
    if($this->cfg3->get($name2) == 4){
   $entity->setHealth($entity->getHealth() - 3.5);
  $entity->addEffect(Effect::getEffect(2)->setAmplifier(2)->setDuration(7 * 20));
    }
    if($this->cfg3->get($name2) == 5){
   $entity->setHealth($entity->getHealth() - 4);
   $entity->addEffect(Effect::getEffect(9)->setAmplifier(1)->setDuration(10 * 20));
     }
    }



   if($this->cfg1->get($name1) == "defender"){
    if($this->cfg3->get($name1) == 1){
   $event->setDamage(1.5);
    }
    if($this->cfg3->get($name1) == 2){
   $event->setDamage(2);
    }
    if($this->cfg3->get($name1) == 3){
   $event->setDamage(3.5);
    }
    if($this->cfg3->get($name1) == 4){
   $event->setDamage(4);
  $entity->addEffect(Effect::getEffect(2)->setAmplifier(3)->setDuration(12 * 20));
    }
    if($this->cfg3->get($name1) == 5){
   $event->setDamage(4.10);
   $entity->addEffect(Effect::getEffect(4)->setAmplifier(1)->setDuration(15 * 20));
     }
    }



   if($this->cfg1->get($name2) == "wizard"){
    if($this->cfg3->get($name2) == 1){
  $entity->addEffect(Effect::getEffect(18)->setAmplifier(1)->setDuration(18 * 20));
    }
    if($this->cfg3->get($name2) == 2){
  $entity->addEffect(Effect::getEffect(9)->setAmplifier(2)->setDuration(16* 20));
    }
    if($this->cfg3->get($name2) == 3){
  $entity->addEffect(Effect::getEffect(20)->setAmplifier(2)->setDuration(9 * 20));
  $damager->addEffect(Effect::getEffect(8)->setAmplifier(2)->setDuration(12 * 20));
    }
    if($this->cfg3->get($name2) == 4){
  $entity->addEffect(Effect::getEffect(1)->setAmplifier(3)->setDuration(17 * 20));
  $entity->addEffect(Effect::getEffect(9)->setAmplifier(2)->setDuration(10 * 20));
    }
    if($this->cfg3->get($name2) == 5){
  $entity->addEffect(Effect::getEffect(18)->setAmplifier(1)->setDuration(16 * 20));
  $entity->addEffect(Effect::getEffect(9)->setAmplifier(2)->setDuration(23 * 20));
    }
   }
  }

 public function onDeath(PlayerDeathEvent $event){
   $name = strtolower($event->getEntity()->getLastDamageCause()->getDamager()->getName());
   if($this->cfg1->get($name) == "knight"){
    if($this->cfg3->get($name) == 1){
   $event->getEntity()->getLastDamageCause()->getDamager()->addEffect(Effect::getEffect(10)->setAmplifier(2)->setDuration(5 * 20));
    }
    if($this->cfg3->get($name) == 2){
   $event->getEntity()->getLastDamageCause()->getDamager()->addEffect(Effect::getEffect(10)->setAmplifier(3)->setDuration(5 * 20));
    }
    if($this->cfg3->get($name) == 3){
   $event->getEntity()->getLastDamageCause()->getDamager()->addEffect(Effect::getEffect(10)->setAmplifier(3)->setDuration(10 * 20));
    }
    if($this->cfg3->get($name) == 4){
   $event->getEntity()->getLastDamageCause()->getDamager()->addEffect(Effect::getEffect(1)->setAmplifier(2)->setDuration(5 * 20));
   $event->getEntity()->getLastDamageCause()->getDamager()->addEffect(Effect::getEffect(10)->setAmplifier(3)->setDuration(5 * 20));
    }
    if($this->cfg3->get($name) == 5){
   $event->getEntity()->getLastDamageCause()->getDamager()->addEffect(Effect::getEffect(9)->setAmplifier(2)->setDuration(10 * 20));
   $event->getEntity()->getLastDamageCause()->getDamager()->addEffect(Effect::getEffect(10)->setAmplifier(3)->setDuration(5 * 20));
    }
   }
}

}