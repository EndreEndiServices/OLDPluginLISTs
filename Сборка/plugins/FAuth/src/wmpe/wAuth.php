<?php

namespace wmpe;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as F;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\block\BlockBreakEvent; 
use pocketmine\event\player\PlayerInteractEvent; 

	class wAuth extends PluginBase implements Listener {
		private $db, $users = array(), $reg = array();

		public function onEnable() {
			if(!is_dir($this->getDataFolder()))
				@mkdir($this->getDataFolder());
			$this->config = (new Config($this->getDataFolder()."config.yml", Config::YAML, ["salt" => "ТвойКлючШифрования"]))->getAll();
			$this->db = new \SQLite3($this->getDataFolder()."users.db");
			$this->db->exec(stream_get_contents($this->getResource("database.sql")));
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}

		public function onPlayerMove(PlayerMoveEvent $event) {
			if(!isset($this->users[strtolower($event->getPlayer()->getName())])) 
				$event->setCancelled(true);
		}
public function onDrop(PlayerDropItemEvent $e){
			if(!isset($this->users[strtolower($e->getPlayer()->getName())])) 
				$e->setCancelled(true);
		}
public function onBreak(BlockBreakEvent $e){
			if(!isset($this->users[strtolower($e->getPlayer()->getName())])) 
				$e->setCancelled(true);
}

public function onTap(	PlayerInteractEvent $e){
			if(!isset($this->users[strtolower($e->getPlayer()->getName())])) 
				$e->setCancelled(true);
}
		public function onPlayerJoin(PlayerJoinEvent $event) {
			$player = $event->getPlayer();
			$sql = $this->db->prepare("SELECT * FROM `users` WHERE `nickname` = :nickname");
			$sql->bindValue(":nickname", strtolower($player->getName()), SQLITE3_TEXT);
			$sql = $sql->execute();
			$user = $sql->fetchArray(SQLITE3_ASSOC);
			if(isset($user["nickname"])) {
				$ip = $player->getAddress();
				if($ip == $user["ipLast"]) {
					$this->users[strtolower($player->getName())] = [
						"pass" => $user["password"],
						"ip" => $ip
					];
					$player->sendMessage("§8(§bMine§cScar§8)§f Вы уже вводили свой пароль! §aПриятной игры!");
				} else
					$player->sendMessage("§8(§bMine§cScar§8)§f Введите §bсвой пароль §fв чат, который вводили при регистрации.");
			} else $player->sendMessage("§8(§bMine§cScar§8)§f Придумайте и введите §bсвой пароль §fв чат, для регистрации.");
			$sql->finalize();
		}

		public function onPlayerQuit(PlayerQuitEvent $event) {
			unset($this->users[strtolower($event->getPlayer()->getName())]);
		}

		public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) {
			$login = "§8(§bMine§cScar§8)§f Введите §bсвой пароль §fв чат, который вводили при регистрации.";
			$player = $event->getPlayer();
			$name = strtolower($player->getName());
			$ip = $player->getAddress();
			$msg = $event->getMessage();
			if(!isset($this->users[$name])) {
				if(count(explode("/", $msg)) > 1) {
					$event->setCancelled(true);
					$player->sendMessage($login);
				} else {
					$msg = explode(" ", $msg);
					if(count($msg) == 1) {
						$sql = $this->db->prepare("SELECT * FROM `users` WHERE `nickname` = :nickname");
						$sql->bindValue(":nickname", $name, SQLITE3_TEXT);
						$sql = $sql->execute();
						if($sql instanceof \SQLite3Result) {
							$pass = crypt(md5($msg[0]), sha1($this->config["salt"]));
							$user = $sql->fetchArray(SQLITE3_ASSOC);
							if(!empty($user["nickname"])) {
								if($pass == $user["password"]) {
									$upd = $this->db->prepare("UPDATE `users` SET `ipLast` = :ip WHERE `nickname` = :nickname");
									$upd->bindValue(":ip", $ip);
									$upd->bindValue(":nickname", $name);
									$upd = $upd->execute();
									$upd->finalize();
									$this->users[$name] = [
										"pass" => $pass,
										"ip" => $ip
									];
									$player->sendMessage("§8(§bMine§cScar§8)§f Вы успешно §aавторизировались §fна сервере. Приятной игры!");
								} else $player->sendMessage("§8(§bMine§cScar§8)§f Вы ввели §cневерный пароль.");
							} else {
								if(!isset($this->reg[$name])) {
									$this->reg[$name] = $pass;
									$player->sendMessage("§8(§bMine§cScar§8)§f Введите §c<свой пароль> §fв чат повторно!");
								} else {
									if($pass == $this->reg[$name]) {
										$add = $this->db->prepare("INSERT INTO `users`(`nickname`, `password`, `ipReg`, `ipLast`) VALUES(:nickname, :password, :ip, :ip)");
										$add->bindValue(":nickname", $name);
										$add->bindValue(":password", $pass);
										$add->bindValue(":ip", $ip);
										$add = $add->execute();
										$add->finalize();
										$this->users[$name] = [
											"pass" => $pass,
											"ip" => $ip
										];
										$player->sendMessage("§8(§bMine§cScar§8)§f Вы успешно §aзарегистрировались, §fне забудьте свой пароль!");
									} else {
										unset($this->reg[$name]);
										$player->sendMessage("§8(§bMine§cScar§8)§f Вы ввели §cневерный пароль.");
									}
								}
							}
						}
						$sql->finalize();
					} else $player->sendMessage($login);
				}
				$event->setCancelled(true);
			} else {
				if($ip != $this->users[$name]["ip"]) {
					$event->setCancelled(true);
					$player->sendMessage($login);
				}
			
		}}

	}

?>