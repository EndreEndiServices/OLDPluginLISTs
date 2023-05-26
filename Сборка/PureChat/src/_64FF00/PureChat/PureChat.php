<?php
namespace _64FF00\PureChat;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\scheduler\CallbackTask;

use Richen\Richen\Marry;
use Richen\Clans\ClanMain;

class PureChat extends PluginBase
{	
	public $flood, $mute;
	
    public function onLoad()
	{
		$this->saveDefaultConfig();
	}
    
	public function onEnable()
	{
		$this->PurePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
		$this->getServer()->getPluginManager()->registerEvents(new ChatListener($this), $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "flood")), 20 * 2);
	}
	
	public function flood()
	{
		foreach($this->getServer()->getOnlinePlayers() as $player)
		{
			if(isset($this->flood[strtolower($player->getName())])){
				unset($this->flood[strtolower($player->getName())]);
			}
		}
	}
	
	public function formatMessage(Player $player, $message)
	{
		$group = $this->PurePerms->getUser($player)->getGroup(null);
		$group = $group->getName();
		
		$chatFormat = $this->getConfig()->getNested("groups.$group.chat");
		
		$marry = Server::getInstance()->getPluginManager()->getPlugin("Marry");
		$marry = $marry->getPrefix(strtolower($player->getName()));
		
		$clan = new ClanMain; $clan = $clan::getInstance();
		
		if(!$clan->isInClan($player->getName())){
			$clan = "";
		}elseif($clan->isLeader($player->getName())){
			$clan = "§8[§7Лидер " . $clan->getPlayerClan($player->getName()) . "§8]";
		}elseif($clan->isMember($player)){
			$clan = "§8[§7Учас. " . $clan->getPlayerClan($player->getName()) . "§8]";
		}
		
		$faction = "";
		
		$name = $player->getName();
		
		$chatFormat = str_replace("{clan}", $clan, $chatFormat);
		$chatFormat = str_replace("{name}", $name, $chatFormat);
		$chatFormat = str_replace("{marry}", $marry, $chatFormat);
		$chatFormat = str_replace("{message}", $message, $chatFormat);
		$chatFormat = str_replace("{faction}", $faction, $chatFormat);
		
		return $chatFormat;
	}

	public function getNameTag(Player $player)
	{
		$group = $this->PurePerms->getUser($player)->getGroup(null);
		$group = $group->getName();
		
		$nameTag = $this->getConfig()->getNested("groups.$group.nametag");
		
		$marry = Server::getInstance()->getPluginManager()->getPlugin("Marry");
		$marry = $marry->getPrefix(strtolower($player->getName()));
		
		$clan = new ClanMain; $clan = $clan::getInstance();
		
		if(!$clan->isInClan($player->getName())){
			$clan = "";
		}elseif($clan->isLeader($player->getName())){
			$clan = "§8[§7Лидер " . $clan->getPlayerClan($player->getName()) . "§8]";
		}elseif($clan->isMember($player)){
			$clan = "§8[§7Учас. " . $clan->getPlayerClan($player->getName()) . "§8]";
		}
		
		$faction = "";
		
		$name = $player->getName();
		
		$nameTag = str_replace("{clan}", $clan, $nameTag);
		$nameTag = str_replace("{name}", $name, $nameTag);
		$nameTag = str_replace("{marry}", $marry, $nameTag);
		$nameTag = str_replace("{faction}", $faction, $nameTag);
		
		return $nameTag;
	}
}