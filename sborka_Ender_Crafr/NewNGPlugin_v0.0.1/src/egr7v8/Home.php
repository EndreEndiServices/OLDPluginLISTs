<?php

namespace egr7v8;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\item\Item;

class Home extends PluginBase implements Listener {
	
	public function onLoad(){
        $this->saveDefaultConfig();
    }

	public function onEnable(){
		$this->getLogger()->info("Плагин включен");
		$this->getLogger()->info("До Нового Года " .$this->counter('2016-01-01 00:00:00'). ", а до Рождества " .$this->counter('2016-01-07 00:00:00'));
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$time = $this->getConfig()->getNested("interval") * 1200;
		$num = 0;
		foreach ($this->getConfig()->getNested("items") as $i) {
			$r = explode(":",$i);
			$this->itemdata[$num] = array("id" => $r[0],"meta" => $r[1],"amount" => $r[2]);
			$num++;
		}
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Task($this),$time);
	}
  
	public function counter($date){
	    $check_time = strtotime($date) - time();
	    if($check_time <= 0){
	        return false;
	    }

	    $days = floor($check_time/86400);
	    $hours = floor(($check_time%86400)/3600);
	    $minutes = floor(($check_time%3600)/60);
	    $seconds = $check_time%60; 

	    $str = '';
	    if($days > 0) $str .= $this->declension($days,array('день','дня','дней')).' ';
	    if($hours > 0) $str .= $this->declension($hours,array('час','часа','часов')).' ';
	    if($minutes > 0) $str .= $this->declension($minutes,array('минута','минуты','минут')).' ';
	    if($seconds > 0) $str .= $this->declension($seconds,array('секунда','секунды','секунд'));

	    return $str;
	}
	
	public function declension($digit,$expr,$onlyword=false){
		if(!is_array($expr)) $expr = array_filter(explode(' ', $expr));
		if(empty($expr[2])) $expr[2]=$expr[1];
		$i=preg_replace('/[^0-9]+/s','',$digit)%100;
		if($onlyword) $digit='';
		if($i>=5 && $i<=20) $res=$digit.' '.$expr[2];
		else
		{
			$i%=10;
			if($i==1) $res=$digit.' '.$expr[0];
			elseif($i>=2 && $i<=4) $res=$digit.' '.$expr[1];
			else $res=$digit.' '.$expr[2];
		}
		return trim($res);
	}
	
	public function time_particle(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		$p = $event->getPlayer()->getName();
		if($this->getConfig()->getNested("float")){
			$level = $event->getPlayer()->getLevel();
			$x = $this->getConfig()->getNested("float_x");
			$y = $this->getConfig()->getNested("float_y");
			$z = $this->getConfig()->getNested("float_z");
			$text = $this->getConfig()->getNested("float_text");
			$vector = new Vector3($x, $y, $z);
			$level->addParticle(new FloatingTextParticle($vector->add(0.5, 0.0, -0.5), "", $text));
		}
		if($this->getConfig()->getNested("join_message")){
			$text = $this->getConfig()->getNested("join_message_text");
			$text = str_replace("{TN}", $this->counter('2016-01-01 00:00:00'), $text);
			$text = str_replace("{TC}", $this->counter('2016-01-07 00:00:00'), $text);
			$player->sendMessage($text);
		}
		if($this->getConfig()->getNested("join_particle")){
			$level = $this->getServer()->getDefaultLevel();
			$X = $player->getX();
			$Y = $player->getY();
			$Z = $player->getZ();
			$x = round($X,1);
			$y = round($Y,1);
			$z = round($Z,1);
			$x = $x + 5;
			$y = $y + 12;
			$z = $z + 5;
			$center = new Vector3($x, $y, $z);
			$radius = 3.0;
			$count = 650;
			$r = mt_rand(0, 200);
			$g = mt_rand(0, 200);
			$b = mt_rand(0, 200);
			$center = new Vector3($x, $y + 1, $z);
			$particle = new DustParticle($center, $r, $g, $b);
			for ($i = 0; $i < $count; $i++) {
				$pitch = (mt_rand() / mt_getrandmax() - 0.5) * M_PI;
				$yaw = mt_rand() / mt_getrandmax() * 2 * M_PI;
				$y = -sin($pitch);
				$delta = cos($pitch);
				$x = -sin($yaw) * $delta;
				$z = cos($yaw) * $delta;
				$v = new Vector3($x, $y, $z);
				$p = $center->add($v->normalize()->multiply($radius));
				$particle->setComponents($p->x, $p->y, $p->z);
				$level->addParticle($particle);
			}
		}
	}
	
	public function gift() {
		if(count($this->getServer()->getOnlinePlayers()) >= 2){
			$array_players = $this->getServer()->getOnlinePlayers();
			$rand_player = array_rand($array_players, 1);
			$player = $array_players[$rand_player];
			$p = $array_players[$rand_player]->getName();
			$text = $this->getConfig()->getNested("santa_claus");
			$text = str_replace("{player}", $p, $text);
			$this->getServer()->broadcastMessage($text);
			$this->give($player);
		}
	}
	
	public function give($p) {
		$data = $this->randomitem();
		$item = new Item($data["id"],$data["meta"],$data["amount"]);
		$p->getInventory()->addItem($item);
	}
	
	public function randomitem() {
		return $this->itemdata[rand(0,(count($this->itemdata) - 1))];
	}

	public function onDisable(){
		$this->getLogger()->info("Плагин выключен");
	}
}
