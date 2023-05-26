<?php
namespace _64FF00\PureChat;

use _64FF00\PurePerms\event\PPGroupChangedEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\Player;
use pocketmine\Server;

class ChatListener implements Listener
{
	private $plugin;
	
    public function __construct(PureChat $plugin){
		$this->plugin = $plugin;
	}
	
    public function onGroupChanged(PPGroupChangedEvent $event){
		$player = $event->getPlayer();
		if($player instanceof Player){
			$nameTag = $this->plugin->getNameTag($player);
			$player->setDisplayName($nameTag);
			$player->setNameTag($nameTag);
		}
	}
	
	public function onPreChatAndCommand(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		if(!$player->hasPermission("purechat.chat")){
			if(isset($this->plugin->flood[$name])){
				$event->setCancelled();
				return $player->sendMessage("§6Вам нельзя так часто использовать комманды и чат.");
			}
			$this->plugin->flood[$name] = true;
		}
	}
	
    public function onPlayerChat(PlayerChatEvent $event){
		$player = $event->getPlayer();
		$message = $event->getMessage();
		$name = $player->getName();
		
		if(!$player->hasPermission("purechat.chat"))
		{
			if(preg_match('@[A-z0-9]@u', $message)){
				$event->setCancelled();
				return $player->sendMessage("§6В чате запрещены английские буквы и символы.");
			}
			
			if(mb_strlen($message) < 2){
				$event->setCancelled();
				return $player->sendMessage("§6Нельзя отправлять в чат менее двух символов.");
			}
			
			$message = strtolower($message);
			$mat = "хуй,пизд,уеб,пидор,пидр,гандон,наху,далбаеб,долбаеб,ебать,ебал,сука,бля,ёб,соси,заеб";
			$mat = explode(",", $mat);
			foreach($mat as $mats){
				$message = str_replace($mats, "***", $message);
			}
		}
		
		$message = str_replace(":)", "§a☺§f", $message);
		$message = str_replace("))", "§a☺§f", $message);
		$message = str_replace(":(", "§c☹§f", $message);
		$message = str_replace("((", "§c☹§f", $message);
		$message = str_replace("<3", "§c❤§f", $message);
		$message = str_replace(".", "", $message);
		
		$marry = Server::getInstance()->getPluginManager()->getPlugin("Marry");
		
		$chatFormat = $this->plugin->formatMessage($player, $message);
		$event->setFormat($chatFormat);
	}
	
	public function onPlayerJoin(PlayerJoinEvent $event){
		$event->setJoinMessage(null);
		$player = $event->getPlayer();
		
		$nameTag = $this->plugin->getNameTag($player);
		$player->setDisplayName($nameTag);
		$player->setNameTag($nameTag);
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event){
		$event->setQuitMessage(null);
	}
}