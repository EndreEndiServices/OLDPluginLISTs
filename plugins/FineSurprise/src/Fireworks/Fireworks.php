<?php

namespace Fireworks;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\command\CommandExecutor;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;

class Fireworks extends PluginBase implements Listener, CommandExecutor {

	public $eid;
	public $players = [];
	public $itemid = 280;

	public static $obj = null;

	/**
	 * @return Fireworks
	 * API调用
     */
	public static function getPlugin(){
		return self::$obj;
	}

	public function onLoad(){
		self::$obj = $this;
	}

	public function onEnable() {
	    $this->eid = 10000;
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
 	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if ($sender instanceof Player) {
			$pos = $sender->getPosition();
			$pos->y = $pos->y + 20;
			$this->Firework($pos,mt_rand(30,40));
		}else{
			$pos = new Position(128,90,128,$this->getServer()->getDefaultLevel());
			$this->Firework($pos,100,4,true);
		}
		return true;
	}

	/**
	 * @param Position $pos
	 * @param $high
	 * @param null $count
	 * @param null $size
	 * @param bool $colorful
     */
	public function gofire(Position $pos, $high = 20, $count = null, $size = null, $colorful = false) {
		if ($count == null) $count = mt_rand(25,45);
		if ($size == null) $size = mt_rand(10,15)/5;
		$this->goUp($pos);
		$firepos = $pos;
		$firepos->y += $high;
		$tps = $this->getServer()->getTicksPerSecond();
		$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"Firework"],[$firepos,$count,$size,$colorful]),$tps * 0.9);
		unset($firepos,$tps);
	}

	/**
	 * @param Position $pos
	 * 从某坐标向上飞出一个雪球，持续0.9秒
     */
	public function goUp(Position $pos) {
		$v3 = new Vector3($pos->x,$pos->y,$pos->z);
		$motion = new Vector3(0,10,0);
		$tps = $this->getServer()->getTicksPerSecond();
		foreach ($pos->getLevel()->getPlayers() as $player) {
			$this->SnowspawnTo($player,$this->eid,$v3,$motion);
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"removeEntity"],[$player,$this->eid]),$tps * 0.9);
			unset($layer);
		}
		unset($v3,$motion,$tps);
		$this->eid++;
	}

	/**
	 * @param Position $pos
	 * @param int $count
	 * @param int $size
	 * @param bool $colorful
	 * 在空中释放烟花
     */
	public function Firework(Position $pos, $count = 35, $size = 2, $colorful = false) {
		$v3 = new Vector3($pos->x,$pos->y,$pos->z);
		$color = mt_rand(1,14);
		for ($i=1; $i <= $count; $i++) {
			$mx = mt_rand(-$size, $size) / 5;
			$my = mt_rand(-$size+1, $size+1) / 5;
			$mz = mt_rand(-$size, $size) / 5;
			$motion = new Vector3($mx, $my, $mz);
			$tps = $this->getServer()->getTicksPerSecond();

			$if = mt_rand(0, 2);
			if ($if == 0) {
				foreach ($pos->getLevel()->getPlayers() as $player) {
					$this->SnowspawnTo($player, $this->eid, $v3, $motion);
					$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "removeEntity"], [$player, $this->eid]), $tps * 2);
					unset($player);
				}
			} elseif ($if >= 1) {
				if ($colorful === true) $color = mt_rand(1,14);
				$item = new Item(351, $color);
				foreach ($pos->getLevel()->getPlayers() as $player) {
					$this->DropItemspawnTo($player, $this->eid, $item, $v3, $motion);
					$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "removeEntity"], [$player, $this->eid]), $tps * 2);
					unset($player);
				}
				unset($item);
			}
			$this->eid++;
			//echo $motion->x.','.$motion->y.','.$motion->z."\n";
			unset($mx, $my, $mz, $motion, $tps, $if);
		}

		if ($pos->getLevel()->getTime() < 0 or $pos->getLevel()->getTime() >= 14000) {  //是夜晚
			if ($pos->getLevel()->getBlock($v3)->getId() != 51) {
				$light = new Item(51);
				$pos->getLevel()->setBlock($v3,$light->getBlock());
				$pos->getLevel()->updateAllLight($v3);
				$tps = $this->getServer()->getTicksPerSecond();
				$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"setBlock"],[$pos->getLevel(),$v3,$pos->getLevel()->getBlock($v3)]),$tps * 1.5);
				unset($light,$tps);
			}
		}
		$this->explode($pos);  //爆炸声
	}

	/**
	 * @param Position $pos
	 * 发出爆炸声
	 * 0-60距离为最响
	 * 60-100距离响声渐渐减弱
	 * >100不发包
     */
	public function explode(Position $pos) {
		$level = $pos->getLevel();
		$v3 = new Vector3($pos->x,$pos->y,$pos->z);
		foreach($level->getPlayers() as $player) {
			$distance = floor($player->distance($v3));
			//echo $distance." ";
			if ($distance <= 60) {
				$this->explodePacket($player, $distance / 3);
				//echo ($distance/3)."\n";
			}
			elseif ($distance <= 100) {
				$this->explodePacket($player, 20 + ($distance - 60) / 10);
				//echo (20 + ($distance - 60) / 10)."\n";
			}
			else{
			}
			unset($player,$distance);
		}
		unset($level,$v3);
	}

	/**
	 * @param Player $player
	 * @param int $high
	 * Send explode Data Packet
     */
	public function explodePacket(Player $player, $high = 15) {
		$pk = new ExplodePacket();
		$pk->x = $player->getX();
		$pk->y = $player->getY() + $high;
		$pk->z = $player->getZ();
		$pk->radius = 10;
		$send[] = new Vector3($player->getX(), $player->getY() + $high, $player->getZ());
		$pk->records = $send;
		$player->dataPacket($pk);
		unset($pk,$send);
	}

	/**
	 * @param Level $level
	 * @param Vector3 $pos
	 * @param Block $block
     */
	public function setBlock(Level $level, Vector3 $pos, Block $block) {
		if ($level instanceof Level) {
			$level->setBlock($pos, $block);
		}
	}

	public function removeEntity(Player $player, $eid){
		$pk = new RemoveEntityPacket();
		$pk->eid = $eid;
		$player->dataPacket($pk);
		unset($pk);
	}

	/**
	 * @param Player $player
	 * @param $eid
	 * @param Vector3 $pos
	 * @param Vector3 $motion
	 * 发送生成雪球实体数据包
     */
	public function SnowspawnTo(Player $player, $eid, Vector3 $pos, Vector3 $motion){
		$pk = new AddEntityPacket();
		$pk->type = 81;
		$pk->eid = $eid;
		$pk->x = $pos->getX();
		$pk->y = $pos->getY();
		$pk->z = $pos->getZ();
		$pk->did = 0;
		$player->dataPacket($pk);
		$player->addEntityMotion($eid, $motion->x, $motion->y, $motion->z);

		unset($pk);
	}

	/**
	 * @param Player $player
	 * @param $eid
	 * @param Item $item
	 * @param Vector3 $pos
	 * @param Vector3 $motion
	 * 发送生成掉落物品实体数据包
     */
	public function DropItemspawnTo(Player $player, $eid, Item $item, Vector3 $pos, Vector3 $motion){
		$pk = new AddItemEntityPacket();
		$pk->eid = $eid;
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->roll = 0;
		$pk->item = $item;
		$player->dataPacket($pk);
		$player->addEntityMotion($eid, $motion->x, $motion->y, $motion->z);

		unset($pk);
	}

	/**
	 * @param PlayerInteractEvent $event
	 * 监听触摸事件，来放烟花
     */
	public function onTouch(PlayerInteractEvent $event) {
		$player = $event->getPlayer();
		$item = $event->getItem();
		$block = $event->getBlock();
		if ($item->getId() == $this->itemid) {
			if (in_array($player->getName(),$this->players)) {
				//$player->sendMessage("[Fireworks] Please don't fire too fast !");
			}
			else {
				$pos = new Position($block->x + 0.5, $block->y + 1, $block->z + 0.5, $block->getLevel());
				if (mt_rand(0,5) == 0) {
					$colorful = true;
				}else{
					$colorful = false;
				}
				$this->gofire($pos,20,null,null,$colorful);
				$this->players[] = $player->getName();
				$tps = $this->getServer()->getTicksPerSecond();
				$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"removePlayer"],[$player->getName()]),$tps * 5);
			}
		}
		unset($player,$item,$block);
	}

	/**
	 * @param PlayerItemHeldEvent $event
	 * 友情提示
     */
	public function onItemHold(PlayerItemHeldEvent $event) {
		$player = $event->getPlayer();
		$item = $event->getItem();
		if ($item->getId() == $this->itemid) {
			$player->sendMessage("[FineCase] О , я вижу ты взял в руку ключ !\n[FineCase] Теперь ты можешь открыть кейс !");
		}
		unset($player,$item);
	}

	/**
	 * @param $name
	 * 从禁止燃放列表移除玩家
     */
	public function removePlayer($name) {
		$founded = array_search($name, $this->players);
		if ($founded !== false) {
			array_splice($this->players, $founded, 1);   //移除此键值的数据
		}
		unset($founded);
	}

}
