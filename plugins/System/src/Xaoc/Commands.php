<?php

namespace Xaoc;

use Xaoc\MainClass;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerChatEvent;

class Commands extends PluginBase implements Listener
{
	public function __construct(MainClass $plugin){
		$this->plugin = $plugin;
		$this->block = "";
 	}

	public function onBanJoin(PlayerPreLoginEvent $e) {
		$p = $e->getPlayer();
		if($this->plugin->bandata->exists(strtolower($p->getName()))) {
			$p->close("","§cВаш аккаунт заблокирован!\n§cПричина: §e".$this->plugin->bandata->getAll()[strtolower($p->getName())]["reason"]."\n§cЗабанил: §e".$this->plugin->bandata->getAll()[strtolower($p->getName())]["who"]."\n§cРазбан: §eСКОРО..");			
		}
}

 	public function onSpamChat(PlayerChatEvent $e) {
		$p = $e->getPlayer();
		if(isset($this->plugin->mutes[strtolower($p->getName())])){
			$p->sendMessage("§8[§cСистема§8] §fВы не можете писать в чат");
			$e->setCancelled();
			}
   }

	public function onCommand(CommandSender $sender, Command $command, $label, array $args): bool{
		switch($command){
			case "mute":
			if(count($args) < 2){
				$sender->sendMessage("§8[§cСистема§8] §fИспользование: §2/mute <игрок> <причина>");
 				return true;
			}
			$muted = $this->getServer()->getPlayer($args[0]);
			if($muted instanceof Player) {
				unset($args[0]);
				$reason = implode(" ", $args);
				if(!$muted->hasPermission("bansystem.antimute") || $sender->hasPermission("bansystem.mute")){
					if(!isset($this->plugin->mutes[strtolower($muted->getName())])){
						$this->getServer()->broadcastMessage("§8[§cСистема§8] §fИгрок §e".$sender->getName()." §fдал мут игроку §6".$muted->getName()."§f. Причина: §b".$reason);
 						$this->plugin->mutes[strtolower($muted->getName())] = true;
						$muted->sendMessage("§8[§cСистема§8] §fВы поучили мут от игрока §a".$sender->getName()."§f. Причина: §b".$reason);
						$muted->sendMessage("§8[§cСистема§8] §fМут пропадет после рестарта сервера");
					}else{
						$sender->sendMessage("§8[§cСистема§8] §fЭтот игрок и так имеет мут");
					}
				}else{
					$sender->sendMessage("§8[§cСистема§8] §fЭтот игрок не может получить мут");
				}
			}else{
				$sender->sendMessage("§8[§cСистема§8] §fИгрок не онлайн");
			}
			break;
			
			case "unmute":
			if(count($args) < 1){
				$sender->sendMessage("§8[§cСистема§8] §fИспользование: §2/unmute <игрок>");
 					return true;
				}
				$muted = $this->getServer()->getPlayer($args[0]);
				if($muted instanceof Player) {
					if(isset($this->plugin->mutes[strtolower($muted->getName())])){
						$this->getServer()->broadcastMessage("§8[§cСистема§8]§f Игрок §e".$sender->getName()."§f убрал мут у игрока §6".$muted->getName());
						$muted->sendMessage("§8[§cСистема§8]§f Вы снова можете писать в чат");
 						unset($this->plugin->mutes[strtolower($muted->getName())]);
					}else{
						$sender->sendMessage("§8[§cСистема§8]§f Данный игрок не заткнут");
					}
				}else{
					$sender->sendMessage("§8[§cСистема§8] Игрок не онлайн");
				}
				break;
			}
		}
	}