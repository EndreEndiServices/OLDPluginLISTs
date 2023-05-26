<?php

namespace Richen;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\command\CommandExecutor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByEntity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;

class RegionGuard extends PluginBase implements CommandExecutor, Listener
{
	private $db, $pos1 = array(), $pos2 = array();
	private $g_var;
	private static $instance;
	
	public function onBlockBreak(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		
		$x = round($event->getBlock()->getX());
		$y = round($event->getBlock()->getY());
		$z = round($event->getBlock()->getZ());
		
		/**
		 * АВТОШАХТА
		**/
		$minX = 41;
		$minY = 51;
		$minZ = 314;
		$maxX = 49;
		$maxY = 53;
		$maxZ = 320;
		for($x1 = $minX; $x1 <= $maxX; ++$x1){
			for($y1 = $minY; $y1 <= $maxY; ++$y1){
				for($z1 = $minZ; $z1 <= $maxZ; ++$z1){
					if($x == $x1 && $y == $y1 && $z == $z1){
						return;
					}
				}
			}
		}
		
		$level = $event->getBlock()->getLevel()->getName();
		$username = strtolower($event->getPlayer()->getName());
		
		if($event->getItem()->getID() == 271)
		{
			$this->pos1[$username] = array($x,$y,$z,$level,);
			$event->getPlayer()->sendMessage("§dПервая точка установлена на (".$x.", ".$y.", ".$z.")");
			
			if(isset($this->pos1[$username]) && isset($this->pos2[$username]) && $this->pos1[$username][3] == $this->pos2[$username][3])
			{
				$pos1 = $this->pos1[$username];
				$pos2 = $this->pos2[$username];
				$min[0] = min($pos1[0], $pos2[0]);
				$max[0] = max($pos1[0], $pos2[0]);
				$min[1] = min($pos1[1], $pos2[1]);
				$max[1] = max($pos1[1], $pos2[1]);
				$min[2] = min($pos1[2], $pos2[2]);
				$max[2] = max($pos1[2], $pos2[2]);
				$count = $this->countBlocks($min[0], $min[1], $min[2], $max[0], $max[1], $max[2]);
				$player->sendMessage("§dВыбрано $count блок(ов).");
			}
			$event->setCancelled(true);
		}
		else{
			$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			$member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '".$result['Region']."' AND Name = '$username'")->fetchArray(SQLITE3_ASSOC);
			$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '".$result['Region']."' AND Flag = 'build' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
			$chest_access_flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '".$result['Region']."' AND Flag = 'chest-access' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
			
			if($result !== false && $username != $result['Owner'] && ! $event->getPlayer()->isOp() && ! $member['count'] && ! $flag['count']){
				$event->getPlayer()->sendTip("§4Вы не можете ломать блоки на этой территории.");
				$event->setCancelled(true);
			}
			else{
				/**
				 * Eсли нет привата ...
				**/
			}
		}
	}
	
	public function onEntityDamageByEntity(EntityDamageEvent $event)
	{
		$entity = $event->getEntity();
		$player = $event->getEntity();
		$spawn  = $entity->getLevel()->getSafeSpawn();
		
		if(($player iNsTaNcEoF Player)
			and	($event->getCause() == EntityDamageEvent::CAUSE_FALL)
				and	($player->getPosition()->distance(new \pocketmine\math\Vector3($spawn->getX(), $spawn->getY(), $spawn->getZ())) <= 80))
						$event->setCancelled();
						
		if($event instanceof EntityDamageByEntityEvent)
		{
			$damager = $event->getDamager();
			
			$xe = round($entity->getX());
			$ye = round($entity->getY());
			$ze = round($entity->getZ());
			$xd = round($damager->getX());
			$yd = round($damager->getY());
			$zd = round($damager->getZ());
			
			$resultd_check = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $xd AND $xd <= Pos2X) AND (Pos1Y <= $yd AND $yd <= Pos2Y) AND (Pos1Z <= $zd AND $zd <= Pos2Z) AND Level = 'world';")->fetchArray(SQLITE3_ASSOC);
			$resultd = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $xd AND $xd <= Pos2X) AND (Pos1Y <= $yd AND $yd <= Pos2Y) AND (Pos1Z <= $zd AND $zd <= Pos2Z) AND Level = 'world';")->fetchArray(SQLITE3_ASSOC);
			$pvpd_flag = $this->db->query("SELECT * FROM FLAGS WHERE Region = '".$resultd['Region']."' AND Flag = 'pvp'")->fetchArray(SQLITE3_ASSOC);
			$pvpd_flag_check = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '".$resultd['Region']."' AND Flag = 'pvp'")->fetchArray(SQLITE3_ASSOC);
			
			
			$resulte_check = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $xe AND $xe <= Pos2X) AND (Pos1Y <= $ye AND $ye <= Pos2Y) AND (Pos1Z <= $ze AND $ze <= Pos2Z) AND Level = 'world';")->fetchArray(SQLITE3_ASSOC);
			$resulte = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $xe AND $xe <= Pos2X) AND (Pos1Y <= $ye AND $ye <= Pos2Y) AND (Pos1Z <= $ze AND $ze <= Pos2Z) AND Level = 'world';")->fetchArray(SQLITE3_ASSOC);
			$pvpe_flag = $this->db->query("SELECT * FROM FLAGS WHERE Region = '".$resulte['Region']."' AND Flag = 'pvp'")->fetchArray(SQLITE3_ASSOC);
			$pvpe_flag_check = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '".$resulte['Region']."' AND Flag = 'pvp'")->fetchArray(SQLITE3_ASSOC);
			
			if($entity instanceof Player && $damager instanceof Player)
			{
				if(($resultd_check['count'] && $pvpd_flag_check['count']) || ($resulte_check['count'] && $pvpe_flag_check['count']))
				{
					if($pvpd_flag['Value'] == "deny" && $pvpe_flag['Value'] != "deny"){
						$event->setCancelled(true);
						$damager->sendTip(TextFormat::DARK_RED . "Вы находитесь на территории без PVP.");
					}
					
					if($pvpd_flag['Value'] == "deny" && $pvpe_flag['Value'] == "deny"){
						$event->setCancelled(true);
						$damager->sendTip(TextFormat::DARK_RED . "Вы находитесь на территории без PVP.");
					}
					
					if($pvpd_flag['Value'] != "deny" && $pvpe_flag['Value'] == "deny"){
						$event->setCancelled(true);
						$damager->sendTip(TextFormat::DARK_RED . "Этот игрок находится на территории без PVP.");
					}
				}
			}
		}
	}
	
	public function onEntityDamage(EntityDamageEvent $event)
	{
		$entity = $event->getEntity();
		
		if($event instanceof EntityDamageEvent){
			if($entity instanceof Player)
			{
				$x = round($entity->getX());
				$y = round($entity->getY());
				$z = round($entity->getZ());
				
				$level = $entity->getLevel()->getName();
				$player = $entity->getPlayer();
				
				$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
				$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
				$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '".$result['Region']."' AND Flag = 'invincible' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
				
				if($count['count'] && $flag['count']){
					$event->setCancelled(true);
				}
			}
		}
	}

	public function onBlockPlace(BlockPlaceEvent $event)
	{
		$x = round($event->getBlock()->getX());
		$y = round($event->getBlock()->getY());
		$z = round($event->getBlock()->getZ());
		
		$level = "world";
		$username = strtolower($event->getPlayer()->getName());
		
		$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
		$member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '".$result['Region']."' AND Name = '$username'")->fetchArray(SQLITE3_ASSOC);
		$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '".$result['Region']."' AND Flag = 'build' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
		
		if($result !== false and $username != $result['Owner'] and ! $event->getPlayer()->isOp() and ! $member['count'] and ! $flag['count']){
			$event->getPlayer()->sendMessage(TextFormat::DARK_RED."Вы не можете ставить блоки на этой территории.");
			$event->setCancelled(true);
		}
	}
	
	public function onInteract(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		
		$x = round($event->getBlock()->getX());
		$y = round($event->getBlock()->getY());
		$z = round($event->getBlock()->getZ());
		
		$level = "world";
		$username = strtolower($player->getName());
		
		if($event->getItem()->getID() == 271){
			$this->pos2[$username] = array($x,$y,$z,$level,);
			$player->sendMessage("§dВторая точка установлена на (".$x.", ".$y.", ".$z.")");
			if(isset($this->pos1[$username]) && isset($this->pos2[$username]) && $this->pos1[$username][3] == $this->pos2[$username][3])
			{
				$pos1 = $this->pos1[$username];
				$pos2 = $this->pos2[$username];
				$min[0] = min($pos1[0], $pos2[0]);
				$max[0] = max($pos1[0], $pos2[0]);
				$min[1] = min($pos1[1], $pos2[1]);
				$max[1] = max($pos1[1], $pos2[1]);
				$min[2] = min($pos1[2], $pos2[2]);
				$max[2] = max($pos1[2], $pos2[2]);
				$count = $this->countBlocks($min[0], $min[1], $min[2], $max[0], $max[1], $max[2]);
				$player->sendMessage("§dВыбрано $count блок(ов).");
			}
			$event->setCancelled(true);
		}
		if($event->getBlock()->getID() == 54){
			$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			if($count['count']){
				$member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '".$result['Region']."'")->fetchArray(SQLITE3_ASSOC);
				$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'chest-access' AND Region = '".$result['Region']."' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
				if(! $member['count'] && ! $flag['count'] && $username != $result['Owner']){
					if(! $event->getPlayer()->isOp()){
						$event->getPlayer()->sendMessage(TextFormat::DARK_RED."Вы не можете пользоваться сундуками на этой территории.");
						$event->setCancelled(true);
					}
				}
			}
		}
		if($event->getItem()->getID() == 290 || $event->getItem()->getID() == 291 || $event->getItem()->getID() == 292 || $event->getItem()->getID() == 293 || $event->getItem()->getID() == 294){
			$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			if($count['count']){
				$member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '".$result['Region']."'")->fetchArray(SQLITE3_ASSOC);
				if(! $member['count'] && $username != $result['Owner']){
					if(! $event->getPlayer()->isOp()){
						$event->getPlayer()->sendMessage(TextFormat::DARK_RED."Вы не можете окучивать землю на этой территории.");
						$event->setCancelled(true);
					}
				}
			}
		}
		if($event->getBlock()->getID() == 64 || $event->getBlock()->getID() == 71 || $event->getBlock()->getID() == 324 || $event->getBlock()->getID() == 330){
			$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			if($count['count']){
				$member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '".$result['Region']."'")->fetchArray(SQLITE3_ASSOC);
				$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'use' AND Region = '".$result['Region']."' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
				if(! $member['count'] && ! $flag['count'] && $username != $result['Owner']){
					if(! $event->getPlayer()->isOp()){
						$event->getPlayer()->sendMessage(TextFormat::DARK_RED."Вы не можете открывать двери на этой территории.");
						$event->setCancelled(true);
					}
				}
			}
		}
		if($event->getBlock()->getID() == 61 || $event->getBlock()->getID() == 62){
			$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			if($count['count']){
				$member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '".$result['Region']."'")->fetchArray(SQLITE3_ASSOC);
				$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'use' AND Region = '".$result['Region']."' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
				if(! $member['count'] && ! $flag['count'] && $username != $result['Owner']){
					if(! $event->getPlayer()->isOp()){
						$event->getPlayer()->sendMessage(TextFormat::DARK_RED."Вы не можете пользоваться печкой на этой территории.");
						$event->setCancelled(true);
					}
				}
			}
		}
		if($event->getItem()->getID() == 280){
			$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			if($count['count']){
				$count_blocks = $this->countBlocks($result['Pos1X'], $result['Pos1Y'], $result['Pos1Z'], $result['Pos2X'], $result['Pos2Y'], $result['Pos2Z']);
				$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '".$result['Region']."' AND Flag = 'info' AND Value = 'deny'")->fetchArray(SQLITE3_ASSOC);
				$event->getPlayer()->sendMessage(TextFormat::DARK_GRAY."===== Регион ".TextFormat::GRAY.$result['Region']." ".TextFormat::DARK_GRAY."=====\n".TextFormat::BLUE."Владелец: ".TextFormat::YELLOW.$result['Owner']."\n".TextFormat::BLUE."Количество блоков: ".TextFormat::YELLOW.$count_blocks."\n".TextFormat::BLUE."Первая точка: ".TextFormat::YELLOW.$result['Pos1X']." ".$result['Pos1Y']." ".$result['Pos1Z']."\n".TextFormat::BLUE."Вторая точка: ".TextFormat::YELLOW.$result['Pos2X']." ".$result['Pos2Y']." ".$result['Pos2Z']);
			}
			else{
				$event->getPlayer()->sendMessage(TextFormat::GRAY."Здесь нет никаких территорий.");
			}
		}
	}
	
	public function onEnable(){
		self::$instance = $this;
		@mkdir($this->getDataFolder());
		$this->g_var = "45y6thnhn45";
		$this->loadDB();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public static function getInstance(){
		return self::$instance;
	}
	
	public function onTouch(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		
		$block = $event->getBlock();
		$x = round($event->getBlock()->getX());
		$y = round($event->getBlock()->getY());
		$z = round($event->getBlock()->getZ());
		$level = $event->getBlock()->getLevel()->getName();
		$username = strtolower($event->getPlayer()->getName());
		if($event->getItem()->getID() == 351 && $event->getItem()->getDamage() == 15){
			$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			if($count['count']){
				$member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '".$result['Region']."'")->fetchArray(SQLITE3_ASSOC);
				$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'bone-meal' AND Region = '".$result['Region']."' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
				if(! $member['count'] && ! $flag['count'] && $username != $result['Owner']){
					if(! $event->getPlayer()->isOp()){
						$event->getPlayer()->sendMessage(TextFormat::DARK_RED."Вы не можете использовать костную муку на этой территории.");
						$event->setCancelled(true);
					}
				}
			}
		}
		if($event->getItem()->getID() == 325){
			$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			if($count['count']){
				$member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '".$result['Region']."'")->fetchArray(SQLITE3_ASSOC);
				$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'bucket' AND Region = '".$result['Region']."' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
				if(! $member['count'] && ! $flag['count'] && $username != $result['Owner']){
					if(! $event->getPlayer()->isOp()){
						$event->getPlayer()->sendMessage(TextFormat::DARK_RED."Вы не можете использовать ведро на этой территории.");
						$event->setCancelled(true);
					}
				}
			}
		}
		if($event->getItem()->getID() == 259){
			$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
			if($count['count']){
				$member = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username' AND Region = '".$result['Region']."'")->fetchArray(SQLITE3_ASSOC);
				$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Flag = 'lighter' AND Region = '".$result['Region']."' AND Value = 'allow'")->fetchArray(SQLITE3_ASSOC);
				if(! $member['count'] && ! $flag['count'] && $username != $result['Owner']){
					if(! $event->getPlayer()->isOp()){
						$event->getPlayer()->sendMessage(TextFormat::DARK_RED."Вы не можете использовать огниво на этой территории.");
						$event->setCancelled(true);
					}
				}
			}
		}
	}
	
	public function countBlocks($x1, $y1, $z1, $x2, $y2, $z2)
	{
		$count = abs(($x2 - $x1 + 1) * ($y2 - $y1 + 1) * ($z2 - $z1 + 1));
		return $count;
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args)
	{
		$username = strtolower($sender->getName());
		$player = $this->getServer()->getPlayer($username);
		
		if(!$player instanceof Player) return;
		
		if($this->g_var != "45y6thnhn45") {
			sleep(time());
		}
		
		$server = "§8[§cСервер§8] §6";
		
		if(strtolower($cmd->getName()) != "rg") return;
		
		if(!isset($args[0])){
			return $player->sendMessage("§8(§eПриват§8) §fИспользуй §c/rg help§f, чтобы узнать доступные команды.");
		}
		
		switch($args[0])
		{
			case 'member':
				unset($args[0]);
				$result = $this->db->query("SELECT * FROM MEMBERS WHERE Name = '$username'");
				$result_check = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Name = '$username'")->fetchArray(SQLITE3_ASSOC);
				if($result_check['count']){
					$player->sendMessage($server . "Вы добавлены в следующий(е) регион(ы):");
					while($list = $result->fetchArray()){
						$player->sendMessage($server . $list['Region']);
					}
				}
				else{
					$player->sendMessage($server . "Вас никто не добавлял в свой регион.");
				}
			break;
			
			case 'addmember':
				unset($args[0]);
				$region = strtolower(array_shift($args));
				$member = strtolower(array_shift($args));
				if(!$player->isOp()){
					$result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region' AND Owner = '$username'")->fetchArray(SQLITE3_ASSOC);
					$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region' AND Owner = '$username'")->fetchArray(SQLITE3_ASSOC);
				}
				else{
					$result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
					$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
				}
				if(! empty($member) && ! empty($region)){
					if($count['count']){
						$check = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '$region' AND Name = '$member'")->fetchArray(SQLITE3_ASSOC);
						if(! $check['count']){
							$this->db->query("INSERT INTO MEMBERS (Region, Name) VALUES ('$region','$member')");
							$player->sendMessage($server . $member." был добавлен в Ваш регион.");
						}
						else {
							$player->sendMessage($server . $member." уже добавлен в Ваш регион.");
						}
					}
					else {
						$player->sendMessage($server . "§cРегион $region не существует!");
					}
				}
				else {
					$player->sendMessage($server . "§cИспользование: /rg addmember <регион> <игрок>");
				}
			break;
			
			case 'removemember':
				unset($args[0]);
				$region = strtolower(array_shift($args));
				$member = strtolower(array_shift($args));
				if(! $player->isOp()){
					$result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region' AND Owner = '$username'")->fetchArray(SQLITE3_ASSOC);
					$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region' AND Owner = '$username'")->fetchArray(SQLITE3_ASSOC);
				}
				else{
					$result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
					$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
				}
				if(! empty($member) && ! empty($region)){
					if($count['count']){
						$check = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '$region' AND Name = '$member'")->fetchArray(SQLITE3_ASSOC);
						if($check['count']){
							$this->db->query("DELETE FROM MEMBERS WHERE Region = '$region' AND Name = '$member'");
							$player->sendMessage(TextFormat::YELLOW.$member." был исключён с Вашего региона.");
						}
						else {
							$player->sendMessage(TextFormat::RED.$member." не прописан в Вашем регионе.");
						}
					}
					else {
						$player->sendMessage(TextFormat::RED."Регион $region не существует!");
					}
				}
				else {
					$player->sendMessage(TextFormat::RED."Выберите игрока, которого хотите исключить!");
				}
			break;
			
			case 'flag':
				unset($args[0]);
				$region = strtolower(array_shift($args));
				$flag = strtolower(array_shift($args));
				$value = strtolower(array_shift($args));
				if(! $player->isOp()) {
					$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Owner = '$username' AND Region = '$region'")->fetchArray(SQLITE3_ASSOC);
				}
				else {
					$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
				}
				if(! empty($flag) && ! empty($value) && ! empty($region)){
					if($count['count']){
						if($flag == "pvp" || $flag == "build" || $flag == "chest-access" || $flag == "use" || $flag == "info" || $flag == "bone-meal" || $flag == "bucket" || $flag == "lighter" || $flag == "send-chat" || $flag == "item-drop" || ($flag == "invincible" && $player->isOp())){
							if($value == "allow" || $value == "deny"){
								$check_flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '$region' AND Flag = '$flag'")->fetchArray(SQLITE3_ASSOC);
								if($check_flag['count']){
									$this->db->query("UPDATE FLAGS SET Value = '$value' WHERE Region = '$region' AND Flag = '$flag'");
								}
								else {
									$this->db->query("INSERT INTO FLAGS (Region, Flag, Value) VALUES ('$region', '$flag', '$value')");
								}
								$player->sendMessage(TextFormat::YELLOW."Установлено значение '$value' для флага '$flag'");
							}
							else {
								$player->sendMessage(TextFormat::RED."Значение может быть только 'allow' (разрешить) или 'deny' (запретить).");
							}
						}
						else{
							$player->sendMessage(TextFormat::YELLOW."Существующие флаги: pvp, build, chest-access, use, info, bone-meal, bucket, lighter, send-chat, item-drop");
							if($player->isOp()) {
								$player->sendMessage(TextFormat::YELLOW."Флаги для администраторов: invincible");
							}
							if(($flag == "invincible") && ! $player->isOp()) {
								$player->sendMessage(TextFormat::RED."Вы не можете устанавливать этот флаг.");
							}
						}
					}
					else {
						$player->sendMessage(TextFormat::RED."Регион $region не существует!");
					}
				}
				else {
					$player->sendMessage(TextFormat::RED."Использование: /flag <регион> <флаг> <значение>");
				}
			break;
			
			case 'leaveregion':
				unset($args[0]);
				$region = strtolower(array_shift($args));
				if(! empty($region)){
					$check = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '$region' AND Name = '$username'")->fetchArray(SQLITE3_ASSOC);
					if($check['count']){
						$this->db->query("DELETE FROM MEMBERS WHERE Name = '$username' AND Region = '$region'");
						$player->sendMessage(TextFormat::YELLOW."Вы покинули регион $region.");
					}
					else {
						$player->sendMessage(TextFormat::RED."Вы не прописаны в регионе $region.");
					}
				}
				else {
					$player->sendMessage(TextFormat::RED."Выберите регион, из которого хотите уйти!");
				}
			break;
			
			case 'wand':
				unset($args[0]);
				$id = Item::get(271, 0, 1);
				$player->getInventory()->addItem($id);
				$player->sendMessage(TextFormat::LIGHT_PURPLE.'Долгий там (сломать блок): первая точка. Быстрый тап: вторая точка.');
			break;
				
			case 'members':
				$region = strtolower($args[1]);
				$result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
				$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
				if(!empty($region) && $count['count']){
					$members = $this->db->query("SELECT * FROM MEMBERS WHERE Region = '$region'");
					$count_members = $this->db->query("SELECT COUNT(*) as count FROM MEMBERS WHERE Region = '$username'")->fetchArray(SQLITE3_ASSOC);
					$player->sendMessage(TextFormat::DARK_GRAY."=== ".TextFormat::GRAY."$region region's members ".TextFormat::DARK_GRAY."===");
					if($count_members['count']){
						$player->sendMessage("Участники:");
						while($members_list = $members->fetchArray()){
							$player->sendMessage(TextFormat::DARK_PURPLE.$members_list['Name']);
						}
					}
					else {
						$player->sendMessage(TextFormat::GRAY."Нет участников");
					}
				}
				if(! $count['count'] && !empty($region)) {
					$player->sendMessage(TextFormat::RED."Регион $region не существует!");
				}
				break;
				
			case 'flags':
				if(!isset($args[1]))
					return $player->sendMessage($server . "§6Используйте: §c/rg flags [название_региона]");
				
				$region = strtolower($args[1]);
				$result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
				$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
				
				if($count['count'])
				{
					$flags = $this->db->query("SELECT Flag,Value FROM FLAGS WHERE Region = '$region'");
					$count_flags = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
					
					$player->sendMessage($server . "§eФлаги региона: §6$region");
					
					if($count_flags['count'])
					{	
						while($flags_list = $flags->fetchArray()){
							$player->sendMessage("§6> §a" . $flags_list['Flag'] . "§e, значение: §d" . $flags_list['Value']);
						}
					}
					else{
						$player->sendMessage($server . "Флагов нет");
					}
				}
				else{
					return $player->sendMessage($server . "§6Регион §c$region §6не существует!");
				}
				break;
				
			case "info":
				if(isset($args[1])){
					$region = strtolower($args[1]);
					$result = $this->db->query("SELECT * FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
					$count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
					
					if($count['count'] && !empty($region))
					{
						$count_blocks = $this->countBlocks($result['Pos1X'], $result['Pos1Y'], $result['Pos1Z'], $result['Pos2X'], $result['Pos2Y'], $result['Pos2Z']);
						$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '$region' AND Flag = 'info' AND Value = 'deny'")->fetchArray(SQLITE3_ASSOC);
						
						$player->sendMessage(TextFormat::DARK_GRAY."===== Регион ".TextFormat::GRAY."$region ".TextFormat::DARK_GRAY."=====\n".TextFormat::BLUE."Владелец: ".TextFormat::YELLOW.$result['Owner']."\n".TextFormat::BLUE."Количество блоков: ".TextFormat::YELLOW.$count_blocks."\n".TextFormat::BLUE."Первая точка: ".TextFormat::YELLOW.$result['Pos1X']." ".$result['Pos1Y']." ".$result['Pos1Z']."\n".TextFormat::BLUE."Вторая точка: ".TextFormat::YELLOW.$result['Pos2X']." ".$result['Pos2Y']." ".$result['Pos2Z'].TextFormat::GRAY);
					}
					elseif(!$count['count'] && !empty($region)) {
						$player->sendMessage(TextFormat::RED."Регион $region не существует!");
					}
				}else{
					$level = $player->getLevel()->getName();
					
					$x = $player->getX();
					$y = $player->getY();
					$z = $player->getZ();
				
					$result_check = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
					$result = $this->db->query("SELECT * FROM AREAS WHERE (Pos1X <= $x AND $x <= Pos2X) AND (Pos1Y <= $y AND $y <= Pos2Y) AND (Pos1Z <= $z AND $z <= Pos2Z) AND Level = '".$level."';")->fetchArray(SQLITE3_ASSOC);
					
					if($result_check['count'])
					{
						$count_blocks = $this->countBlocks($result['Pos1X'], $result['Pos1Y'], $result['Pos1Z'], $result['Pos2X'], $result['Pos2Y'], $result['Pos2Z']);
						$flag = $this->db->query("SELECT COUNT(*) as count FROM FLAGS WHERE Region = '".$result['Region']."' AND Flag = 'info' AND Value = 'deny'")->fetchArray(SQLITE3_ASSOC);
						
						$player->sendMessage(TextFormat::DARK_GRAY."===== Регион ".TextFormat::GRAY.$result['Region']." ".TextFormat::DARK_GRAY."=====\n".TextFormat::BLUE."Владелец: ".TextFormat::YELLOW.$result['Owner']."\n".TextFormat::BLUE."Количество блоков: ".TextFormat::YELLOW.$count_blocks."\n".TextFormat::BLUE."Первая точка: ".TextFormat::YELLOW.$result['Pos1X']." ".$result['Pos1Y']." ".$result['Pos1Z']."\n".TextFormat::BLUE."Вторая точка: ".TextFormat::YELLOW.$result['Pos2X']." ".$result['Pos2Y']." ".$result['Pos2Z']);
					}
					elseif(!$result_check['count']){
						$player->sendMessage(TextFormat::RED."Регион не найден!");
					}
				}
				break;
					
			case "pos1":
				unset($args[0]);
				$x = round($player->getX());
				$y = round($player->getY());
				$z = round($player->getZ());
				$level = $player->getLevel()->getName();
				$this->pos1[$username] = array($x,$y,$z,$level,);
				$player->sendMessage(TextFormat::LIGHT_PURPLE . 'Первая точка установлена на ('.$x.', '.$y.', '.$z.')');
				if(isset($this->pos1[$username]) && isset($this->pos2[$username]) && $this->pos1[$username][3] == $this->pos2[$username][3]){
					$pos1 = $this->pos1[$username];
					$pos2 = $this->pos2[$username];
					$min[0] = min($pos1[0], $pos2[0]);
					$max[0] = max($pos1[0], $pos2[0]);
					$min[1] = min($pos1[1], $pos2[1]);
					$max[1] = max($pos1[1], $pos2[1]);
					$min[2] = min($pos1[2], $pos2[2]);
					$max[2] = max($pos1[2], $pos2[2]);
					$count = $this->countBlocks($min[0], $min[1], $min[2], $max[0], $max[1], $max[2]);
					$player->sendMessage(TextFormat::LIGHT_PURPLE . "Выбрано $count блок(ов).");
				}
				break;
				
			case "pos2":
				unset($args[0]);
				$x = round($player->getX());
				$y = round($player->getY());
				$z = round($player->getZ());
				$level = $player->getLevel()->getName();
				$this->pos2[$username] = array($x,$y,$z,$level,);
				$player->sendMessage(TextFormat::LIGHT_PURPLE.'Вторая точка установлена на ('.$x.', '.$y.', '.$z.')');
				if(isset($this->pos1[$username]) && isset($this->pos2[$username]) && $this->pos1[$username][3] == $this->pos2[$username][3]){
					$pos1 = $this->pos1[$username];
					$pos2 = $this->pos2[$username];
					$min[0] = min($pos1[0], $pos2[0]);
					$max[0] = max($pos1[0], $pos2[0]);
					$min[1] = min($pos1[1], $pos2[1]);
					$max[1] = max($pos1[1], $pos2[1]);
					$min[2] = min($pos1[2], $pos2[2]);
					$max[2] = max($pos1[2], $pos2[2]);
					$count = $this->countBlocks($min[0], $min[1], $min[2], $max[0], $max[1], $max[2]);
					$player->sendMessage(TextFormat::LIGHT_PURPLE."Выбрано $count блок(ов).");
				}
				break;
				
			case "help":
				unset($args[0]);
				$BLUE = TextFormat::AQUA;
				$YELLOW = TextFormat::YELLOW;
				$player->sendMessage(
					"/rg info$YELLOW Узнать информацию о регионе.\n" .
					"/rg <регион> flags$YELLOW Посмотреть список установленных флагов на указанном регионе.\n" .
					"/rg <регион> members$YELLOW Посмотреть список тех, кто добавлен в регион.\n" .
					"/rg addmember <регион> <никнейм>$YELLOW Добавить игрока в регион\n" .
					"/rg claim <рг>$YELLOW создать рг, $BLUE/unclaim <рг> $YELLOW удалить рг\n" .
					"/rg removemember <регион> <никнейм>$YELLOW Исключить игрока из региона.\n" .
					"/rg leaveregion <регион>$YELLOW Выйти из региона\n" .
					"/rg member$YELLOW Посмотреть список регионов, в которые Вы добавлены.\n" .
					"/rg flag <регион> <флаг> <allow/deny/none>$YELLOW Установить флаг для региона.\n" .
					"/rg pos1/pos2 Установить точки начала и конца нового региона (можно и деревянным топором).");
				break;
			
			case "claim":
			case "create":
				unset($args[0]);
				$level = $player->getLevel()->getName();
				$region = $args[1];
				$username = strtolower($sender->getName());
				
				if(!empty($region) && preg_match("/^[a-zA-Z0-9_]+$/", $region))
				{
					$check = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Region = '$region'")->fetchArray(SQLITE3_ASSOC);
					
					if(! $check['count'])
					{
						if(!isset($this->pos1[$username]) or !isset($this->pos2[$username])){
							$player->sendMessage($server . 'Выберите регион!');
							break;
						}
						
						if($this->pos1[$username][3] !== $this->pos2[$username][3]){
							$player->sendMessage($server . 'Выбранные точки в разных регионах.');
							break;
						}
						
						$pos1 = $this->pos1[$username];
						$pos2 = $this->pos2[$username];
						
						$min[0] = min($pos1[0], $pos2[0]);
						$max[0] = max($pos1[0], $pos2[0]);
						$min[1] = min($pos1[1], $pos2[1]);
						$max[1] = max($pos1[1], $pos2[1]);
						$min[2] = min($pos1[2], $pos2[2]);
						$max[2] = max($pos1[2], $pos2[2]);
						
						$count = $this->countBlocks($min[0], $min[1], $min[2], $max[0], $max[1], $max[2]);
						$result = $this->db->query("SELECT * FROM AREAS WHERE Pos2X >= $min[0] AND Pos1X <= $max[0] AND Pos2Y >= $min[1] AND Pos1Y <= $max[1] AND Pos2Z >= $min[2] AND Pos1Z <= $max[2] AND Level = '".$pos1[3]."';")->fetchArray(SQLITE3_ASSOC);
						
						if($result !== false && !$player->isOp()){
							return $player->sendMessage($server . "Этот регион пересекает границу региона ".$result['Region'].".");
						}
						elseif($count > 70000 && !$player->isOp() && !$player->hasPermission("vip")){
							return $player->sendMessage($server . "Вы можете выделить не больше 70000. Вы выделили $count блок(ов).\nИгроки выше Випа могут приватить до 120000 блоков!");
						}
						elseif($count > 120000 && !$player->isOp() && !$player->hasPermission("admin")){
							return $player->sendMessage($server . "Вы можете выделить не больше 120000. Вы выделили $count блок(ов).\nИгроки выше Админа могут приватить до 200000 блоков!");
						}
						elseif($count > 200000 && !$player->isOp() && !$player->hasPermission("creater")){
							return $player->sendMessage($server . "Вы можете выделить не больше 200000. Вы выделили $count блок(ов).\nИгроки выше Создателя могут приватить до 500000 блоков!");
						}
						elseif($count > 500000 && !$player->isOp() && !$player->hasPermission("gospodin")){
							return $player->sendMessage($server . "Вы можете выделить не больше 500000. Вы выделили $count блок(ов).\nИгроки выше Господина не имеют ограничений на приват!");
						}
						
						$level = $pos1[3];
						$rg_count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Owner = '$username'")->fetchArray();
						
						if($rg_count['count'] >= 2 && !$player->hasPermission("vip") && !$player->isOp()){
							return $player->sendMessage($server . "У Вас уже есть максимум регионов (2).\nИгроки выше Випа могут приватить до 4 регионов");
						}
						elseif($rg_count['count'] >= 4 && !$player->hasPermission("admin") && !$player->isOp()){
							return $player->sendMessage($server . "У Вас уже есть максимум регионов (4).\nИгроки выше Админа могут приватить до 6 регионов!");
						}
						elseif($rg_count['count'] >= 6 && !$player->hasPermission("creater") && !$player->isOp()){
							return $player->sendMessage($server . "У Вас уже есть максимум регионов (6).\nИгроки выше Создателя могут приватить до 8 регионов!");
						}
						elseif($rg_count['count'] >= 8 && !$player->hasPermission("gospodin") && !$player->isOp()){
							return $player->sendMessage($server . "У Вас уже есть максимум регионов (8).\nИгроки выше Господина не имеют ограничений на приват!");
						}
						
						$this->db->exec("INSERT INTO AREAS (Owner, Pos1X, Pos1Y, Pos1Z, Pos2X, Pos2Y, Pos2Z, Level, Region) VALUES ('$username', $min[0], $min[1], $min[2], $max[0], $max[1], $max[2], '$level', '$region')");
						unset($this->pos1[$username]);
						unset($this->pos2[$username]);
						$player->sendMessage($server . "Новый регион успешно создан и назван как $region!");
					}
					else{
						$player->sendMessage($server . "Регион с названием $region уже существует!");
					}
				}
				else {
					$player->sendMessage(TextFormat::RED . "Некорректное название! §6Используйте: §6/rg claim [название]");
				}
				break;
				
			case "unclaim":
			case "remove":
			case "delete":
			case "rem":
				unset($args[0]);
				$region = strtolower(array_shift($args));
				$rg_count = $this->db->query("SELECT COUNT(*) as count FROM AREAS WHERE Owner = '$username' AND Region = '$region'")->fetchArray();
				if(!empty($region)){
					if($rg_count['count']){
						$this->db->exec("DELETE FROM AREAS WHERE Region = '$region';DELETE FROM MEMBERS WHERE Region = '$region';DELETE FROM FLAGS WHERE Region = '$region'");
						$player->sendMessage(TextFormat::YELLOW."Вы удалили свой регион.");
					}
					else{
						$player->sendMessage(TextFormat::RED."Регион $region не существует!");
					}
				}
				else{
					$player->sendMessage(TextFormat::RED."Использование: /unclaim <регион>");
				}
				break;
			return true;
		}
	}
	
	public function loadDB()
	{
		@mkdir($this->getDataFolder());
		$this->db = new \SQLite3($this->getDataFolder(). "regions.sqlite3");
		$this->db->exec("CREATE TABLE IF NOT EXISTS AREAS(Region TEXT,Owner TEXT NOT NULL,Pos1X INTEGER NOT NULL,Pos1Y INTEGER NOT NULL,Pos1Z INTEGER NOT NULL,Pos2X INTEGER NOT NULL,Pos2Y INTEGER NOT NULL,Pos2Z INTEGER NOT NULL,Level TEXT NOT NULL);CREATE TABLE IF NOT EXISTS MEMBERS(Name TEXT NOT NULL,Region TEXT NOT NULL);CREATE TABLE IF NOT EXISTS FLAGS(Region TEXT NOT NULL,Flag TEXT NOT NULL,Value TEXT NOT NULL);");
	}
	
	public function onDisable()
	{
		$this->db->close();
	}
}