<?php

namespace Just;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\PluginTask;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class Main extends PluginBase implements Listener 
{
 public function onEnable()
 {
	$this->getLogger()->info(TextFormat::GOLD."Плагин запущен!");
	$this->getServer()->getPluginManager()->registerEvents($this, $this);

	if(file_exists($this->getPluginDir()) == false) mkdir($this->getPluginDir());
	if(file_exists($this->getUsersDir()) == false) mkdir($this->getUsersDir());
 }
 public function isLogined($player)
 {
 return $player->logined;
 }
 public function getPluginDir()
 {
	 return "plugins/reglog/";
 }
 public function getUsersDir()
 {
	 return "plugins/reglog/users/";
 }
 public function alert($player, $text)
 {
	 $player->sendMessage($text);
 }
 public function lock($event)
 {
	 $event->setCancelled(true);
 }
 public function getHelloMessage()
 {
	 if(file_exists($this->getPluginDir()."hello_message.txt") == false)
	 {
		 $fp = fopen($this->getPluginDir()."hello_message.txt", "w"); fwrite($fp, $default_hello_text);
	 }
	 else $default_hello_text = file_get_contents($this->getPluginDir()."hello_message.txt");
	 return $default_hello_text;
 }
 
 public function onCommand(CommandSender $sender, Command $cmd, $label, array $cmds)
 {
	 if($cmd == "ch")
	 {
		 if(!Empty($cmds[0]))
		 {
			 file_put_contents($this->getUsersDir().strtolower($sender->getName()).".user", $cmds[0]);
			 $sender->sendMessage(TextFormat::GREEN."§7(§bFree§cCraft§7) §aВаш§f пароль успешно изменён на: ".TextFormat::GOLD.$cmds[0]);
		 }
		 else $sender->sendMessage(TextFormat::RED."§7(§bFree§cCraft§7) §fИспользование: ".TextFormat::GOLD."§b/ch §7(§aновый пароль§7)");
	 }
 }
 
/*
 Можете изменить сообщения на свои
*/

 public function ifSteve(PlayerPreLoginEvent $e)
 {
	 $nick = $e->getPlayer()->getName();
	 if(strtolower($nick) == "steve")
	 {
	 $e->getPlayer()->close($e->getPlayer()->getLeaveMessage(), TextFormat::YELLOW."§bНик:§a Steve §cзаблокирован,§e смените ник§f!");
     $e->setCancelled(true);
	 }
 }
 public function InitPerson(PlayerJoinEvent $e)
 {
	 $nick = $e->getPlayer()->getName();
	 $person = $e->getPlayer();
	 $person->logined = false;
	 
	 $this->alert($person, $this->getHelloMessage());
	 
	 if(file_exists($this->getUsersDir().strtolower($nick).".user"))
	 {
		 $person->regwait = false;
		 $this->alert($person, TextFormat::GOLD."§7(§bFree§cCraft§7) §eПожалуйста, §6войдите §eна сервер.");
		 $this->alert($person, TextFormat::YELLOW."§7(§bFree§cCraft§7) §aНапишите пароль в §bчат,§a чтобы §aавторизоваться§f!");
	 }
	 else
	 {
		 $person->regwait = true;
		 $this->alert($person, TextFormat::GOLD."§7(§bFree§cCraft§7) §aВы§c не зарегистрированы§f!");
		 $this->alert($person, TextFormat::YELLOW."§7(§bFree§cCraft§7) §aЧтобы§a зарегистрироваться, §aнапишите §aсвой§a пароль в§b чат§f!");
	 }
 }
 
 public function ChatWaiter(PlayerCommandPreprocessEvent $e)
 {
	 $nick = $e->getPlayer()->getName();
	 $person = $e->getPlayer();
	 //контроль профилей и работа с ними
	 if(substr($e->getMessage(), 0, 1) == "/" and $person->logined == false) $person->close($person->getLeaveMessage(), TextFormat::GOLD."Сейчас команды недоступны");
	 else {
	   if($person->regwait == true)
	   {
		 $fp = fopen($this->getUsersDir().strtolower($nick).".user", "w"); fwrite($fp, $e->getMessage());
		 $this->alert($person, TextFormat::GREEN."§7(§bFree§cCraft§7) §aВы§b успешно§f зарегистрировались§c!");
		 $this->alert($person, TextFormat::BLUE."§7(§aЧат§7) §aВаш ник: ".TextFormat::GOLD.$nick);
		 $this->alert($person, TextFormat::BLUE."§7(§aЧат§7) §aВаш пароль: ".TextFormat::GOLD.$e->getMessage());
		 $person->logined = true;
		 $person->regwait = false;
		 $e->setCancelled(true);
	   }
	   else 
	   {
		 if($e->getMessage() == file_get_contents($this->getUsersDir().strtolower($nick).".user") and $person->logined == false)
		 {
			$this->alert($person, TextFormat::GREEN."§7(§bFree§cCraft§7)§a Вы §bуспешно §aвошли в §aсвой§e аккаунт§f!");

			$person->logined = true;
			$e->setCancelled(true);
		 }
		 else if($e->getMessage() != file_get_contents($this->getUsersDir().strtolower($nick).".user") and $person->logined == false) 
		 {
			 $e->setCancelled(true);
			 $person->close($person->getLeaveMessage(), TextFormat::RED."§7(§bFree§cCraft§7)§a Вы§f ввели неверный пароль§f!");
		 }
		 else return;
	   }
	 }
 }
 public function noMove(PlayerMoveEvent $e)
 {
	 if($e->getPlayer()->logined == false) $this->lock($e);
 }
 public function noPlace(BlockPlaceEvent $e)
 {
	 if($e->getPlayer()->logined == false) $this->lock($e);
 }
 public function noBreak(BlockBreakEvent $e)
 {
	 if($e->getPlayer()->logined == false) $this->lock($e);
 }
 public function noEat(PlayerItemConsumeEvent $e)
 {
	 if($e->getPlayer()->logined == false) $this->lock($e);
 }
}
?>