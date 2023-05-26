<?php

namespace CraftingTable;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\Effect;
use pocketmine\command\{Command, CommandSender};
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\scheduler\CallbackTask;
use pocketmine\utils\Color;
use pocketmine\math\Vector3;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\entity\Entity;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\event\server\ServerShutdownEvent;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent, PlayerChatEvent, PlayerDeathEvent, PlayerRespawnEvent};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\CriticalParticle;

class TaskK extends \pocketmine\scheduler\PluginTask{ 
public $down;

      public function __construct(API $plugin){
        parent::__construct($plugin);
        $this->p = $plugin; 
    }

public $re=0;

       public function onRun($currentTick){
     //      var_dump("rr");
           $this->re++;
           if($this->re == 2){
           $this->p->r = $this->p->r + 0.03;
            $this->p->hctf = $this->p->hctf+0.1;
           $this->re = 0;
          }
             if($this->p->h > 20){
               $this->down = 1;
             } 
             if($this->p->h < 0.5){
               $this->down = 0;
             }
            
            if($this->down == 0){
            $this->p->h = $this->p->h + 0.5;
            }
            if($this->down == 1){
            $this->p->h = $this->p->h - 0.5;
            }
            
            if($this->p->h2 > 20){
               $this->down = 1;
             } 
             if($this->p->h2 < 0.5){
               $this->down = 0;
             }
            
            if($this->down == 0){
            $this->p->h2 = $this->p->h2 + 0.5;
            }
            if($this->down == 1){
            $this->p->h2 = $this->p->h2 - 0.5;
            }

            $this->p->s = $this->p->s + 7;
            $this->p->s2 = $this->p->s2 + 7;
         $this->p->part2();
        }
}

class API extends PluginBase implements Listener{

	public $back = [];
	public $flyes = [];
	public $v = [];
	public $god = [];
	public $vip = [];
	public $premium = [];
	public $kit = [];
	public $hack = [];
	public $e;
	
	public $i=0;
	public $particle;
	public $particle2;
	public $s;
	public $h;
	public $h2 = 10;
	public $s2;
	public $conus;
	public $ctf;
	public $hctf = 0;
	public $r;

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->e = $this->getServer()->getPluginManager()->getPlugin("CT-Economy");
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "money")), 20 * 60 * 6.4);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "cast")), 20 * 60 * 1.9);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "game")), 20 * 60 * 8.2);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new TaskK($this),1);
	}
	
	public function check(){
		$tps = $this->getServer()->getTicksPerSecond();
		if($tps <= 15){
			foreach($this->getServer()->getOnlinePlayers() as $p){
				$p->close("","§cПерезагрзка сервера\n§fЗайдите на сервер через 10 секунд\nПерезагрузка нужна, что бы не было лагов");
			}
			$this->getServer()->shutdown();
		}
	}
	
	public function part2(){

                   $h2 = $this->h2;
                         $h = $this->h;
                        $s = $this->s;
    
                        $conus = $this->conus;
                         if($s > 3600){ $this->s = 0;}
                           $level = $this->getServer()->getLevelByName("world");
                    	   $a = cos(deg2rad($s/2))* 7;//the Base
                             $b = sin(deg2rad($s/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 64 + $h-2, 127 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());

                     $s2 =$this->s2;
                $h=$this->h;
                      if($s2 > 3600){ $this->s2 = 0;}
                           $level = $this->getServer()->getLevelByName("world");
                    	   $a = cos(deg2rad($s2/2))* 7;//the Base
                             $b = -sin(deg2rad($s2/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 64 + $h-2, 127 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());
                  

                          $level = $this->getServer()->getLevelByName("world");
                    	   $a = -cos(deg2rad($s2/2))* 7;//the Base
                             $b = -sin(deg2rad($s2/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 64 + $h-2, 127 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());

                          $level = $this->getServer()->getLevelByName("world");
                    	   $a = -cos(deg2rad($s2/2))* 7;//the Base
                             $b = sin(deg2rad($s2/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 64 + $h-2, 127 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());


               if($s > 3600){ $this->s = 0;}
                           $level = $this->getServer()->getLevelByName("world");
                    	   $a = cos(deg2rad($s/2))* 7;//the Base
                             $b = sin(deg2rad($s/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 64 + $h+3, 127 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());

                     $s2 =$this->s2;
                $h=$this->h;
                      if($s2 > 3600){ $this->s2 = 0;}
                           $level = $this->getServer()->getLevelByName("world");
                    	   $a = cos(deg2rad($s2/2))* 7;//the Base
                             $b = -sin(deg2rad($s2/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 64 + $h+3, 127 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());
                  

                          $level = $this->getServer()->getLevelByName("world");
                    	   $a = -cos(deg2rad($s2/2))* 7;//the Base
                             $b = -sin(deg2rad($s2/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 64 + $h+3, 127 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());

                          $level = $this->getServer()->getLevelByName("world");
                    	   $a = -cos(deg2rad($s2/2))* 7;//the Base
                             $b = sin(deg2rad($s2/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 64 + $h+3, 127 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());

/////////////////////////////////////////////
        //   $ctf = $this->ctf;
           $hctf = $this->hctf;
           $r = $this->r;
           if($hctf > 6.5){
               $this->hctf=0;
               $this->r = 0;
            }
                            $level = $this->getServer()->getLevelByName("world");
                    	   $a = cos(deg2rad($s/2))* (3.5-$r);//the Base
                             $b = sin(deg2rad($s/2))* (3.5-$r);//the highest
                           
                             $pos = new Vector3(-240 + $a, 79 + $hctf, 127 + $b);
                             $particle = new CriticalParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());

                  /*   $s2 =$this->s2;
                $h=$this->h;
                      if($s2 > 3600){ $this->s2 = 0;}
                           $level = $this->getServer()->getLevelByName("world");
                    	   $a = cos(deg2rad($s2/2))* 7;//the Base
                             $b = -sin(deg2rad($s2/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 5 + $h-7, -502 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());
                  

                          $level = $this->getServer()->getLevelByName("world");
                    	   $a = -cos(deg2rad($s2/2))* 7;//the Base
                             $b = -sin(deg2rad($s2/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 5 + $h-7, -502 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());

                          $level = $this->getServer()->getLevelByName("world");
                    	   $a = -cos(deg2rad($s2/2))* 7;//the Base
                             $b = sin(deg2rad($s2/2))* 7;//the highest
                             $pos = new Vector3(-240 + $a, 5 + $h-7, -502 + $b);
                             $particle = new FlameParticle($pos);
                             $level->addParticle($particle,  $level->getPlayers());*/


             /*$x = -843;
             $y = 110;
             $z = 773;
             $level = $this->getServer()->getLevelByName("sg");
             $center = new Vector3($x, $y, $z);
             $radius = 3.0;
             $count = 650;
             $r = mt_rand(0, 300);
             $g = mt_rand(0, 300);
             $b = mt_rand(0, 300);
             $center = new Vector3($x, $y + 2, $z); 
             $particle = new DustParticle($center, $r, $g, $b);
         //      for ($i = 0; $i < $count; $i++) {
               $pitch = (mt_rand() / mt_getrandmax() - 0.5) * M_PI;
               $yaw = mt_rand() / mt_getrandmax() * 2 * M_PI;
               $y = -sin($pitch);$delta = cos($pitch);
               $x = -sin($yaw) * $delta;
               $z = cos($yaw) * $delta;
               $v = new Vector3($x, $y, $z);
               $p = $center->add($v->normalize()->multiply($radius));
               $particle->setComponents($p->x, $p->y, $p->z);
               $level->addParticle($particle);*/


$x = -240;
             $y = 72;
             $z = 127;
             $level = $this->getServer()->getLevelByName("world");
             $center = new Vector3($x, $y, $z);
             $radius = 6.0;
             $count = 4;
             $r = mt_rand(0, 300);
             $g = mt_rand(0, 300);
             $b = mt_rand(0, 300);
             $center = new Vector3($x, $y + 2, $z); 
             $particle = new DustParticle($center, $r, $g, $b);
             for ($i = 0; $i < $count; $i++) {
               $pitch = (mt_rand() / mt_getrandmax() - 0.5) * M_PI;
               $yaw = mt_rand() / mt_getrandmax() * 2 * M_PI;
               $y = -sin($pitch);$delta = cos($pitch);
               $x = -sin($yaw) * $delta;
               $z = cos($yaw) * $delta;
               $v = new Vector3($x, $y, $z);
               $p = $center->add($v->normalize()->multiply($radius));
               $particle->setComponents($p->x, $p->y, $p->z);
               $level->addParticle($particle);

             }



           } 
	
	public function cast(){
		$this->check();
		$r = mt_rand(1,4);
		$rr = mt_rand(1,25);
		switch($rr){
			case 1:
			case 20:
			case 21:
			case 10:
				$m = "§7(§6Уведомление§7) §fТолько на этой неделе §cогромные скидки §fна привилегии\n§fУспей купить §aпривилегию со скидкой §eдо 70%";
			break;
			case 2:
				$m = "§7(§6Уведомление§7) §fПостроил дом ? Не забудь заприватить! §e/rg help";
			break;
			case 3:
				$m = "§7(§6Уведомление§7) §fНа сервере есть свадьбы! §e/marry help";
			break;
			case 4:
				$m = "§7(§6Уведомление§7) §fХочешь создать свой клан ? Напиши §e/c help";
			break;
			case 5:
				$m = "§7(§6Уведомление§7) §fНа спавне есть §aочень много §fполезного, например §dМагазин\n§fдля телепортации на спавн введи §e/spawn";
			break;
			case 6:
				$m = "§7(§6Уведомление§7) §fХочешь добыть §aресурсы ? Скорее беги в автошахту §e/mine";
			break;
			case 7:
			case 18:
			case 19:
				$m = "§7(§6Уведомление§7) §fНа сервере есть множество §aкрутых §fпривилегий, для информации введите §a/donate\n§fА купить можно на сайте §l§bpay.craftingtable.ru§r";
			break;
			case 8:
				$m = "§7(§6Уведомление§7) §fПостроил дом ? Не забудь поставить §e/sethome §f,что бы §cне потерять его!";
			break;
			case 9:
			case 16:
			case 17:
				$m = "§7(§6Уведомление§7) §fПокупка привилегий на сайте §l§bpay.craftingtable.ru§r";
			break;
			/*case 10:
				$m = "§7(§6Уведомление§7) §fТы ютубер или просто не любишь гриферов ? §aКупи §fпривилегию §cАнти-Грифер §fи снимай анти грифер шоу\nПокупка привилегий на сайте §l§bpay.craftingtable.ru§r";
			break;
			case 11:
				$m = "§7(§6Уведомление§7) §fХотите §cбанить §fигроков и следить за порядком ? §fПривилегия §bAdmin §fдля вас!\nПокупка привилегий на сайте §l§bpay.craftingtable.ru§r";
			break;*/
			case 12:
				$m = "§7(§6Уведомление§7) §fДля телепортации к §aдругим игрокам §fвведите §e/tpa <ник игрока>";
			break;
			case 13:
				$m = "§7(§6Уведомление§7) §fЧто бы перевести игроку §aденьги §fвведите §e/pay";
			break;
			case 14:
				$m = "§7(§6Уведомление§7) §fНа сервере есть §aварпы §e/warp";
			break;
			case 15:
				$m = "§7(§6Уведомление§7) §fПодписывайтесь на группу §9Вконтакте §l§evk.com/crafting_table§r";
			break;
			case 22:
				$m = "§7(§6Уведомление§7) §fКупите §c§lконсоль§r §fсервера и станьте §aвсемогущим§c! §fПокупка привилегий на сайте §l§bpay.craftingtable.ru§r";
			break;
			case 23:
			case 24:
			case 25:
			case 11:
				$m = "§7(§6Уведомление§7) §eВнимание, §fнаш новый айпи - §acraftingtable.ru §fи новый порт - §a19132 §fне забудь сохранить!";
			break;
		}
		if($r != 4){
			$this->getServer()->broadcastMessage($m);
		}else{
			$this->getServer()->broadcastTip($m);
		}
	}
	
	public function game(){
        $r = mt_rand(1,3);
        switch($r){
            case 1:
                //$this->type = 1;
                $this->otvet = mt_rand(900000000, 999999999);
                $this->nagrada = mt_rand(100,1000);
                $this->getServer()->broadcastMessage("§c------------------\n§7(§aЧат Игра§7) §fВведите число §e".$this->otvet." §fчто бы получить §a".$this->nagrada."$ \n§c------------------");
            break;
            case 2:
                //$this->type = 2;
                $one = mt_rand(1,1000);
                $two = mt_rand(1,1000);
                $this->otvet = $one + $two;
                $this->nagrada = mt_rand(100,1000);
                $this->getServer()->broadcastMessage("§c------------------\n§7(§aЧат Игра§7) §fВведите сколько будет §e".$one." + ".$two." §fчто бы получить §a".$this->nagrada."$ \n§c------------------");
            break;
            case 3:
                //$this->type = 3;
                $one = mt_rand(1,100);
                $two = mt_rand(1,100);
                $this->otvet = $one * $two;
                $this->nagrada = mt_rand(500,2500);
                $this->getServer()->broadcastMessage("§c------------------\n§7(§aЧат Игра§7) §fВведите сколько будет §e".$one." * ".$two." §fчто бы получить §a".$this->nagrada."$ \n§c------------------");
            break;
        }
        $this->game = 1;
    }

	public function perm($p, $c, $pp){
		$p->sendMessage("§7(§cСервер§7) §6Для доступа к команде §e/".$c->getName()." §6нужна привилегия не ниже §a".$pp."\n§6Купить привилегию можно на сайте §bpay.craftingtable.ru");
	}	
	
	public function count_files($dir){ 
		$c = 0; // количество файлов. Считаем с нуля
		$d = dir($dir); // 
		while($str = $d->read()){ 
			if($str{0} != '.'){ 
				if(is_dir($dir.'/'.$str)) $c += count_files($dir.'/'.$str); 
				else $c++; 
			}; 
		} 
		$d->close(); // закрываем директорию
		return $c; 
	}
	
	/*public function onJoin(PlayerPreLoginEvent $e){
		$p = $e->getPlayer();
		$n = strtolower($p->getName());
		if(file_exists($this->getServer()->getDataPath()."players/".$n.".dat")){
			//null
		}else{
			$c = count(glob($this->getServer()->getDataPath()."players/*"));
			$this->addKit($p, "Kit");
			$this->getServer()->broadcastMessage("§7(§cСервер§7) §fНа сервере новый игрок §c".$p->getName()." §fименно он становится §e".$c." игроком сервера");
		}
	}*/

	public function onQuit(PlayerQuitEvent $e){
		$p = $e->getPlayer();
		$n = $p->getName();
		if(isset($this->flyes[$n])){ $p->setAllowFlight(false); unset($this->flyes[$n]); }
		foreach($this->v as $p => $abc){
            foreach($this->getServer()->getOnlinePlayers() as $p2){
                $p2->showPlayer($p);
            }
        }

        unset($this->god[$p->getName()]);
		unset($this->v[$p->getName()]);
		$e->setQuitMessage("§7[§c-§7] §e".$e->getPlayer()->getName()." §fпокинул сервер");
	}
	
	public function money(){
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->e->addMoney($p->getName(), 100);
            $p->sendMessage("§7(§2Бонус§7)§f Вы получили бонус §a100$ §fза игру на сервере");
        }
    }
	
	public function onDeath(PlayerDeathEvent $e){
        $p = $e->getPlayer();
        if($p->hasPermission("cmd.back")){
            $this->back[$p->getName()] = new Vector3($p->getX(), $p->getY(), $p->getZ());
        }
        if($e->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent && $e->getEntity() instanceof Player){
            $d = $e->getEntity()->getLastDamageCause()->getDamager();
            $e->setDeathMessage("§7(§cУбийство§7) §fИгрок §e".$d->getName()." §fубил игрока §b".$p->getName());
			$item = Item::get(397, 3, 1);
			$item->setCustomName("§eГолова игрока§6 ".$p->getName());
			$p->getLevel()->dropItem(new Vector3($p->getX(), $p->getY(), $p->getZ()), $item);
        }
    }
	
	public function onChat(PlayerChatEvent $e){
        $m = $e->getMessage();
        $p = $e->getPlayer();
		$n = strtolower($p->getName());
        if($this->game == 1){
            if($this->otvet == $e->getMessage()){
                $this->game = 2;
                $this->getServer()->broadcastMessage("§7(§aЧат Игра§7) §fИгрок §b".$e->getPlayer()->getName()." §fвыйграл в чат игре");
				$this->e->addMoney($p, $this->nagrada);
                $e->setCancelled();
            }
        }elseif($e->getMessage() == $this->otvet && $this->game == 2){
            $p->sendMessage("§7(§aЧат Игра§7) §cВас уже опередили, попробуйте в следующий раз");
            $e->setCancelled();
        }
    }
	
	public function onDamage(EntityDamageEvent $e){
        if($e->getEntity() instanceof Player){
            $p = $e->getEntity();
			if(isset($this->god[$p->getName()])) $e->setCancelled();
            if($e instanceof EntityDamageByEntityEvent){
                $d = $e->getDamager();
                if(isset($this->god[$d->getName()]) && !$d->hasPermission("ag")){
                    $d->sendMessage("§7(§cЗащита§7) §cВ бессмертии бить нельзя");
                    $e->setCancelled();
                }
                if(isset($this->v[$d->getName()]) && !$d->hasPermission("ag")){
                    $d->sendMessage("§7(§cЗащита§7) §cВ невидимости бить нельзя");
                    $e->setCancelled();
                }
                if($d->getGamemode() == 1 && !$d->hasPermission("ag")){
                    $d->sendMessage("§7(§cЗащита§7) §cВ креативе бить нельзя");
                    $e->setCancelled();
                }
                if($d->getAllowFlight() == true && !$d->hasPermission("ag")){
                    $d->sendMessage("§7(§cЗащита§7) §cВ полете бить нельзя");
                    $e->setCancelled();
                }
                if(isset($this->god[$p->getName()]) && !$d->hasPermission("ag")){
                    $d->sendMessage("§7(§cЗащита§7) §cУ этого игрока режим бессмертия");
                    $e->setCancelled();
                }
            }
        }
    }
	

	public function onCommand(CommandSender $p, Command $c, $label, array $args){
		$n = $p->getName();
		$nn = strtolower($n);
		switch(strtolower($c->getName())){

			case "fly":
			if($p->hasPermission("cmd.fly")){
                    if($p->getAllowFlight() == true){
                        $p->setAllowFlight(false);
                        $p->sendMessage("§7(§eФлай§7) §fВы выключили режим полета");
                    }else{
                        $p->setAllowFlight(true);
                        $p->sendMessage("§7(§eФлай§7) §fВы включили режим полета");
                    }
                }else{
                    $this->perm($p, $c, "Флай");
                }
			break;
			
			case "chest":
				if($p->hasPermission("cmd.chest")){
					$p->sendMessage("Команда в разработке");
				}else{
					$this->perm($p, $c, "Вип");
				}
			break;

			case "back":
			if($p->hasPermission("cmd.back")){
                    if(isset($this->back[$p->getName()])){
                        $p->teleport($this->back[$p->getName()]);
                        $p->sendMessage("§7(§eТелепорт§7) §fТелепортация на место смерти...");
                    }else{
                        $p->sendMessage("§7(§eТелепорт§7) §cВы ещё не умирали");
                    }
                }else{
                    $this->perm($p, $c, "Флай");
                }
			break;

			case "food":
			if($p->hasPermission("cmd.food")){
                    $p->setHealth(20);
                    $p->sendMessage("§7(§eГолод§7) §fВы утолили свой §cголод");
                }else{
                    $this->perm($p, $c, "Вип");
                }
			break;

			case "heal":
			if($p->hasPermission("cmd.heal")){
                    $p->setHealth($p->getMaxHealth());
                    $p->sendMessage("§7(§eЖизни§7) §fВы пополнили свои §cжизни");
                }else{
                    $this->perm($p, $c, "Вип");
                }
			break;

			case "repair":
			if($p->hasPermission("cmd.repair")){
                    $i = $p->getInventory()->getItemInHand();
                    $i->setDamage(0);
                    $p->getInventory()->setItemInHand($i);
                    $p->sendMessage("§7(§cПочинка§7) §fВы починили предмет в руке");
                }else{
                    $this->perm($p, $c, "Вип");
                }
			break;
			
			case "brepair":
			if($this->e->myMoney($p->getName()) >= 500){
                    $i = $p->getInventory()->getItemInHand();
                    $i->setDamage(0);
                    $p->getInventory()->setItemInHand($i);
                    $p->sendMessage("§7(§cПочинка§7) §fВы починили предмет в руке");
					$this->e->reduceMoney($p->getName(), 500);
                }else{
                    $p->sendMessage("§7(§cПочинка§7) §cВам не хватает денег, проверить бананс: §6/money");
                }
			break;

			case "ench":
			if($p->hasPermission("cmd.ench")){
                    $i = $p->getInventory()->getItemInHand();
                    $ids = [0,1,2,3,4,5,6,7,34,16,17,18,19,21,34,32,33,35,48,49,50,51];
                    $i->addEnchantment((Enchantment::getEnchantment($ids[array_rand($ids)]))->setLevel(mt_rand(1,20)));
                    $p->getInventory()->setItemInHand($i);
                    $p->sendMessage("§7(§aЗачарование§7) §fВы зачаровали предмет в руке");
                }else{
                	$this->perm($p, $c, "Вип");
                }
			break;
			
			case "bench":
			if($this->e->myMoney($p->getName()) >= 1000){
                    $i = $p->getInventory()->getItemInHand();
                    $ids = [0,1,2,3,4,5,6,7,34,16,17,18,19,21,34,32,33,35,48,49,50,51];
                    $i->addEnchantment((Enchantment::getEnchantment($ids[array_rand($ids)]))->setLevel(mt_rand(1,20)));
                    $p->getInventory()->setItemInHand($i);
                    $p->sendMessage("§7(§aЗачарование§7) §fВы зачаровали предмет в руке");
					$this->e->reduceMoney($p->getName(), 1000);
                }else{
                	$p->sendMessage("§7(§aЗачарование§7) §cВам не хватает денег, проверить бананс: §6/money");
                }
			break;

			case "vip":
			if($p->hasPermission("cmd.vip")){
				$this->addKit($p, "Vip");
			}else{
				$this->perm($p, $c, "Вип");
			}
			break;

			case "dupe":
			if($p->hasPermission("cmd.dupe")){
                    $i = $p->getInventory()->getItemInHand();
                    $i->setCount(64);
                    $p->getInventory()->setItemInHand($i);
                    $p->sendMessage("§7(§aДюп§7) §fВы дюпнули предмет в руке");
                }else{
                    $this->perm($p, $c, "Премиум");
                }
			break;

			case "1000lvl":
			if($p->hasPermission("cmd.1000lvl")){
				$i = $p->getInventory()->getItemInHand();
                $ids = [0,1,2,3,4,5,6,7,34,16,17,18,19,21,34,32,33,35,48,49,50,51];
                foreach($ids as $id) {$i->addEnchantment((Enchantment::getEnchantment($id)->setLevel(1000)));
}
                $p->getInventory()->setItemInHand($i);
                $p->sendMessage("§7(§aЗачарование§7) §fВы зачаровали предмет в руке на уровеь §eбога");
			}else{
				$this->perm($p, $c, "Премиум");
			}
			break;

			case "god":
			if($p->hasPermission("cmd.god")){
                    if(isset($this->god[$p->getName()])){
                        unset($this->god[$p->getName()]);
                        $p->sendMessage("§7(§aБессмертие§7) §fВы выключили режим бога");
                    }else{
                        $this->god[$p->getName()] = 1;
                        $p->sendMessage("§7(§aБессмертие§7) §fВы включили режим бога");
                    }
                }else{
                    $this->perm($p, $c, "Премиум");
                }
			break;

			case "premium":
			if($p->hasPermission("cmd.premium")){
				$this->addKit($p, "Premium");
			}else{
				$this->perm($p, $c, "Премиум");
			}
			break;

			case "gm":
			case "gamemode":
			if($p->hasPermission("cmd.gm")){
                    switch($args[0]){
                        case "1":
                            $p->setGamemode(1);
                            $p->sendMessage("§7(§eРежимы§7) §fВы включили режим §eкреатива");
                        break;
                        case "2":
                            $p->setGamemode(2);
                            $p->sendMessage("§7(§eРежимы§7) §fВы включили режим §eприключения");
                        break;
                        case "3":
                            $p->setGamemode(3);
                            $p->sendMessage("§7(§eРежимы§7) §fВы включили режим §eнаблюдателя");
                        break;
                        case "0":
                            $p->setGamemode(0);
                            $p->sendMessage("§7(§eРежимы§7) §fВы включили режим §eвыживания");
                        break;
                        default: $p->sendMessage("§e/gm 1 §a - §fвключить режим §6креатива\n"
                                . "§e/gm 2 §a - §fвключить режим §6приключения\n"
                                . "§e/gm 3 §a - §fвключить режим §6наблюдателя\n"
                                . "§e/gm 0 §a - §fвключить режим §6выживания");
                    }
                }else{
                    $this->perm($p, $c, "Креатив");
                }
			break;

			case "speed":
			if($p->hasPermission("cmd.speed")){
				$p->sendMEssage("Команда в разработке");
			}else{
				$this->perm($p, $c, "Креатив");
			}
			break;

			case "s":
			if($p->hasPermission("cmd.s")){
                    if(isset($args[0])){
                        $p2 = $this->getServer()->getPlayer($args[0]);
                        if($p2 != null){
                            if($p2->isOp()){
                                $p->sendMessage("§7(§eТелепорт§7) §cВы не можете телепортироваться к создателю");
                            }else{
                                $x = $p2->getX();
                                $y = $p2->getY();
                                $z = $p2->getZ();
                                $p->teleport(new Vector3($x, $y, $z));
                                $p->sendMessage("§7(§eТелепорт§7) §fВы телепортировались к §e".$p2->getName());
                                //$p2->sendMessage("§7(§eТелепорт§7) §fК вам телепортировался §e".$p->getName());
                            }
                        }else{
                            $p->sendMessage("§7(§eТелепорт§7) §cТакого игрока нет на сервере");
                        }
                    }else{
                        $p->sendMessage("§7(§eТелепорт§7) §cТакого игрока нет на сервере");
                    }
                }else{
                    $this->perm($p, $c, "Модератор");
                }
			break;

			case "day":
			if($p->hasPermission("cmd.day")){
                    $p->getLevel()->setTime(3500);
                    $p->sendMessage("§7(§eВремя§7) §fВы сделали день");
                }else{
                    $this->perm($p, $c, "Модератор");
                }
			break;

			case "night":
			if($p->hasPermission("cmd.night")){
                    $p->getLevel()->setTime(14000);
                    $p->sendMessage("§7(§eВремя§7) §fВы сделали день");
                }else{
                    $this->perm($p, $c, "Модератор");
                }
			break;

			case "unfire":
			if($p->hasPermission("cmd.unfire")){
                    if(isset($args[0])){
                        $p2 = $this->getServer()->getPlayer($args[0]);
                        if($p2 != null){
                            if($p2->isOp()){
                                $p->sendMessage("§7(§6Огонь§7) §cНельзя тушить создателя");
                            }else{
                                $p2->setOnFire(1);
                                $p->sendMessage("§7(§6Огонь§7) §fВы потушили игрока §6".$p2->getName());
                            }
                        }else{
                            $p->sendMessage("§7(§6Огонь§7) §cТакой игрок не найден");
                        }
                    }else{
                        $p->sendMessage("§7(§6Огонь§7) §cТакой игрок не найден");
                    }
                }else{
                    $this->perm($p, $c, "Админ");
                }
			break;

			case "v":
			case "vanish":
			if($p->hasPermission("cmd.v")){
                    if(isset($this->v[$p->getName()])){
                        unset($this->v[$p->getName()]);
                        $p->sendMessage("§7(§bНевидимость§7) §fВы выключили невидимость");
                        foreach($this->getServer()->getOnlinePlayer() as $p2){
                            $p2->showPlayer($p);
                        }
                    }else{
                        $this->v[$p->getName()] = 1;
                        $p->sendMessage("§7(§bНевидимость§7) §fВы включили невидимость");
                        foreach($this->getServer()->getOnlinePlayer() as $p2){
                            $p2->hidePlayer($p);
                        }
                    }
                }else{
                    $this->perm($p, $c, "Админ");
                }
			break;

			case "cc":
			if($p->hasPermission("cmd.cc")){
                    $this->getServer()->broadcastMessage("\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n");
                    $this->getServer()->broadcastMessage("§7(§eЧат§7) §a".$p->getName()." §fочистил чат");
                }else{
                    $this->perm($p, $c, "Админ");
                }
			break;

			case "shock":
			if($p->hasPermission("cmd.shock")){
                    if(isset($args[0])){
                        $p2 = $this->getServer()->getPlayer($args[0]);
                        if($p2 != null){
                            if($p2->isOp()){
                                $p->sendMessage("§7(§6Молния§7) §cНельзя так делать");
                            }else{
                            	$pk = new AddEntityPacket();
	                            $pk->type = 93;
	                            $pk->eid = Entity::$entityCount++;
	                            $pk->metadata = array();
	                            $pk->speedX = 0;
	                            $pk->speedY = 0.5;
	                            $pk->speedZ = 0;
	                            $pk->yaw = 0;
	                            $pk->pitch = 0;
	                            $pk->x = $target->x;
	                            $pk->y = $target->y;
	                            $pk->z = $target->z;
	                            foreach ($this->getServer()->getOnlinePlayers() as $pl) {
	                                $pl->dataPacket($pk);
	                            }
                                $p2->setOnFire(5);
                                $p->sendMessage("§7(§6Молния§7) §fВы вызвали молнию игроку §6".$p2->getName());
                            }
                        }else{
                            $p->sendMessage("§7(§6Молния§7) §cТакой игрок не найден");
                        }
                    }else{
                        $p->sendMessage("§7(§6Молния§7) §cТакой игрок не найден");
                    }
                }else{
                    $this->perm($p, $c, "Анти Грифер");
                }
			break;

			case "fire":
			if($p->hasPermission("cmd.fire")){
                    if(isset($args[0])){
                        $p2 = $this->getServer()->getPlayer($args[0]);
                        if($p2 != null){
                            if($p2->isOp()){
                                $p->sendMessage("§7(§6Огонь§7) §cНельзя поджигать создателя");
                            }else{
                                $p2->setOnFire(60);
                                $p->sendMessage("§7(§6Огонь§7) §fВы подожгли игрока §6".$p2->getName());
                            }
                        }else{
                            $p->sendMessage("§7(§6Огонь§7) §cТакой игрок не найден");
                        }
                    }else{
                        $p->sendMessage("§7(§6Огонь§7) §cТакой игрок не найден");
                    }
                }else{
                    $this->perm($p, $c, "Анти Грифер");
                }
			break;

			case "prefix":
			if($p->hasPermission("cmd.prefix")){
				$p->sendMessage("Команда в разработке");
			}else{
                $this->perm($p, $c, "Анти Грифер");
            }
			break;

			case "freeze":
			if($p->hasPermission("cmd.freeze")){
				$p->sendMessage("Команда в разработке");
			}else{
                $this->perm($p, $c, "Анти Грифер");
            }
			break;

			case "addfly":
			if($p->hasPermission("cmd.addfly")){
                     if(isset($args[0])){
                        $p2 = $this->getServer()->getPlayer($args[0]);
                        if($p2 != null){
                            if($p2->isOp()){
                                $p->sendMessage("§7(§6Флай§7) §cНельзя дать флай создателю");
                            }else{
                                $p2->setAllowFlight(true);
                                $p2->sendMessage("§7(§6Флай§7) §fИгрок §b".$p->getName()." §fподарил тебе флай до перезагрузки");
                                $this->flyes[$p->getName()] == 1;
                                $p->sendMessage("§7(§6Флай§7) §fВы подарили флай игроку §6".$p2->getName());
                            }
                        }else{
                            $p->sendMessage("§7(§6Флай§7) §cТакой игрок не найден");
                        }
                    }else{
                        $p->sendMessage("§7(§6Флай§7) §cТакой игрок не найден");
                    }
                }else{
                    $this->perm($p, $c, "Властелин");
                }
			break;

			case "popup":
			if($p->hasPermission("cmd.title")){
				if(empty($this->title($nn))){
					if(isset($args[0])){
						$m = implode(" ", $args);
						foreach($this->getServer()->getOnlinePlayers() as $p2){
							$p2->sendPopup($m);
						}
						$p->sendMessage("§7(§6Сообщение§7) §fВы отправили §aпопуп §fвсем игрокам");
					}else{
						$p->sendMessage("§7(§6Сообщение§7) §cИспользование: §f/popup сообщение");
					}
				}else{
					$p->sendMessage("§7(§6Сообщение§7) §cВы можете делать это раз в перезагрузку");
				}
			}else{
				$this->perm($p, $c, "Властелин");
			}
			break;

			case "okraska":
            $i = $p->getItemInHand();
            if($i instanceof \pocketmine\item\LeatherCap || $i instanceof \pocketmine\item\LeatherTunic || $i instanceof \pocketmine\item\LeatherPants || $i instanceof \pocketmine\item\LeatherBoots){
                $i->setCustomColor(Color::getRGB(mt_rand(1,255), mt_rand(1,255), mt_rand(1,255)));
                $p->getInventory()->setItemInHand($i);
                $p->sendMessage("§7(§2Покраска§7) §fВы покрасили броню в §cрандомный §fцвет");
            }else{
            	$p->sendMessage("§7(§2Покраска§7) §cВозьмите в руку кожаную броню");
            }
            break;

            case "hack":
            case "ophack":
            case "hack++":
			if(!isset($this->hack[$p->getName()])){
				$r = mt_rand(1,999);
				$p->sendMessage("§7(§cВзлом§7) §fВам не удалось взломать админку. Попробуйте после перезагрузки. Ваше число §6".$r." §fдля взлома нужно число §a1000");
			}else{
				$p->sendMessage("§7(§cВзлом§7) §cВы уже взламывали. Попробуйте после перезагрузки");
				$this->hack[$p->getName()] = 1;
			}
			break;

			case "sleep":
                $p->sleepOn(new Vector3($p->getX(), $p->getY(), $p->getZ()));
                $p->sendMessage("§7(§eСон§7) §fВы успешно легли спать");
            break;

            case "spawn": 
                $x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getX();
                $y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getY();
                $z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getZ();
                $p->teleport(new Vector3($x, $y, $z));
                $p->sendMessage("§7(§eТелепорт§7) §fТелепортация на спавн...");
            break;

            case "size":
				switch($args[0]){
					case "min":
						$p->setDataProperty(Player::DATA_SCALE, Player::DATA_TYPE_FLOAT, 0.8);
						$p->sendMessage("§7(§eРост§7) §fВы стали §6маленьким");
					break;
					case "nor":
						$p->setDataProperty(Player::DATA_SCALE, Player::DATA_TYPE_FLOAT, 1);
						$p->sendMessage("§7(§eРост§7) §fВы стали §6обычным");
					break;
					case "big":
						$p->setDataProperty(Player::DATA_SCALE, Player::DATA_TYPE_FLOAT, 1.7);
						$p->sendMessage("§7(§eРост§7) §fВы стали §6большим");
					break;
					default:
						$p->sendMessage("§e/size min §a- §fстать §6маленьким\n"
								. "§e/size nor §a- §fстать §6обычным\n"
								. "§e/size big §a- §fстать §6большим");
					break;
				}
            break;

            case "click":
				$rr = mt_rand(1,4);
				if($rr == 1){
					$r = mt_rand(7,12);
					$this->e->addMoney($p->getName(), $r);
					$p->sendPopup("§c+".$r);
				}else{
					$r = mt_rand(1,6);
					$this->e->addMoney($p->getName(), $r);
					$p->sendPopup("§c+".$r);
				}
			break;

			case "donate":
                $p->sendMessage("§c------------------\n§eФлай §7- §f25 руб\n§eВип §7- §f65 руб\n§eПремиум §7- §f127 руб\n§eКреатив §7- §f165 руб\n§eМодератор §7- §f245 руб\n§eАдмин §7- §f325 руб\n§eАнти Грифер §7- §f675 руб\n§eВластелин §7- §f950\n§eЛегенда §7- §f1415 руб\n§eКонслоь §7- §f5000 руб\n§aПокупка на сайте §bpay.craftingtable.ru\n§c------------------");
            break;

            case "ci":
				$p->getInventory()->clearAll();
				$p->sendMessage("§7(§aОчистка§7) §fВы очистили свой инвентарь");
            break;

            case "kit":
            $this->addKit($p, "Kit");
            break;


		}
	}

    public function addKit($p, $k, $i = 0){
        $n = strtolower($p->getName());
        switch($k){
            case "Kit":
            if(empty($this->kit[$n])){
				$i = Item::get(272,0,1);
				$i->addEnchantment((Enchantment::getEnchantment(16)->setLevel(2)));
				$p->getInventory()->addItem($i);
                $ids = [274,273,275];
				$chars = [32,33,34];
				$i = Item::get(274,0,1);
				$i->addEnchantment((Enchantment::getEnchantment(32)->setLevel(1)));
				$i->addEnchantment((Enchantment::getEnchantment(34)->setLevel(2)));
				$p->getInventory()->addItem($i);
				$i = Item::get(273,0,1);
				$i->addEnchantment((Enchantment::getEnchantment(32)->setLevel(1)));
				$i->addEnchantment((Enchantment::getEnchantment(34)->setLevel(2)));
				$p->getInventory()->addItem($i);
				$i = Item::get(275,0,1);
				$i->addEnchantment((Enchantment::getEnchantment(32)->setLevel(1)));
				$i->addEnchantment((Enchantment::getEnchantment(34)->setLevel(2)));
				$p->getInventory()->addItem($i);
				
				$h = Item::get(298,0,1);
				$h->addEnchantment((Enchantment::getEnchantment(mt_rand(0,7))->setLevel(2)));
				$c = Item::get(299,0,1);
				$c->addEnchantment((Enchantment::getEnchantment(mt_rand(0,7))->setLevel(2)));
				$l = Item::get(300,0,1);
				$l->addEnchantment((Enchantment::getEnchantment(mt_rand(0,7))->setLevel(2)));
				$b = Item::get(301,0,1);
				$b->addEnchantment((Enchantment::getEnchantment(mt_rand(0,7))->setLevel(2)));
				$p->getInventory()->setHelmet($h);
                $p->getInventory()->setChestplate($c);
                $p->getInventory()->setLeggings($l);
                $p->getInventory()->setBoots($b);
				
				$i = Item::get(261,0,1);
				$i->addEnchantment((Enchantment::getEnchantment(19)->setLevel(1)));
				$p->getInventory()->addItem($i);
				$i = Item::get(262,0,32);
				$p->getInventory()->addItem($i);
				
				$i = Item::get(50,0,16);
				$p->getInventory()->addItem($i);
				$i = Item::get(98,0,64);
				$p->getInventory()->addItem($i);
				$i = Item::get(45,0,16);
				$p->getInventory()->addItem($i);
				$i = Item::get(61,0,1);
				$p->getInventory()->addItem($i);
				$i = Item::get(54,0,1);
				$p->getInventory()->addItem($i);
				$i = Item::get(58,0,1);
				$p->getInventory()->addItem($i);
				$i = Item::get(5,0,64);
				$p->getInventory()->addItem($i);
				$this->kit[$n] = 1;
				$p->sendMessage("§7(§aКит§7) §fВы получили §eстартовый §fнабор");
            }else{
				if($i == 0) $p->sendMessage("§7(§aКит§7) §cВы уже получали стартовый набор");
			}
            break;
        }
    }

}