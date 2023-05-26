<?php

namespace Animalworld;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\entity\Zombie;
use pocketmine\level\format\FullChunk;
use pocketmine\scheduler\CallbackTask;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\event\Entity\EntityShootBowEvent;
use pocketmine\level\Explosion;
use pocketmine\entity\Arrow;
use pocketmine\entity\Villager;
use pocketmine\level\particle\HeartParticle;
use pocketmine\utils\TextFormat;
use pocketmine\level\particle\PortalParticle;

class Animalworld extends PluginBase implements Listener{
	private $zombie;
	private $animals = array();
	private $player = array();
 	public $width = 0.4;  //宽度
	private $lz;
	private $nbt;
	private $dif = 1;
	//private $nighttype = array(32);
	private $nighttype = array(32,33,34);
	//private $nighttype = array(34,34,34);
	private $daytype = array(10,11,12,13);
	private $dog = 14;
	private $dogs = array();
	private $specialMobtype = array(38);
	
	public $hatred_r = 8;  //仇恨半径
	public $bomb = 0.5;//苦力怕爆炸范围
	public $animalcount = 40;//animalcount范围
	
	public $animalbirth = 10;  //动物出生间隔秒数
	public $animalbirth_r = 20;  //动物出生半径
	public $animalbirth_A = 30;  //动物出生总数限制
	public $animal_A = 0;//动物出生总数
	
	public $birth = 5;  //怪物出生间隔秒数
	public $birth_r = 20;  //怪物出生半径
	public $mobbirth_A = 100;  //怪物出生总数限制
	public $mob_A = 0;//怪物出生总数
	public $smobbirth_A = 50;  //特殊怪物出生总数限制
	public $smob_A = 0;//特殊怪物出生总数
	private $arrow = array();
		
	public function onEnable(){
		$this->getLogger()->info("Animalworld Is Loading!");

	
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		
		

		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"animalGenerate"]),20*$this->animalbirth);
		
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"clear"]),5.8);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"timereset"]),20);
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"AnimalRandomWalk"]),1);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"AnimalRandomWalkCalc"]),10);
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"MobRandomWalkCalc"]),10);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"MobRandomWalk"]),1);
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"SpecialMobRandomWalkCalc"]),50);
		//$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		//[$this,"MobRandomWalk"]),1);
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"DogRandomWalkCalc"]),10);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"DogRandomWalk"]),1);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"DogGenerate"]),50*$this->animalbirth);
		
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask( new CallbackTask(
		[$this,"AllRotation"]),1);
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"MOBGenerate"]),20*$this->birth);
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"SpecialMobGenerate"]),20*$this->birth +  1);
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"ArrowCalc"]),1);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(
		[$this,"Change"]),60);
		$this->getLogger()->info("Animalworld Loaded !!!!");

	}

	public function Change(){
	foreach ($this->getServer()->getOnlinePlayers() as $pl) {
		if(isset($this->player[$pl->getName()])){
			$pp = &$this->player[$pl->getName()];
			$pp["time"] = $pp["time"] +1;
			if($pp["time"] <= 10){
			$ann = $this->animals;
			$filter_res = array_filter($ann);
			if(!empty($filter_res))
				foreach ($this->animals as $animal){
					if($animal['type'] == 38){
						$pk2 = new RemoveEntityPacket;
				$pk2->eid = $animal['ID'];		
				$pk3 = new AddEntityPacket;
				$pk3->eid = $animal['ID'];
				$pk3->type = $animal['type'];
				$pk3->x = $animal['x'];
				$pk3->y = $animal['y'] +2;
				$pk3->z = $animal['z'];
				$pk3->pitch = $animal['pitch'];
				$pk3->yaw = $animal['yaw'];
				$pk3->metadata = [];
				
				$pl->dataPacket($pk2);
				$pl->dataPacket($pk3);
					}elseif($animal['type'] == 14){
				//var_dump("普通狗");		
				$pk2 = new RemoveEntityPacket;
				$pk2->eid = $animal['ID'];		
				$pk3 = new AddEntityPacket;
				$pk3->eid = $animal['ID'];
				$pk3->type = $animal['type'];
				$pk3->x = $animal['x'];
				$pk3->y = $animal['y']+1;
				$pk3->z = $animal['z'];
				$pk3->pitch = $animal['pitch'];
				$pk3->yaw = $animal['yaw'];
				$pk3->metadata = [];
				
				$pl->dataPacket($pk2);
				$pl->dataPacket($pk3);	
					}else{
				//var_dump("普通");		
				$pk2 = new RemoveEntityPacket;
				$pk2->eid = $animal['ID'];		
				$pk3 = new AddEntityPacket;
				$pk3->eid = $animal['ID'];
				$pk3->type = $animal['type'];
				$pk3->x = $animal['x'];
				$pk3->y = $animal['y'];
				$pk3->z = $animal['z'];
				$pk3->pitch = $animal['pitch'];
				$pk3->yaw = $animal['yaw'];
				$pk3->metadata = [];
				
				$pl->dataPacket($pk2);
				$pl->dataPacket($pk3);	
					}	
				}					
				}
			}
		}
	}
	
	public function getNBT() {
		$nbt = new Compound("", [
			"Pos" => new Enum("Pos", [
				new Double("", 0),
				new Double("", 0),
				new Double("", 0)
			]),
			"Motion" => new Enum("Motion", [
				new Double("", 0),
				new Double("", 0),
				new Double("", 0)
			]),
			"Rotation" => new Enum("Rotation", [
				new Float("", 0),
				new Float("", 0)
			]),
		]);
		return $nbt;
	}

	public function ArrowCalc() {
	$filter_res = array_filter($this->arrow);
	if(!empty($filter_res))
		foreach ($this->arrow as $arrow){
			$level=$this->getServer()->getLevelByName($arrow['level']);
			if($arrow['level'] != false){
			foreach($level->getEntities() as $ents){
				if($ents->getId() == $arrow['ID']){
					//var_dump($ents->getId()."|".$arrow['ID']);
					$arr = $level->getEntity($arrow['ID']);
					$a = &$this->arrow[$arrow['ID']];
					$pos=new Vector3($arr->getX(), $arr->getY(), $arr->getZ());
						foreach($arr->getViewers() as $p) {  //获取附近玩家
						if($p->distance($pos) <= 1){
						$p->attack(2);
						//var_dump("距离:".$p->distance($pos));
						}
					}
				}else{
			unset($this->arrow[$arrow['ID']]);
			}
		}
	}
	}
	}
	
	public function getLight(Level $level,$pos) {
		$chunk = $level->getChunk($pos->x >> 4, $pos->z >> 4, false);
		$l = 0;
		if($chunk instanceof FullChunk){
			$l = $chunk->getBlockSkyLight($pos->x & 0x0f, $pos->y & 0x7f, $pos->z & 0x0f);
			if($l < 15){
				//$l = \max($chunk->getBlockLight($pos->x & 0x0f, $pos->y & 0x7f, $pos->z & 0x0f));
				$l = $chunk->getBlockLight($pos->x & 0x0f, $pos->y & 0x7f, $pos->z & 0x0f);
			}
		}
		return $l;
	}

	public function AllRotation(){
		$ann = $this->animals;
	$filter_res = array_filter($ann);
		if(!empty($filter_res))
			foreach ($this->animals as $animal){
						$level=$this->getServer()->getLevelByName($animal['level']);
						$zo = $level->getEntity($animal['ID']);
						if($zo != ""){
						$zom = &$this->animals[$zo->getId()];
							$yaw0 = $zo->yaw;  //实际yaw
							$yaw = $zom['yaw']; //目标yaw
							//$this->getLogger()->info($yaw0.' '.$yaw);
							if (abs($yaw0 - $yaw) <= 180) {  //-180到+180正方向
								if ($yaw0 <= $yaw) {  //实际在目标左边
									if ($yaw - $yaw0 <= 15) {
										$yaw0 = $yaw;
									}
									else {
										$yaw0 += 15;
									}
								}
								else {  ////实际在目标右边
									if ($yaw0 - $yaw <= 15) {
										$yaw0 = $yaw;
									}
									else {
										$yaw0 -= 15;
									}
								}
							}
							else {  ////+180到-180方向
								if ($yaw0 >= $yaw) {  //实际在目标左边
									if ((180-$yaw0) + ($yaw+180) <= 15) {
										$yaw0 = $yaw;
									}
									else {
										$yaw0 += 15;
										if ($yaw0 >= 180) $yaw0 = $yaw0 - 360;
									}
								}
								else {  ////实际在目标右边
									if ((180-$yaw) - ($yaw0+180) <= 15) {
										$yaw0 = $yaw;
									}
									else {
										$yaw0 -= 15;
										if ($yaw0 <= 180) $yaw0 = $yaw0 + 360;
									}
								}
							}
							$pitch0 = $zo->pitch;  //实际pitch
							$pitch = $zom['pitch']; //目标pitch

							if (abs($pitch0-$pitch) <= 15) {
								$pitch0 = $pitch;
							}
							elseif ($pitch > $pitch0) {
								$pitch0 += 10;
							}
							elseif ($pitch < $pitch0) {
								$pitch0 -= 10;
							}

							$zo->setRotation($yaw0,$pitch0);
						}
		}
	}

	public function PlayerQuit(PlayerQuitEvent $event){
	$pl = $event->getPlayer();
	unset($this->player[$pl->getName()]);
	}
	
	public function DogRandomWalk(){
	$dog = $this->dogs;
	$filter_res = array_filter($dog);	
	if(!empty($filter_res))
		foreach ($this->dogs as $dog){	
			if($dog["owner"] != false){
				//var_dump($dog["owner"]);
				$p = $this->getServer()->getPlayer($dog["owner"]);
				if (($p instanceof Player) === false){
				
				}else{
					$level=$this->getServer()->getLevelByName($dog['level']);
					$zo = $level->getEntity($dog['ID']);
					if($zo != false){
						$zom = &$this->dogs[$zo->getId()];
								$x1 = $zo->getX() - $p->getX();

								//$jumpy = $zo->getY() - 1;

								if($x1 >= -0.5 and $x1 <= 0.5) { //直行
									//$zx = $zo->getX();
									$xxx = 0;
								}
								elseif($x1 < 0){
									//$zx = $zo->getX() +0.07;
									$xxx =0.07;
								}else{
									//$zx = $zo->getX() -0.07;
									$xxx = -0.07;
								}

								$z1 =$zo->getZ () - $p->getZ() ;
								if($z1 >= -0.5 and $z1 <= 0.5) { //直行
									//$zZ = $zo->getZ();
									$zzz = 0;
								}
								elseif($z1 <0){
									//$zZ = $zo->getZ() +0.07;
									$zzz =0.07;
								}else{
									//$zZ = $zo->getZ() -0.07;
									$zzz =-0.07;
								}

								if ($xxx == 0 and $zzz == 0) {
									$xxx = 0.1;
								}

								$zom['xxx'] = $xxx * 10;
								$zom['zzz'] = $zzz * 10;

								//计算y轴
								//$width = $this->width;
								$pos0 = new Vector3 ($zo->getX(), $zo->getY() + 1 ,$zo->getZ());  //原坐标
								$pos = new Vector3 ($zo->getX()+ $xxx, $zo->getY() + 1,$zo->getZ() + $zzz);  //目标坐标
								$zy = $this->ifjump($zo->getLevel(), $pos, true);

								if ($zy === false) {  //前方不可前进
									//伪自由落体
									if ($this->ifjump($zo->getLevel(),$pos0, true) === false) { //原坐标依然是悬空
										$pos2 = new Vector3 ($zo->getX(), $zo->getY() - 2,$zo->getZ());  //下降
										$zom['up'] = 1;
										$zom['yup'] = 0;
										//var_dump("2");
									}
									else {
										if ($this->whatBlock($level,$pos0) == "climb") {  //梯子
											$zy = $pos0->y + 0.07;
											$pos2 = new Vector3 ($zo->getX(), $zy - 1, $zo->getZ());  //目标坐标
										}
										elseif ($xxx != 0 and $zzz != 0) {  //走向最近距离
											if ($this->ifjump($zo->getLevel(), new Vector3($zo->getX()+ $xxx, $zo->getY() + 1,$zo->getZ()), true) !== false) {
												$pos2 = new Vector3($zo->getX() + $xxx, floor($zo->getY()),$zo->getZ());  //目标坐标
											}
											elseif ($this->ifjump($zo->getLevel(), new Vector3($zo->getX(), $zo->getY() + 1,$zo->getZ()+$zzz), true) !== false) {
												$pos2 = new Vector3($zo->getX(), floor($zo->getY()),$zo->getZ() + $zzz);  //目标坐标
											}
											else {
												$pos2 = new Vector3 ($zo->getX() - $xxx, floor($zo->getY()),$zo->getZ() - $zzz);  //目标坐标
												//转向180度，向身后走
												$zom['up'] = 0;
											}
										}
										else {
											$pos2 = new Vector3 ($zo->getX() - $xxx, floor($zo->getY()),$zo->getZ() - $zzz);  //目标坐标
											//转向180度，向身后走
											$zom['up'] = 0;
										}
									}
								}
								else {
									$pos2 = new Vector3 ($zo->getX()+ $xxx, $zy - 1, $zo->getZ() + $zzz);  //目标坐标
									//echo $zy;
									$zom['up'] = 0;
									if ($this->whatBlock($level, $pos2) == "water") {
										$zom['swim'] += 1;
										if ($zom['swim'] >= 20) $zom['swim'] = 0;
									}
									else {
										$zom['swim'] = 0;
									}
									//var_dump("目标:".($zy - 1) );
									//var_dump("原先:".$zo->getY());
									if(abs(($zy - 1) - floor($zo->getY())) == 1){
										//var_dump("跳");
										$zom['jump'] = 0.5;
									}
									else {
										if ($zom['jump'] > 0.01) {
											$zom['jump'] -= 0.1;
										}
										else {
											$zom['jump'] = 0.01;
										}
									}
								}

								$zo->setPosition($pos2);

								$yaw = $this->getyaw($xxx, $zzz);
								$pos3 = $pos2;
								$pos3->y = $pos3->y + 2.62;
								$ppos = $p->getLocation();
								$ppos->y = $ppos->y + $p->getEyeHeight();
								$pitch = $this->getpitch($pos3,$ppos);

								$zom['x'] = $zo->getX();
								$zom['y'] = $zo->getY();
								$zom['z'] = $zo->getZ();
								$zom['yaw'] = $yaw;
								$zom['pitch'] = $pitch;
								$pk3 = new SetEntityMotionPacket;
								$pk3->entities = [
									[$zo->getID(), $xxx, - $zom['swim'] / 100 + $zom['jump'] , $zzz]
								];
								foreach($zo->getViewers() as $pl){
									$pl->dataPacket($pk3);
								}	
					}
					
				}
				}else{
						//unset();
			}
		}
	}

	public function DogRandomWalkCalc(){
		$this->dif = $this->getServer()->getDifficulty();
		$ann = $this->animals;
			$filter_res = array_filter($ann);
			if(!empty($filter_res))
				foreach ($this->dogs as $animal) {
					$dog = &$this->dogs[$animal['ID']];
					if($dog['love'] != 9999){
						$level=$this->getServer()->getLevelByName($animal['level']);
						$an = $level->getEntity($animal['ID']);
				//var_dump($animal);
						if($an != ""){
						$zom = &$this->animals[$an->getId()];
						if ($this->willMove($an)) {
						$zo = $level->getEntity($animal['ID']);
						if ($zom['IsChasing'] == "0") {  //自由行走模式
							if ($zom['gotimer'] == 0 or $zom['gotimer'] == 10) {
								//限制转动幅度
								$newmx = mt_rand(-5,5)/10;
								while (abs($newmx - $zom['motionx']) >= 0.7) {
									$newmx = mt_rand(-5,5)/10;
								}
								$zom['motionx'] = $newmx;

								$newmz = mt_rand(-5,5)/10;
								while (abs($newmz - $zom['motionz']) >= 0.7) {
									$newmz = mt_rand(-5,5)/10;
								}
								$zom['motionz'] = $newmz;
							}
							elseif ($zom['gotimer'] >= 20 and $zom['gotimer'] <= 24) {
								$zom['motionx'] = 0;
								$zom['motionz'] = 0;
								//僵尸停止
							}

							$zom['gotimer'] += 0.5;
							if ($zom['gotimer'] >= 22) $zom['gotimer'] = 0;  //重置走路计时器

							//$zom['motionx'] = mt_rand(-10,10)/10;
							//$zom['motionz'] = mt_rand(-10,10)/10;
							$zom['yup'] = 0;
							$zom['up'] = 0;

							//boybook的y轴判断法
							//$width = $this->width;
							$pos = new Vector3 ($zom['x'] + $zom['motionx'], floor($zo->getY()) + 1,$zom['z'] + $zom['motionz']);  //目标坐标
							$zy = $this->ifjump($zo->getLevel(),$pos);
							if ($zy === false) {  //前方不可前进
								$pos2 = new Vector3 ($zom['x'], $zom['y'] ,$zom['z']);  //目标坐标
								if ($this->ifjump($zo->getLevel(),$pos2) === false) { //原坐标依然是悬空
									$pos2 = new Vector3 ($zom['x'], $zom['y']-1,$zom['z']);  //下降
									$zom['up'] = 1;
									$zom['yup'] = 0;
								}
								else {
									$zom['motionx'] = - $zom['motionx'];
									$zom['motionz'] = - $zom['motionz'];
									//转向180度，向身后走
									$zom['up'] = 0;
								}
							}
							else {
								$pos2 = new Vector3 ($zom['x'] + $zom['motionx'], $zy - 1 ,$zom['z'] + $zom['motionz']);  //目标坐标
								if ($pos2->y - $zom['y'] < 0) {
									$zom['up'] = 1;
								}
								else {
									$zom['up'] = 0;
								}
							}

							if ($zom['motionx'] == 0 and $zom['motionz'] == 0) {  //僵尸停止
							}
							else {
								//转向计算
								$yaw = $this->getyaw($zom['motionx'], $zom['motionz']);
								//$zo->setRotation($yaw,0);
								$zom['yaw'] = $yaw;
								$zom['pitch'] = 0;
							}

							//更新僵尸坐标
							$zom['x'] = $pos2->getX();
							$zom['z'] = $pos2->getZ();
							$zom['y'] = $pos2->getY();
							$zom['motiony'] = $pos2->getY() - $zo->getY();
							//echo($zo->getY()."\n");
							//var_dump($pos2);
							//var_dump($zom['motiony']);
							$zo->setPosition($pos2);
							$animal = $zom;
							//echo "SetPosition \n";
						}
					}
				}
			}else{
				if($dog["owner"] != false){
				//var_dump($dog["owner"]);
				$p = $this->getServer()->getPlayer($dog["owner"]);
				if (($p instanceof Player) === false){
				
				}else{
					$level=$this->getServer()->getLevelByName($dog['level']);
					$zo = $level->getEntity($dog['ID']);
					if($zo != false){
						$zom = &$this->dogs[$zo->getId()];
								$x1 = $zo->getX() - $p->getX();

								//$jumpy = $zo->getY() - 1;

								if($x1 >= -0.5 and $x1 <= 0.5) { //直行
									//$zx = $zo->getX();
									$xxx = 0;
								}
								elseif($x1 < 0){
									//$zx = $zo->getX() +0.07;
									$xxx =0.07;
								}else{
									//$zx = $zo->getX() -0.07;
									$xxx = -0.07;
								}

								$z1 =$zo->getZ () - $p->getZ() ;
								if($z1 >= -0.5 and $z1 <= 0.5) { //直行
									//$zZ = $zo->getZ();
									$zzz = 0;
								}
								elseif($z1 <0){
									//$zZ = $zo->getZ() +0.07;
									$zzz =0.07;
								}else{
									//$zZ = $zo->getZ() -0.07;
									$zzz =-0.07;
								}

								if ($xxx == 0 and $zzz == 0) {
									$xxx = 0.1;
								}

								$zom['xxx'] = $xxx * 10;
								$zom['zzz'] = $zzz * 10;

								//计算y轴
								//$width = $this->width;
								$pos0 = new Vector3 ($zo->getX(), $zo->getY() + 1 ,$zo->getZ());  //原坐标
								$pos = new Vector3 ($zo->getX()+ $xxx, $zo->getY() + 1,$zo->getZ() + $zzz);  //目标坐标
								$zy = $this->ifjump($zo->getLevel(), $pos, true);

								if ($zy === false) {  //前方不可前进
									//伪自由落体
									if ($this->ifjump($zo->getLevel(),$pos0, true) === false) { //原坐标依然是悬空
										$pos2 = new Vector3 ($zo->getX(), $zo->getY() - 2,$zo->getZ());  //下降
										$zom['up'] = 1;
										$zom['yup'] = 0;
										//var_dump("2");
									}
									else {
										if ($this->whatBlock($level,$pos0) == "climb") {  //梯子
											$zy = $pos0->y + 0.07;
											$pos2 = new Vector3 ($zo->getX(), $zy - 1, $zo->getZ());  //目标坐标
										}
										elseif ($xxx != 0 and $zzz != 0) {  //走向最近距离
											if ($this->ifjump($zo->getLevel(), new Vector3($zo->getX()+ $xxx, $zo->getY() + 1,$zo->getZ()), true) !== false) {
												$pos2 = new Vector3($zo->getX() + $xxx, floor($zo->getY()),$zo->getZ());  //目标坐标
											}
											elseif ($this->ifjump($zo->getLevel(), new Vector3($zo->getX(), $zo->getY() + 1,$zo->getZ()+$zzz), true) !== false) {
												$pos2 = new Vector3($zo->getX(), floor($zo->getY()),$zo->getZ() + $zzz);  //目标坐标
											}
											else {
												$pos2 = new Vector3 ($zo->getX() - $xxx, floor($zo->getY()),$zo->getZ() - $zzz);  //目标坐标
												//转向180度，向身后走
												$zom['up'] = 0;
											}
										}
										else {
											$pos2 = new Vector3 ($zo->getX() - $xxx, floor($zo->getY()),$zo->getZ() - $zzz);  //目标坐标
											//转向180度，向身后走
											$zom['up'] = 0;
										}
									}
								}
								else {
									$pos2 = new Vector3 ($zo->getX()+ $xxx, $zy - 1, $zo->getZ() + $zzz);  //目标坐标
									//echo $zy;
									$zom['up'] = 0;
									if ($this->whatBlock($level, $pos2) == "water") {
										$zom['swim'] += 1;
										if ($zom['swim'] >= 20) $zom['swim'] = 0;
									}
									else {
										$zom['swim'] = 0;
									}
									//var_dump("目标:".($zy - 1) );
									//var_dump("原先:".$zo->getY());
									if(abs(($zy - 1) - floor($zo->getY())) == 1){
										//var_dump("跳");
										$zom['jump'] = 0.5;
									}
									else {
										if ($zom['jump'] > 0.01) {
											$zom['jump'] -= 0.1;
										}
										else {
											$zom['jump'] = 0.01;
										}
									}
								}

								$zo->setPosition($pos2);

								$yaw = $this->getyaw($xxx, $zzz);
								$pos3 = $pos2;
								$pos3->y = $pos3->y + 2.62;
								$ppos = $p->getLocation();
								$ppos->y = $ppos->y + $p->getEyeHeight();
								$pitch = $this->getpitch($pos3,$ppos);

								$zom['x'] = $zo->getX();
								$zom['y'] = $zo->getY();
								$zom['z'] = $zo->getZ();
								$zom['yaw'] = $yaw;
								$zom['pitch'] = $pitch;
								$pk3 = new SetEntityMotionPacket;
								$pk3->entities = [
									[$zo->getID(), $xxx, - $zom['swim'] / 100 + $zom['jump'] , $zzz]
								];
								foreach($zo->getViewers() as $pl){
									$pl->dataPacket($pk3);
								}	
					}
				}
				}
				
			}
		}
	}
	
	public function willMove(Entity $entity) {
		foreach($entity->getViewers() as $viewer) {
			if ($entity->distance($viewer->getLocation()) <= 32) return true;
		}
		return false;
	}

	public function MobRandomWalkCalc() {
		$this->dif = $this->getServer()->getDifficulty();
		$ann = $this->animals;
			$filter_res = array_filter($ann);
			if(!empty($filter_res))
				foreach ($this->animals as $animal) {
					if(in_array($animal['type'],$this->nighttype)){
						$level=$this->getServer()->getLevelByName($animal['level']);
						$an = $level->getEntity($animal['ID']);
				//var_dump($animal);
						if($an != ""){
						$zom = &$this->animals[$an->getId()];
						if ($this->willMove($an)) {
						$zo = $level->getEntity($animal['ID']);
						if ($zom['IsChasing'] == "0") {  //自由行走模式
							if ($zom['gotimer'] == 0 or $zom['gotimer'] == 10) {
								//限制转动幅度
								$newmx = mt_rand(-5,5)/10;
								while (abs($newmx - $zom['motionx']) >= 0.7) {
									$newmx = mt_rand(-5,5)/10;
								}
								$zom['motionx'] = $newmx;

								$newmz = mt_rand(-5,5)/10;
								while (abs($newmz - $zom['motionz']) >= 0.7) {
									$newmz = mt_rand(-5,5)/10;
								}
								$zom['motionz'] = $newmz;
							}
							elseif ($zom['gotimer'] >= 20 and $zom['gotimer'] <= 24) {
								$zom['motionx'] = 0;
								$zom['motionz'] = 0;
								//僵尸停止
							}

							$zom['gotimer'] += 0.5;
							if ($zom['gotimer'] >= 22) $zom['gotimer'] = 0;  //重置走路计时器

							//$zom['motionx'] = mt_rand(-10,10)/10;
							//$zom['motionz'] = mt_rand(-10,10)/10;
							$zom['yup'] = 0;
							$zom['up'] = 0;

							//boybook的y轴判断法
							//$width = $this->width;
							$pos = new Vector3 ($zom['x'] + $zom['motionx'], floor($zo->getY()) + 1,$zom['z'] + $zom['motionz']);  //目标坐标
							$zy = $this->ifjump($zo->getLevel(),$pos);
							if ($zy === false) {  //前方不可前进
								$pos2 = new Vector3 ($zom['x'], $zom['y'] ,$zom['z']);  //目标坐标
								if ($this->ifjump($zo->getLevel(),$pos2) === false) { //原坐标依然是悬空
									$pos2 = new Vector3 ($zom['x'], $zom['y']-1,$zom['z']);  //下降
									$zom['up'] = 1;
									$zom['yup'] = 0;
								}
								else {
									$zom['motionx'] = - $zom['motionx'];
									$zom['motionz'] = - $zom['motionz'];
									//转向180度，向身后走
									$zom['up'] = 0;
								}
							}
							else {
								$pos2 = new Vector3 ($zom['x'] + $zom['motionx'], $zy - 1 ,$zom['z'] + $zom['motionz']);  //目标坐标
								if ($pos2->y - $zom['y'] < 0) {
									$zom['up'] = 1;
								}
								else {
									$zom['up'] = 0;
								}
							}

							if ($zom['motionx'] == 0 and $zom['motionz'] == 0) {  //僵尸停止
							}
							else {
								//转向计算
								$yaw = $this->getyaw($zom['motionx'], $zom['motionz']);
								//$zo->setRotation($yaw,0);
								$zom['yaw'] = $yaw;
								$zom['pitch'] = 0;
							}

							//更新僵尸坐标
							$zom['x'] = $pos2->getX();
							$zom['z'] = $pos2->getZ();
							$zom['y'] = $pos2->getY()+1;
							$zom['motiony'] = $pos2->getY() - $zo->getY();
							//echo($zo->getY()."\n");
							//var_dump($pos2);
							//var_dump($zom['motiony']);
							$zo->setPosition($pos2);
							$animal = $zom;
							foreach ($this->getServer()->getOnlinePlayers() as $pl) {
								$pk2 = new RemoveEntityPacket;
								$pk2->eid = $animal['ID'];		
								$pk3 = new AddEntityPacket;
								$pk3->eid = $animal['ID'];
								$pk3->type = $animal['type'];
								$pk3->x = $animal['x'];
								$pk3->y = $animal['y'] ;
								$pk3->z = $animal['z'];
								$pk3->pitch = $animal['pitch'];
								$pk3->yaw = $animal['yaw'];
								$pk3->metadata = [];
				
								$pl->dataPacket($pk2);
								$pl->dataPacket($pk3);		
							}
							
							
							
							
							//echo "SetPosition \n";
						}
					}
				}
			}
		}
	}

	public function AnimalRandomWalkCalc(){
		$this->dif = $this->getServer()->getDifficulty();
		$ann = $this->animals;
			$filter_res = array_filter($ann);
			if(!empty($filter_res))
				foreach ($this->animals as $animal) {
					if(in_array($animal['type'],$this->daytype)){
						$level=$this->getServer()->getLevelByName($animal['level']);
						$an = $level->getEntity($animal['ID']);
				//var_dump($animal);
						if($an != ""){
						$zom = &$this->animals[$an->getId()];
						if ($this->willMove($an)) {
						$zo = $level->getEntity($animal['ID']);
						if ($zom['IsChasing'] == "0") {  //自由行走模式
							if ($zom['gotimer'] == 0 or $zom['gotimer'] == 10) {
								//限制转动幅度
								$newmx = mt_rand(-5,5)/10;
								while (abs($newmx - $zom['motionx']) >= 0.7) {
									$newmx = mt_rand(-5,5)/10;
								}
								$zom['motionx'] = $newmx;

								$newmz = mt_rand(-5,5)/10;
								while (abs($newmz - $zom['motionz']) >= 0.7) {
									$newmz = mt_rand(-5,5)/10;
								}
								$zom['motionz'] = $newmz;
							}
							elseif ($zom['gotimer'] >= 20 and $zom['gotimer'] <= 24) {
								$zom['motionx'] = 0;
								$zom['motionz'] = 0;
								//僵尸停止
							}

							$zom['gotimer'] += 0.5;
							if ($zom['gotimer'] >= 22) $zom['gotimer'] = 0;  //重置走路计时器

							//$zom['motionx'] = mt_rand(-10,10)/10;
							//$zom['motionz'] = mt_rand(-10,10)/10;
							$zom['yup'] = 0;
							$zom['up'] = 0;

							//boybook的y轴判断法
							//$width = $this->width;
							$pos = new Vector3 ($zom['x'] + $zom['motionx'], floor($zo->getY()) + 1,$zom['z'] + $zom['motionz']);  //目标坐标
							$zy = $this->ifjump($zo->getLevel(),$pos);
							if ($zy === false) {  //前方不可前进
								$pos2 = new Vector3 ($zom['x'], $zom['y'] ,$zom['z']);  //目标坐标
								if ($this->ifjump($zo->getLevel(),$pos2) === false) { //原坐标依然是悬空
									$pos2 = new Vector3 ($zom['x'], $zom['y']-1,$zom['z']);  //下降
									$zom['up'] = 1;
									$zom['yup'] = 0;
								}
								else {
									$zom['motionx'] = - $zom['motionx'];
									$zom['motionz'] = - $zom['motionz'];
									//转向180度，向身后走
									$zom['up'] = 0;
								}
							}
							else {
								$pos2 = new Vector3 ($zom['x'] + $zom['motionx'], $zy - 1 ,$zom['z'] + $zom['motionz']);  //目标坐标
								if ($pos2->y - $zom['y'] < 0) {
									$zom['up'] = 1;
								}
								else {
									$zom['up'] = 0;
								}
							}

							if ($zom['motionx'] == 0 and $zom['motionz'] == 0) {  //僵尸停止
							}
							else {
								//转向计算
								$yaw = $this->getyaw($zom['motionx'], $zom['motionz']);
								//$zo->setRotation($yaw,0);
								$zom['yaw'] = $yaw;
								$zom['pitch'] = 0;
							}

							//更新僵尸坐标
							$zom['x'] = $pos2->getX();
							$zom['z'] = $pos2->getZ();
							$zom['y'] = $pos2->getY();
							$zom['motiony'] = $pos2->getY() - $zo->getY();
							//echo($zo->getY()."\n");
							//var_dump($pos2);
							//var_dump($zom['motiony']);
							$zo->setPosition($pos2);
							$animal = $zom;
							//echo "SetPosition \n";
						}
					}
				}
			}
		}
	}
	
	public function getyaw($mx, $mz) {  //根据motion计算转向角度
		//转向计算
		if ($mz == 0) {  //斜率不存在
			if ($mx < 0) {
				$yaw = -90;
			}
			else {
				$yaw = 90;
			}
		}
		else {  //存在斜率
			if ($mx >= 0 and $mz > 0) {  //第一象限
				$atan = atan($mx/$mz);
				$yaw = rad2deg($atan);
			}
			elseif ($mx >= 0 and $mz < 0) {  //第二象限
				$atan = atan($mx/abs($mz));
				$yaw = 180 - rad2deg($atan);
			}
			elseif ($mx < 0 and $mz < 0) {  //第三象限
				$atan = atan($mx/$mz);
				$yaw = -(180 - rad2deg($atan));
			}
			elseif ($mx < 0 and $mz > 0) {  //第四象限
				$atan = atan(abs($mx)/$mz);
				$yaw = -(rad2deg($atan));
			}
			else {
				$yaw = 0;
			}
		}

		$yaw = - $yaw;
		return $yaw;
	}

	public function getpitch(Vector3 $from, Vector3 $to) {
		$distance = $from->distance($to);
		$height = $to->y - $from->y;
		if ($height > 0) {
			return -rad2deg(asin($height/$distance));
		}
		elseif ($height < 0) {
			return rad2deg(asin(-$height/$distance));
		}
		else {
			return 0;
		}
	}
	
	public function getmypitch($my, $d) {  //根据距离计算转向角度
		//转向计算
		if ($d == 0) {  //斜率不存在
			if ($my < 0) {
				$yaw = -90;
			}
			else {
				$yaw = 90;
			}
		}
		else {  //存在斜率
			if ($my >= 0 and $d > 0) {  //第一象限
				$atan = atan($my/$d);
				$yaw = rad2deg($atan);
			}
			elseif ($my >= 0 and $d < 0) {  //第二象限
				$atan = atan($my/abs($d));
				$yaw = 180 - rad2deg($atan);
			}
			elseif ($my < 0 and $d < 0) {  //第三象限
				$atan = atan($my/$d);
				$yaw = -(180 - rad2deg($atan));
			}
			elseif ($my < 0 and $d > 0) {  //第四象限
				$atan = atan(abs($my)/$d);
				$yaw = -(rad2deg($atan));
			}
		}
		
		$yaw = - $yaw;
		return $yaw;
	}
	
	public function ifjump($level, Vector3 $v3, $hate = false) {  //boybook Y轴算法核心函数
		$x = floor($v3->getX());
		$y = floor($v3->getY());
		$z = floor($v3->getZ());

		//echo ($y." ");
		if ($this->whatBlock($level,new Vector3($x,$y,$z)) == "air") {
			//echo "前方空气 ";
			if ($this->whatBlock($level,new Vector3($x,$y-1,$z)) == "block" or new Vector3($x,$y-1,$z) == "climb") {  //方块
				//echo "考虑向前 ";
				if ($this->whatBlock($level,new Vector3($x,$y+1,$z)) == "block" or $this->whatBlock($level,new Vector3($x,$y+1,$z)) == "half" or $this->whatBlock($level,new Vector3($x,$y+1,$z)) == "high") {  //上方一格被堵住了
					//echo "上方卡住 \n";
					return false;  //上方卡住
				}
				else {
					//echo "GO向前走 \n";
					return $y;  //向前走
				}
			}
			elseif ($this->whatBlock($level,new Vector3($x,$y-1,$z)) == "water") {  //水
				//echo "下水游泳 \n";
				return $y-1;  //降低一格向前走（下水游泳）
			}
			elseif ($this->whatBlock($level,new Vector3($x,$y-1,$z)) == "half") {  //半砖
				//echo "下到半砖 \n";
				return $y-0.5;  //向下跳0.5格
			}
			elseif ($this->whatBlock($level,new Vector3($x,$y-1,$z)) == "lava") {  //岩浆
				//echo "前方岩浆 \n";
				return false;  //前方岩浆
			}
			elseif ($this->whatBlock($level,new Vector3($x,$y-1,$z)) == "air") {  //空气
				//echo "考虑向下跳 ";
				if ($this->whatBlock($level,new Vector3($x,$y-2,$z)) == "block") {
					//echo "GO向下跳 \n";
					return $y-1;  //向下跳
				}
				else { //前方悬崖
					//echo "前方悬崖 \n";
					if ($hate === false) {
						return false;
					}
					else {
						return $y-1;  //向下跳
					}
				}
			}
		}
		elseif ($this->whatBlock($level,new Vector3($x,$y,$z)) == "water") {  //水
			//echo "正在水中";
			if ($this->whatBlock($level,new Vector3($x,$y+1,$z)) == "water") {  //上面还是水
				//echo "向上游 \n";
				return $y+1;  //向上游，防溺水
			}
			elseif ($this->whatBlock($level,new Vector3($x,$y+1,$z)) == "block" or $this->whatBlock($level,new Vector3($x,$y+1,$z)) == "half") {  //上方一格被堵住了
				if ($this->whatBlock($level,new Vector3($x,$y-1,$z)) == "block" or $this->whatBlock($level,new Vector3($x,$y-1,$z)) == "half") {  //下方一格被也堵住了
					//echo "上下都被卡住 \n";
					return false;  //上下都被卡住
				}
				else {
					//echo "向下游 \n";
					return $y-1;  //向下游，防卡住
				}
			}
			else {
				//echo "游泳ing... \n";
				return $y;  //向前游
			}
		}
		elseif ($this->whatBlock($level,new Vector3($x,$y,$z)) == "half") {  //半砖
			//echo "前方半砖 \n";
			if ($this->whatBlock($level,new Vector3($x,$y+1,$z)) == "block" or $this->whatBlock($level,new Vector3($x,$y+1,$z)) == "half" or $this->whatBlock($level,new Vector3($x,$y+1,$z)) == "high") {  //上方一格被堵住了
				//return false;  //上方卡住
			}
			else {
				return $y+0.5;
			}

		}
		elseif ($this->whatBlock($level,new Vector3($x,$y,$z)) == "lava") {  //岩浆
			//echo "前方岩浆 \n";
			return false;
		}
		elseif ($this->whatBlock($level,new Vector3($x,$y,$z)) == "high") {  //1.5格高方块
			//echo "前方栅栏 \n";
			return false;
		}
		elseif ($this->whatBlock($level,new Vector3($x,$y,$z)) == "climb") {  //梯子
			//echo "前方梯子 \n";
			//return $y;
			if ($hate) {
				return $y + 0.07;
			}else{
				return $y + 0.5;
			}
		}
		else {  //考虑向上
			//echo "考虑向上 ";
			if ($this->whatBlock($level,new Vector3($x,$y+1,$z)) != "air") {  //前方是面墙
				//echo "前方是墙 \n";
				return false;
			}
			else {
				if ($this->whatBlock($level,new Vector3($x,$y+2,$z)) == "block" or $this->whatBlock($level,new Vector3($x,$y+2,$z)) == "half" or $this->whatBlock($level,new Vector3($x,$y+2,$z)) == "high") {  //上方两格被堵住了
					//echo "2格处被堵 \n";
					return false;
				}
				else {
					//echo "GO向上跳 \n";
					return $y+1;  //向上跳
				}
			}
		}
		return false;
	}

	public function AnimalRandomWalk(){
	$ann = $this->animals;
	$filter_res = array_filter($ann);
	if(!empty($filter_res))
		foreach ($this->animals as $animal){
		if(in_array($animal['type'],$this->daytype)){
			$level=$this->getServer()->getLevelByName($animal['level']);
			$zo = $level->getEntity($animal['ID']);
			if($zo != ""){
			$zom = &$this->animals[$zo->getId()];
			$zom['yup'] = $zom['yup'] -1;
			if($zom['IsStop'] != "1"){
			if($zom['up'] == 1){
				if($zom['yup'] <= 10){
				//var_dump("1");
					$pk3 = new SetEntityMotionPacket;
					$pk3->entities = [
					[$zo->getID(), $zom['motionx']/10,  $zom['motiony']/10 , $zom['motionz']/10]
					];
						foreach($zo->getViewers() as $pl){
						$pl->dataPacket($pk3);
						}
				}else{
				$pk3 = new SetEntityMotionPacket;
				$pk3->entities = [
				[$zo->getID(), $zom['motionx']/10,  -$zom['motiony']/10 , $zom['motionz']/10]
				];
					foreach($zo->getViewers() as $pl){
					$pl->dataPacket($pk3);
					}
				}
			}else{
				//var_dump("2");
				$pk3 = new SetEntityMotionPacket;
				$pk3->entities = [
				[$zo->getID(), $zom['motionx']/10,  -$zom['motiony']/10 , $zom['motionz']/10]
				];
					foreach($zo->getViewers() as $pl){
					$pl->dataPacket($pk3);
					
					}
			}
			}
			}
			}else{
			}
			}
			unset($zo);
			}
	
	public function whatBlock(Level $level, $v3) {  //boybook的y轴判断法 核心 什么方块？
		$block = $level->getBlock($v3);
		$id = $block->getID();
		$damage = $block->getDamage();
		switch ($id) {
			case 0:
			case 6:
			case 27:
			case 30:
			case 31:
			case 37:
			case 38:
			case 39:
			case 40:
			case 50:
			case 51:
			case 63:
			case 66:
			case 68:
			case 78:
			case 111:
			case 141:
			case 142:
			case 171:
			case 175:
			case 244:
			case 323:
				//透明方块
				return "air";
				break;
			case 8:
			case 9:
				//水
				return "water";
				break;
			case 10:
			case 11:
				//岩浆
				return "lava";
				break;
			case 44:
			case 158:
				//半砖
				if ($damage >= 8) {
					return "block";
				}else{
					return "half";
				}
				break;
			case 64:
				//门
				//echo ($damage." ");
				//TODO 这个不知如何判断门是否开启，因为以下条件永远满足
				if (($damage & 0x08) === 0x08) {
					return "air";
				}else{
					return "block";
				}
				break;
			case 85:
			case 107:
			case 139:
				//1.5格高的无法跳跃物
				return "high";
				break;
			case 65:
			case 106:
				//可攀爬物
				return "climb";
				break;
			default:
				//普通方块
				return "block";
				break;
		}
	}

	public function addarrow($id,$yaw,$level,$x,$y,$z,$px,$py,$pz){	
	//var_dump("ID:".$id);
	$this->arrow[$id] = array(
		'ID' => $id,
		'px' => $px,
		'py' => $py,
		'pz' => $pz,
		'motionx' => 0,
		'motiony' => 0,
		'motionz' => 0,
		'x' => $x,
		'y' => $y,
		'z' => $z,
		'yaw' => $yaw,
		'pitch' => 0,
		'level' => $level,
		);
	}		
	
	public function MobRandomWalk(){
		$ann = $this->animals;
	$filter_res = array_filter($ann);
		if(!empty($filter_res))
			foreach ($this->animals as $animal){
				if(in_array($animal['type'],$this->nighttype)){
					$level=$this->getServer()->getLevelByName($animal['level']);
					$zo = $level->getEntity($animal['ID']);
					if($zo != ""){
					$zom = &$this->animals[$zo->getId()];	
					if($zom['IsStop'] != "1"){	
						$zom['yup'] = $zom['yup'] -1;
						$h_r = $this->hatred_r;  //仇恨半径
						$pos = new Vector3($zo->getX(), $zo->getY(), $zo->getZ());
						$hatred = false;
						foreach($zo->getViewers() as $p) {  //获取附近玩家
							if ($p->distance($pos) <= $h_r) {  //玩家在仇恨半径内
								if ($hatred === false) {
									$hatred = $p;
								}
								elseif ($hatred instanceof Player) {
									if ($p->distance($pos) <= $hatred->distance($pos)) {  //比上一个更近
										$hatred = $p;
									}
								}
							}
						}
						//echo ($zom['IsChasing']."\n");
						if ($hatred == false or $this->dif == 0) {
							$zom['IsChasing'] = false;
						}
						else {
							$zom['IsChasing'] = $hatred->getName();
						}
						//echo ($zom['IsChasing']."\n");
						if($zom['IsChasing'] !== false){
							//echo ("是属于仇恨模式\n");
							$p = $this->getServer()->getPlayer($zom['IsChasing']);
							if (($p instanceof Player) === false){
								$zom['IsChasing'] = false;  //取消仇恨模式
							}
							else {
								/*
                                $xxx = 0.07;
                                $zzz = 0.07;
                                $posz1 = new Vector3 ($zo->getX() + $xxx, $zo->getY(), $zo->getZ());
                                    if($p->distance($pos) > $p->distance($posz1)){
                                    $xxx = 0.07;
                                    }
                                    if($p->distance($pos) == $p->distance($posz1)){
                                    $xxx = 0;
                                    }
                                    if($p->distance($pos) < $p->distance($posz1)){
                                    $xxx = -0.07;
                                    }
                                $posz2 = new Vector3 ($zo->getX()+ $xxx, $zo->getY(), $zo->getZ() + $zzz);
                                    if($p->distance($pos) < $p->distance($posz2)){
                                    $zzz = -0.07;
                                    }
                                    if($p->distance($pos) == $p->distance($posz2)){
                                    $zzz = 0;
                                    }
                                    if($p->distance($pos) > $p->distance($posz2)){
                                    $zzz = 0.07;
                                    }
                                */

								//还不如用旧算法了。。
								/*
                                $zx =floor($zo->getX());
                                $zZ = floor($zo->getZ());
                                $xxx = 0.07;
                                $zzz = 0.07;
                                */

								$x1 = $zo->getX() - $p->getX();

								//$jumpy = $zo->getY() - 1;

								if($x1 >= -0.5 and $x1 <= 0.5) { //直行
									//$zx = $zo->getX();
									$xxx = 0;
								}
								elseif($x1 < 0){
									//$zx = $zo->getX() +0.07;
									$xxx =0.07;
								}else{
									//$zx = $zo->getX() -0.07;
									$xxx = -0.07;
								}

								$z1 =$zo->getZ () - $p->getZ() ;
								if($z1 >= -0.5 and $z1 <= 0.5) { //直行
									//$zZ = $zo->getZ();
									$zzz = 0;
								}
								elseif($z1 <0){
									//$zZ = $zo->getZ() +0.07;
									$zzz =0.07;
								}else{
									//$zZ = $zo->getZ() -0.07;
									$zzz =-0.07;
								}

								if ($xxx == 0 and $zzz == 0) {
									$xxx = 0.1;
								}

								$zom['xxx'] = $xxx * 10;
								$zom['zzz'] = $zzz * 10;

								//计算y轴
								//$width = $this->width;
								$pos0 = new Vector3 ($zo->getX(), $zo->getY() + 1 ,$zo->getZ());  //原坐标
								$pos = new Vector3 ($zo->getX()+ $xxx, $zo->getY() + 1,$zo->getZ() + $zzz);  //目标坐标
								$zy = $this->ifjump($zo->getLevel(), $pos, true);

								if ($zy === false) {  //前方不可前进
									//伪自由落体
									if ($this->ifjump($zo->getLevel(),$pos0, true) === false) { //原坐标依然是悬空
										$pos2 = new Vector3 ($zo->getX(), $zo->getY() - 2,$zo->getZ());  //下降
										$zom['up'] = 1;
										$zom['yup'] = 0;
										//var_dump("2");
									}
									else {
										if ($this->whatBlock($level,$pos0) == "climb") {  //梯子
											$zy = $pos0->y + 0.07;
											$pos2 = new Vector3 ($zo->getX(), $zy - 1, $zo->getZ());  //目标坐标
										}
										elseif ($xxx != 0 and $zzz != 0) {  //走向最近距离
											if ($this->ifjump($zo->getLevel(), new Vector3($zo->getX()+ $xxx, $zo->getY() + 1,$zo->getZ()), true) !== false) {
												$pos2 = new Vector3($zo->getX() + $xxx, floor($zo->getY()),$zo->getZ());  //目标坐标
											}
											elseif ($this->ifjump($zo->getLevel(), new Vector3($zo->getX(), $zo->getY() + 1,$zo->getZ()+$zzz), true) !== false) {
												$pos2 = new Vector3($zo->getX(), floor($zo->getY()),$zo->getZ() + $zzz);  //目标坐标
											}
											else {
												$pos2 = new Vector3 ($zo->getX() - $xxx, floor($zo->getY()),$zo->getZ() - $zzz);  //目标坐标
												//转向180度，向身后走
												$zom['up'] = 0;
											}
										}
										else {
											$pos2 = new Vector3 ($zo->getX() - $xxx, floor($zo->getY()),$zo->getZ() - $zzz);  //目标坐标
											//转向180度，向身后走
											$zom['up'] = 0;
										}
									}
								}
								else {
									$pos2 = new Vector3 ($zo->getX()+ $xxx, $zy - 1, $zo->getZ() + $zzz);  //目标坐标
									//echo $zy;
									$zom['up'] = 0;
									if ($this->whatBlock($level, $pos2) == "water") {
										$zom['swim'] += 1;
										if ($zom['swim'] >= 20) $zom['swim'] = 0;
									}
									else {
										$zom['swim'] = 0;
									}
									//var_dump("目标:".($zy - 1) );
									//var_dump("原先:".$zo->getY());
									if(abs(($zy - 1) - floor($zo->getY())) == 1){
										//var_dump("跳");
										$zom['jump'] = 0.5;
									}
									else {
										if ($zom['jump'] > 0.01) {
											$zom['jump'] -= 0.1;
										}
										else {
											$zom['jump'] = 0.01;
										}
									}
								}

								$zo->setPosition($pos2);
								$v3 = new Vector3($zo->getX(),$zo->getY()+2,$zo->getZ());
								$yaw = $this->getyaw($xxx, $zzz);
								$pos3 = $pos2;
								$pos3->y = $pos3->y + 2.62;
								$ppos = $p->getLocation();
								$ppos->y = $ppos->y + $p->getEyeHeight();
								$pitch = $this->getpitch($pos3,$ppos);

								$zom['x'] = $zo->getX();
								$zom['y'] = $zo->getY();
								$zom['z'] = $zo->getZ();
								$zom['yaw'] = $yaw;
								$zom['pitch'] = $pitch;
								$pk3 = new SetEntityMotionPacket;
								$pk3->entities = [
									[$zo->getID(), $xxx, - $zom['swim'] / 100 + $zom['jump'] , $zzz]
								];
								foreach($zo->getViewers() as $pl){
									$pl->dataPacket($pk3);
								}

								
								
								

					if($zom['type'] == "34"){
					//$zom['type'] = "skeleton";
						if($zom['hurt'] >= 10){
							$zom['hurt'] = $zom['hurt'] -1 ;
						}else{
						
						$chunk = $level->getChunk($v3->x >> 4, $v3->z >> 4, true);
						$nbt = $this->getNBT($v3);
						$posnn = new Vector3($zo->getX(),$p->getY(),$zo->getZ());
						$my =$p->getY() - $zo->getY();
						$d = $p->distance($posnn);
						$pitch = $this->getmypitch($my, $d);
						
						$nbt2 = new Compound("", [
								"Pos" => new Enum("Pos", [
									new Double("", $zo->getX()),
									new Double("", $zo->getY()),
									new Double("", $zo->getZ())
								]),
								"Motion" => new Enum("Motion", [
									new Double("", -\sin($zom['yaw']) * \cos($pitch / 180 * M_PI)),
									new Double("", -\sin($pitch / 180 * M_PI)),
									new Double("", \cos($zom['yaw'] / 180 * M_PI) * \cos($pitch / 180 * M_PI))
								]),
								"Rotation" => new Enum("Rotation", [
									new Float("", $zom['yaw']),
									new Float("", $pitch)
								]),
							]);
						$f = 1.5;
						//$ev = new EntityShootBowEvent($this, $bow, Entity::createEntity("Arrow", $this->chunk, $nbt, $this), $f);
						//$ev = new EntityShootBowEvent($zo, new ITEM(262,0), Entity::createEntity("Arrow", $chunk, $nbt2, $p), $f);
						//var_dump("shoot|".-$zom['pitch']."|".$zom['yaw']);
						//var_dump(233333);
						$arrow = new Arrow($chunk,$nbt2);
						$arrow->setPosition($v3);
						$arrow->spawnToAll();
						//$p = $this->getServer()->getPlayer($zom['IsChasing']);
						//$d = $p->distance($v3);
						//$d = $d/1.2;	
						//var_dump($d);
						if(!isset($this->arrow[$arrow->getId()])){		
						$this->addarrow($arrow->getId(),$zom['yaw'],$arrow->getLevel()->getName(),$arrow->getX(),$arrow->getY(),$arrow->getZ(),$p->getX(),$p->getY(),$p->getZ());
						}
						$zom['hurt'] = 20;
						}
					}
					
					if(0 <= $p->distance($pos) and $p->distance($pos) <= 1.5){
					
					if($zom['type'] == "33"){
					//$zom['type'] = "FiringCreeper";
					$zom['IsStop'] = 1;
					$zom['time'] = 30;
					}
					
					if($zom['type'] == "32"){
					//$zom['type'] = "Zombie";
						if($zom['hurt'] >= 0){
							$zom['hurt'] = $zom['hurt'] -1 ;
						}else{
							$p->knockBack($zo, 0, $xxx, $zzz, 0.4);
							if ($p->isSurvival()) {
								$p->sethealth($p->gethealth() - $this->dif * 2);
							}
							$zom['hurt'] = 10 ;
						}
					}
					}
								
							}

						}
						else{
							if($zom['IsChasing'] === false){
								if($zom['up'] == 1){
									if($zom['yup'] <= 10){
										$pk3 = new SetEntityMotionPacket;
										$pk3->entities = [
											[$zo->getID(), $zom['motionx']/10,  $zom['motiony']/10  , $zom['motionz']/10]
										];
										foreach($zo->getViewers() as $pl){
											$pl->dataPacket($pk3);
										}
									}else{
										$pk3 = new SetEntityMotionPacket;
										$pk3->entities = [
											[$zo->getID(), $zom['motionx']/10  -$zom['motiony']/10  , $zom['motionz']/10]
										];
										foreach($zo->getViewers() as $pl){
											$pl->dataPacket($pk3);
										}
									}
								}else{

									$pk3 = new SetEntityMotionPacket;
									$pk3->entities = [
										[$zo->getID(), $zom['motionx']/10,  -$zom['motiony']/10 , $zom['motionz']/10]
									];
									foreach($zo->getViewers() as $pl){
										$pl->dataPacket($pk3);

									}
								}
							}
						}
								}else{
							$zom['time'] = $zom['time'] -1;
							if($zom['time'] <= 0){
							//$zo->sethealth(0);
							unset($this->animals[$zo->getId()]);
							$level->removeEntity($level->getEntity($zo->getId()));
							$e = new Explosion(new Position($zo->getX(), $zo->getY(), $zo->getZ(), $level),$this->bomb);
							$e->explode();
						//$pos = new Vector3($zo->getX(), $zo->getY(), $zo->getZ());
							}
						}
					}else{
					unset($this->animals[$animal['ID']]);
					}						
				}
			}
		unset($zo);
	}

	public function AllDeath(EntityDeathEvent $event){
		$entity = $event->getEntity();
		//if ($entity instanceof Zombie) {
			$eid = $entity->getID();
			if (isset($this->animals[$eid])) {
			$ani = &$this->animals[$eid];
				if(in_array($ani['type'],$this->daytype)){
				$this->animal_A = $this->animal_A - 1;
				//var_dump($this->animal_A);
				unset($this->animals[$eid]);
				}else{
				$this->mob_A = $this->mob_A - 1;
				unset($this->animals[$eid]);
				}
			$ok = mt_rand(0,100);
			if ($ok < 30) {  //掉骨头
				$drop = array(new Item(352));
			}
			elseif ($ok >= 30 and $ok < 50) {  //掉羽毛
				$drop = array(new Item(288));
			}
			elseif ($ok >= 50 and $ok < 60) {  //掉胡萝卜
				$drop = array(new Item(391));
			}
			elseif ($ok >= 60 and $ok < 70) {  //掉土豆
				$drop = array(new Item(392));
			}
			elseif ($ok >= 70 and $ok < 75) {  //掉蜘蛛丝
				$drop = array(new Item(287));
			}
			elseif ($ok >= 75 and $ok < 80) {  //掉石英
				$drop = array(new Item(406));
			}
			elseif ($ok >= 80 and $ok < 85) {  //掉铁锭
				$drop = array(new Item(265));
			}
			elseif ($ok >= 85 and $ok < 90) {  //掉金锭
				$drop = array(new Item(266));
			}
			elseif ($ok >= 90 and $ok < 95) {  //掉甘蔗
				$drop = array(new Item(338));
			}
			elseif ($ok >= 95 and $ok < 100) {  //掉萤石粉
				$drop = array(new Item(348));
			}
			elseif ($ok == 100) {  //掉钻石
				$drop = array(new Item(264));
			}
			else {
				$drop = array();
			}
			$event->setDrops($drop);
		}
	}

	public function ZombieFire() {
		foreach ($this->getServer()->getLevels() as $level) {
			foreach ($level->getEntities() as $zo){
				if ($zo instanceof Zombie) {
					//var_dump($p->getLevel()->getTime());
					if(0 < $level->getTime() and $level->getTime() < 13500){
						$v3 = new Vector3($zo->getX(), $zo->getY(), $zo->getZ());
						$ok = true;
						for ($y0 = $zo->getY() + 2; $y0 <= $zo->getY()+10; $y0++) {
							$v3->y = $y0;
							if ($level->getBlock($v3)->getID() != 0) {
								$ok = false;
								break;
							}
						}
						if ($this->whatBlock($level,new Vector3($zo->getX(), floor($zo->getY() - 1), $zo->getZ())) == "water") $ok = false;
						if ($ok) $zo->setOnFire(2);
					}
					if ($level->getTime() > 24000) {
						$level->setTime(0);
					}
				}
			}
		}
	}

	public function MOBGenerate() {
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			$level = $p->getLevel();
			//if ($level->getTime() >= 13500) {//是夜晚\
			if ($level->getTime() >= 14000) {//是夜晚
			if($this->mobbirth_A >= $this->mob_A){
				$this->mob_A = $this->mob_A +1;
			$v3 = new Vector3($p->getX() + mt_rand(-$this->birth_r,$this->birth_r), $p->getY(), $p->getZ() + mt_rand(-$this->birth_r,$this->birth_r));
			for ($y0 = $p->getY()-10; $y0 <= $p->getY()+10; $y0++) {
				$v3->y = $y0;
				if ($this->whatBlock($level,$v3) == "block") {
					$v3_1 = $v3;
					$v3_1->y = $y0 + 1;
					$v3_2 = $v3;
					$v3_2->y = $y0 + 2;
					if ($level->getBlock($v3_1)->getID() == 0 and $level->getBlock($v3_2)->getID() == 0) {
						//$this->getLogger()->info("地面OK");						//找到地面
							//$this->getLogger()->info("亮度".$this->getLight($level,$v3));
						if ($this->getLight($level,$v3) < 20) {
							//$this->getLogger()->info("亮度OK".$this->getLight($level,$v3));
							$chunk = $level->getChunk($v3->x >> 4, $v3->z >> 4, false);
							$nbt = $this->getNBT($v3);
							$zo = new Zombie($chunk,$nbt);
							$zo->setPosition($v3);
							$zo->spawnToAll();
							$zo->sethealth(10);
							$type =  $this->nighttype[array_rand($this->nighttype)];
							$pk2 = new RemoveEntityPacket;
							$pk2->eid = $zo->getId();
							
							$pk3 = new AddEntityPacket;
							$pk3->eid = $zo->getId();;
							$pk3->type = $type;
							$pk3->x = $zo->getX();
							$pk3->y = $zo->getY()+1;
							$pk3->z = $zo->getZ();
							$pk3->pitch = $zo->pitch;
							$pk3->yaw = $zo->yaw;
							$pk3->metadata = [];
							
								foreach ($this->getServer()->getOnlinePlayers() as $p) {
								$p->dataPacket($pk2);
								$p->dataPacket($pk3);
								}
							//var_dump($type);
							//$this->getLogger()->info("生成了一只");
							//var_dump($zo->getId());
								if(!isset($this->animals[$zo->getId()])){		
								$this->addanimal($zo->getId(),$type,$zo->yaw,$zo->getLevel()->getName(),$zo->getX(),$zo->getY()+1,$zo->getZ());
								}
							}
							break;
						}
					}
				}
			}
		}
	}
	}
	
	public function SpecialMobGenerate() {
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			$level = $p->getLevel();
			//if ($level->getTime() >= 13500) {//是夜晚\
			if ($level->getTime() >= 14000) {//是夜晚
			if($this->smobbirth_A >= $this->smob_A){
				$this->smob_A = $this->smob_A +1;
			$v3 = new Vector3($p->getX() + mt_rand(-$this->birth_r,$this->birth_r), $p->getY(), $p->getZ() + mt_rand(-$this->birth_r,$this->birth_r));
			for ($y0 = $p->getY()-10; $y0 <= $p->getY()+10; $y0++) {
				$v3->y = $y0;
				if ($this->whatBlock($level,$v3) == "block") {
					$v3_1 = $v3;
					$v3_1->y = $y0 + 1;
					$v3_2 = $v3;
					$v3_2->y = $y0 + 2;
					if ($level->getBlock($v3_1)->getID() == 0 and $level->getBlock($v3_2)->getID() == 0) {
						//$this->getLogger()->info("地面OK");						//找到地面
							//$this->getLogger()->info("亮度".$this->getLight($level,$v3));
						if ($this->getLight($level,$v3) < 20) {
							//$this->getLogger()->info("亮度OK".$this->getLight($level,$v3));
							$chunk = $level->getChunk($v3->x >> 4, $v3->z >> 4, false);
							$nbt = $this->getNBT($v3);
							$zo = new Zombie($chunk,$nbt);
							$zo->setPosition($v3);
							$zo->spawnToAll();
							$zo->sethealth(20);
							$type =  $this->specialMobtype[array_rand($this->specialMobtype)];
							$pk2 = new RemoveEntityPacket;
							$pk2->eid = $zo->getId();
							
							$pk3 = new AddEntityPacket;
							$pk3->eid = $zo->getId();;
							$pk3->type = $type;
							$pk3->x = $zo->getX();
							$pk3->y = $zo->getY();
							$pk3->z = $zo->getZ();
							$pk3->pitch = $zo->pitch;
							$pk3->yaw = $zo->yaw;
							$pk3->metadata = [];
							
								foreach ($this->getServer()->getOnlinePlayers() as $p) {
								$p->dataPacket($pk2);
								$p->dataPacket($pk3);
								}
							//var_dump($type);
							//$this->getLogger()->info("生成了一只");
							//var_dump($zo->getId());
								if(!isset($this->animals[$zo->getId()])){		
								$this->addanimal($zo->getId(),$type,$zo->yaw,$zo->getLevel()->getName(),$zo->getX(),$zo->getY(),$zo->getZ());
								//$this->getLogger()->info("加入成功");
								}
							}
							break;
						}
					}
				}
			}
		}
	}
	}
	
	public function DogGenerate() {
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			$level = $p->getLevel();
			//if ($level->getTime() >= 13500) {//是夜晚\
			if ($level->getTime() <= 13500) {  //是白天
				if($this->animalbirth_A >= $this->animal_A){
				$this->animal_A = $this->animal_A +1;
			$v3 = new Vector3($p->getX() + mt_rand(-$this->birth_r,$this->birth_r), $p->getY(), $p->getZ() + mt_rand(-$this->birth_r,$this->birth_r));
			for ($y0 = $p->getY()-10; $y0 <= $p->getY()+10; $y0++) {
				$v3->y = $y0;
				if ($this->whatBlock($level,$v3) == "block") {
					$v3_1 = $v3;
					$v3_1->y = $y0 + 1;
					$v3_2 = $v3;
					$v3_2->y = $y0 + 2;
					if ($level->getBlock($v3_1)->getID() == 0 and $level->getBlock($v3_2)->getID() == 0) {
						//$this->getLogger()->info("地面OK");						//找到地面
							//$this->getLogger()->info("亮度".$this->getLight($level,$v3));
						if ($this->getLight($level,$v3) < 20) {
							//$this->getLogger()->info("亮度OK".$this->getLight($level,$v3));
							$chunk = $level->getChunk($v3->x >> 4, $v3->z >> 4, false);
							$nbt = $this->getNBT($v3);
							$zo = new Villager($chunk,$nbt);
							$zo->setPosition($v3);
							$zo->spawnToAll();
							$zo->sethealth(20);
							$type =  $this->dog;
							$pk2 = new RemoveEntityPacket;
							$pk2->eid = $zo->getId();
							
							$pk3 = new AddEntityPacket;
							$pk3->eid = $zo->getId();;
							$pk3->type = $type;
							$pk3->x = $zo->getX();
							$pk3->y = $zo->getY();
							$pk3->z = $zo->getZ();
							$pk3->pitch = $zo->pitch;
							$pk3->yaw = $zo->yaw;
							$pk3->metadata = [];
							
								foreach ($this->getServer()->getOnlinePlayers() as $p) {
								$p->dataPacket($pk2);
								$p->dataPacket($pk3);
								}
							//var_dump($type);
							//$this->getLogger()->info("生成了一只Dog");
							//var_dump($zo->getId());
								if(!isset($this->animals[$zo->getId()])){		
								$this->addanimal($zo->getId(),$type,$zo->yaw,$zo->getLevel()->getName(),$zo->getX(),$zo->getY(),$zo->getZ());
								$this->addDog($zo->getId(),$type,$zo->yaw,$zo->getLevel()->getName(),$zo->getX(),$zo->getY(),$zo->getZ());
								//$this->getLogger()->info("加入成功");
								}
							}
							break;
						}
					}
				}
			}
		}
	}
	}

	public function SpecialMobRandomWalkCalc() {
		$this->dif = $this->getServer()->getDifficulty();
			$filter_res = array_filter($this->animals);
			if(!empty($filter_res))
				foreach ($this->animals as $animal) {
					if(in_array($animal['type'],$this->specialMobtype)){
						$level=$this->getServer()->getLevelByName($animal['level']);
						$an = $level->getEntity($animal['ID']);
				//var_dump($animal);
						if($an != ""){
						$zom = &$this->animals[$an->getId()];
							if ($zom['IsChasing'] == "0") {
								if($animal['type'] == 38){
									$move = mt_rand(-2,2);
									//var_dump("Ender");	
										if($move == 0 ){
											//var_dump("Move!");
											$pos = new Vector3 ($zom['x'] + rand(-50,50), floor($an->getY()),$zom['z']  + rand(-50,50));  //目标坐标
											if ($this->whatBlock($level,$pos) == "block") {
												$v3_1 = $pos;
												$v3_1->y = floor($an->getY()) + 1 + 1;
												$v3_2 = $pos;
												$v3_2->y =floor($an->getY()) + 1  + 2;
												$an->setPosition($pos);
												$pos4 = new Vector3 ($zom['x'],$zom['y'],$zom['z']);
												$w1 = new HeartParticle($pos);
												$w2 = new HeartParticle($pos4);
												$an->getLevel()->addParticle($w1);
												$an->getLevel()->addParticle($w2);
												if ($level->getBlock($v3_1)->getID() == 0 and $level->getBlock($v3_2)->getID() == 0) { 
												$zom['x'] = $pos->x;
												$zom['y'] = $pos->y ;
												$zom['z'] = $pos->z;
												}
											}
										}
									}
							}
						if ($this->willMove($an)) {
						$zo = $level->getEntity($animal['ID']);
						if ($zom['IsChasing'] == "0") {  //自由行走模式
							if ($zom['gotimer'] == 0 or $zom['gotimer'] == 10) {
								//限制转动幅度
								$newmx = mt_rand(-5,5)/10;
								while (abs($newmx - $zom['motionx']) >= 0.7) {
									$newmx = mt_rand(-5,5)/10;
								}
								$zom['motionx'] = $newmx;

								$newmz = mt_rand(-5,5)/10;
								while (abs($newmz - $zom['motionz']) >= 0.7) {
									$newmz = mt_rand(-5,5)/10;
								}
								$zom['motionz'] = $newmz;
							}
							elseif ($zom['gotimer'] >= 20 and $zom['gotimer'] <= 24) {
								$zom['motionx'] = 0;
								$zom['motionz'] = 0;
								//僵尸停止
							}

							$zom['gotimer'] += 0.5;
							if ($zom['gotimer'] >= 22) $zom['gotimer'] = 0;  //重置走路计时器

							//$zom['motionx'] = mt_rand(-10,10)/10;
							//$zom['motionz'] = mt_rand(-10,10)/10;
							$zom['yup'] = 0;
							$zom['up'] = 0;

							//boybook的y轴判断法
							//$width = $this->width;
							$pos = new Vector3 ($zom['x'] + $zom['motionx'], floor($zo->getY()) + 1,$zom['z'] + $zom['motionz']);  //目标坐标
							$zy = $this->ifjump($zo->getLevel(),$pos);
							if ($zy === false) {  //前方不可前进
								$pos2 = new Vector3 ($zom['x'], $zom['y'] ,$zom['z']);  //目标坐标
								if ($this->ifjump($zo->getLevel(),$pos2) === false) { //原坐标依然是悬空
									$pos2 = new Vector3 ($zom['x'], $zom['y']-1,$zom['z']);  //下降
									$zom['up'] = 1;
									$zom['yup'] = 0;
								}
								else {
									$zom['motionx'] = - $zom['motionx'];
									$zom['motionz'] = - $zom['motionz'];
									//转向180度，向身后走
									$zom['up'] = 0;
								}
							}
							else {
								$pos2 = new Vector3 ($zom['x'] + $zom['motionx'], $zy - 1 ,$zom['z'] + $zom['motionz']);  //目标坐标
								if ($pos2->y - $zom['y'] < 0) {
									$zom['up'] = 1;
								}
								else {
									$zom['up'] = 0;
								}
							}

							if ($zom['motionx'] == 0 and $zom['motionz'] == 0) {  //僵尸停止
							}
							else {
								//转向计算
								$yaw = $this->getyaw($zom['motionx'], $zom['motionz']);
								//$zo->setRotation($yaw,0);
								$zom['yaw'] = $yaw;
								$zom['pitch'] = 0;
							}

							//更新僵尸坐标
							$zom['x'] = $pos2->getX();
							$zom['z'] = $pos2->getZ();
							$zom['y'] = $pos2->getY();
							$zom['motiony'] = $pos2->getY() - $zo->getY();
							//echo($zo->getY()."\n");
							//var_dump($pos2);
							//var_dump($zom['motiony']);
							$zo->setPosition($pos2);
							$animal = $zom;
							foreach ($this->getServer()->getOnlinePlayers() as $pl) {
								$pk2 = new RemoveEntityPacket;
								$pk2->eid = $animal['ID'];		
								$pk3 = new AddEntityPacket;
								$pk3->eid = $animal['ID'];
								$pk3->type = $animal['type'];
								$pk3->x = $animal['x'];
								$pk3->y = $animal['y'] ;
								$pk3->z = $animal['z'];
								$pk3->pitch = $animal['pitch'];
								$pk3->yaw = $animal['yaw'];
								$pk3->metadata = [];
				
								$pl->dataPacket($pk2);
								$pl->dataPacket($pk3);		
							}
							
							
							
							
							//echo "SetPosition \n";
						}
					}
				}
			}
		}
	}

	public function AllDamage(EntityDamageEvent $event){//僵尸击退修复
		if($event instanceof EntityDamageByEntityEvent){
			$p = $event->getDamager();
			$zo = $event->getEntity();
			if(isset($this->animals[$zo->getId()]) and $zo instanceof Zombie or  $zo instanceof Villager){
				if ($p instanceof Player) {
					$weapon = $p->getInventory()->getItemInHand()->getID();  //得到玩家手中的武器
					$high = 0;
					if ($weapon == 258 or $weapon == 271 or $weapon == 275) {  //击退x5
						$back = 0.7;
					}
					elseif ($weapon == 267 or $weapon == 272 or $weapon == 279 or $weapon == 283 or $weapon == 286) {  //击退x1
						$back = 1;
					}
					elseif ($weapon == 276) {  //击退x2
						$back = 2;
					}
					elseif ($weapon == 292) {  //击退x10
						$back = 10;
						$high = 5;
					}
					else {
						$back = 0.5;
					}
					
					$zom = &$this->animals[$zo->getId()];
					if(isset($this->dogs[$zo->getId()])){
						if($weapon = 352){
							$dog = &$this->dogs[$zo->getId()];
							if($dog['love'] != 9999){
								if($p->getinventory()->getItemInHand()->getID() == 352){
							//var_dump("玩家".$p->getName()."喂养了ID为".$zo->getId()."的wolf");
							$p->getinventory()->removeItem(new Item(352,$p->getInventory()->getItemInHand()->getDamage(),1));
							$pos3 = new Vector3 ($zom['x'],$zom['y']+1,$zom['z'],$zo->getLevel()); 
							$pos4 = new Vector3 ($zom['x'],$zom['y'],$zom['z'],$zo->getLevel());
							$w1 = new HeartParticle($pos3);
							$w2 = new HeartParticle($pos4);
							$zo->getLevel()->addParticle($w1);
							$zo->getLevel()->addParticle($w2);
								if($dog['love'] == 10 or $dog['love'] == 9999){
									$dog['love'] = 9999;
									$dog['owner'] = $p->getName();
									$p->sendTip(TextFormat::GREEN."驯养成功！");
								}else{
									$dog['love'] = $dog['love'] +1;
									$dog['owner'] = $p->getName();
								}
								
							$event->setDamage(0);
							$event->setKnockBack(0);
							}
						}else{
							$zom['IsChasing'] = $p->getName();
						}
					}
					}
					@$zo->knockBack($p, 0, - $zom['xxx'] * $back, - $zom['zzz'] * $back, 0.4);
					//var_dump("玩家".$p->getName()."攻击了ID为".$zo->getId()."的僵尸");
					$zom['x'] = $zom['x'] - $zom['xxx'] * $back;
					$zom['y'] = $zo->getY() + $high;
					$zom['z'] = $zom['z'] - $zom['zzz'] * $back;
					$pos2 = new Vector3 ($zom['x'],$zom['y'],$zom['z']);  //目标坐标
					$zo->setPosition($pos2);
					
					if($zo->getHealth()- $event->getDamage() <= 0){
					//var_dump("玩家".$p->getName()."杀死了ID为".$zo->getId()."的僵尸");
					unset($this->animals[$zo->getId()]);
					}
				}
			}
		}
	}

	public function PlayerJoin(PlayerJoinEvent $event){
	$pl = $event->getPlayer();
	$ann = $this->animals;
	$filter_res = array_filter($ann);
	if(!empty($filter_res))
		foreach ($this->animals as $animal){
			$pk2 = new RemoveEntityPacket;
			$pk2->eid = $animal['ID'];
							
			$pk3 = new AddEntityPacket;
			$pk3->eid = $animal['ID'];
			$pk3->type = $animal['type'];
			$pk3->x = $animal['x'];
			$pk3->y = $animal['y'] +1;
			$pk3->z = $animal['z'];
			$pk3->pitch = $animal['pitch'];
			$pk3->yaw = $animal['yaw'];
			$pk3->metadata = [];
				
			$pl->dataPacket($pk2);
			$pl->dataPacket($pk3);		
			}
			
	$config = array(
		"time" => 0,
        );		
	$this->player[$pl->getname()] = $config;

	
	}

	public function clear(){
	foreach ($this->getServer()->getLevels() as $level){
			foreach ($level->getEntities() as $an){
				if(!isset($this->animals[$an->getId()])){
					if($level->getEntity($an->getId()) instanceof Player){
						}else{
							$level->removeEntity($level->getEntity($an->getId()));
							//$this->getLogger()->info("清除一只");
						}
					}
				}
			}	
	}
		
	public function timereset(){
		foreach ($this->getServer()->getLevels() as $level){
			if ($level->getTime() > 24000) {
			$level->setTime(0);
			}
		}
	}	
	
	public function addanimal($id,$type,$yaw,$level,$x,$y,$z){	
	$this->animals[$id] = array(
		'ID' => $id,
		'type' => $type,
		'IsChasing' => 0,
		'IsStop' => 0,
		'motionx' => 0,
		'motiony' => 0,
		'motionz' => 0,
		'hurt' => 10,
		'time'=>10,
		'x' => $x,
		'y' => $y,
		'z' => $z,
		'yup' => 20,
		'up' => 0,
		'yaw' => $yaw,
		'pitch' => 0,
		'level' => $level,
		'xxx' => 0,
		'zzz' => 0,
		'gotimer' => 10,
		'swim' => 0,
		'jump' => 0,
		'owner' => 0,
		);
	}		
	
	public function addDog($id,$type,$yaw,$level,$x,$y,$z){	
	$this->dogs[$id] = array(
		'ID' => $id,
		'type' => $type,
		'IsChasing' => 0,
		'IsStop' => 0,
		'motionx' => 0,
		'motiony' => 0,
		'motionz' => 0,
		'hurt' => 10,
		'time'=>10,
		'x' => $x,
		'y' => $y,
		'z' => $z,
		'yup' => 20,
		'up' => 0,
		'yaw' => $yaw,
		'pitch' => 0,
		'level' => $level,
		'xxx' => 0,
		'zzz' => 0,
		'gotimer' => 10,
		'swim' => 0,
		'jump' => 0,
		'love' => 0,
		'owner' => 0,
		);
	}		
	
	public function PlayerItemHeld(PlayerItemHeldEvent $event) {
		//分析手上拿的东西
		$player = $event->getPlayer();
		$itemid = $event->getItem()->getID();
		if ($itemid == 288) {
			$player->sendMessage($this->getLight($player->getLevel(),$player->getLocation()));
		}
	}

	public function animalGenerate() {
		foreach ($this->getServer()->getOnlinePlayers() as $p) {	
			$level = $p->getLevel();
			if ($level->getTime() <= 13500) {  //是白天
				if($this->animalbirth_A >= $this->animal_A){
				$this->animal_A = $this->animal_A +1;
			$v3 = new Vector3($p->getX() + mt_rand(-$this->birth_r,$this->birth_r), $p->getY(), $p->getZ() + mt_rand(-$this->birth_r,$this->birth_r));
			for ($y0 = $p->getY()-10; $y0 <= $p->getY()+10; $y0++) {
				$v3->y = $y0;
				if ($this->whatBlock($level,$v3) == "block") {
					$v3_1 = $v3;
					$v3_1->y = $y0 + 1;
					$v3_2 = $v3;
					$v3_2->y = $y0 + 2;
					if ($level->getBlock($v3_1)->getID() == 0 and $level->getBlock($v3_2)->getID() == 0) {  //找到地面
						if ($this->getLight($level,$v3) > 15) {
							$chunk = $level->getChunk($v3->x >> 4, $v3->z >> 4, false);
							$nbt = $this->getNBT($v3);
							$zo = new Villager($chunk,$nbt);
							$zo->setPosition($v3);
							$zo->spawnToAll();
							
							$type =  $this->daytype[array_rand($this->daytype)];
							$pk2 = new RemoveEntityPacket;
							$pk2->eid = $zo->getId();
							
							$pk3 = new AddEntityPacket;
							$pk3->eid = $zo->getId();
							$pk3->type = $type;
							$pk3->x = $zo->getX();
							$pk3->y = $zo->getY();
							$pk3->z = $zo->getZ();
							$pk3->pitch = $zo->pitch;
							$pk3->yaw = $zo->yaw;
							$pk3->metadata = [];
							
								foreach ($this->getServer()->getOnlinePlayers() as $p) {
								$p->dataPacket($pk2);
								$p->dataPacket($pk3);
								}
							//var_dump($type);
							//$this->getLogger()->info("生成了一只白天");
							//var_dump($zo->getId());
								if(!isset($this->animals[$zo->getId()])){		
								$this->addanimal($zo->getId(),$type,$zo->yaw,$zo->getLevel()->getName(),$zo->getX(),$zo->getY(),$zo->getZ());
								}
							}
							break;
						}
					}
				}
				}
			}
		}
	}
	
	public function onDisable(){
		$this->getLogger()->info("AnimalWorld Unload Success!");
	}

}
