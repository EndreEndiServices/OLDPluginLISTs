<?php

/**
 *     
 *    ███  ███  █      ███  █   █ ████   ███   ███   ███
 *        █     █     █   █ ██  █ █   █ █   █ █     █      ███  █  █
 *     █   ███  █     █████ █ █ █ █   █ █████ █ ██  ███    █ █  █  █
 *     █      █ █   █ █   █ █  ██ █   █ █   █ █   █ █      ██   █  █
 *    ███  ███  ████  █   █ █   █ ████  █   █  ███   ███ █ █ █  ███
 *     
**/

namespace Richen\Perms\Commands;

use Richen\Perms\PermsMain;

use pocketmine\command\{Command,CommandSender};

use pocketmine\Player;

class SetGroup extends Command
{
    public function __construct(PermsMain $plugin, $name, $description, $perm){
        $this->plugin = $plugin;
		$this->perm = $perm;
        parent::__construct($name, $description, null, array("sg"));
    }

	public function execute(CommandSender $sender, $label, array $args){
		//if(!$sender->hasPermission($this->perm) && !$sender->isOp() && $sender Instanceof Player)
		if($sender Instanceof Player) return $sender->sendMessage("§6[§ePerms§6] §cНедостаточно прав для выполнения команды.");

		if(!isset($args[0]) or !isset($args[1]))
			return $sender->sendMessage("§6[§ePerms§6] §cИспользуйте: /setgroup <игрок> <группа>");
		
		if(!in_array(strtolower($args[1]), $this->plugin->groups))
			return $sender->sendMessage("§6[§ePerms§6] §cГруппа §6" . strtolower($args[1]) . " §cне существует");
		
		$player = $this->plugin->getPlayer($args[0]);
		
		if(strtolower($args[1]) == "guest"){
			$this->plugin->remPlayerInfo($player->getName());
		}else{
			$old = $this->plugin->getPlayerInfo($sender->getName());
			$this->plugin->setPlayerInfo($player->getName(), array("group" => strtolower($args[0]), "nick" => $old["nick"], "prefix" => $old["prefix"]));	
		}
		
		if($player->isOnline()){ 
			$this->plugin->updatePermissions($player);
			$nameTag = $this->plugin->getNameTag($player);
			$player->setDisplayName($nameTag);
			$player->setNameTag($nameTag . "\n§e* §fIsland§bAge§3.ru §e*");
		}
		
		$sender->sendMessage("§6[§ePerms§6] §eГруппа игрока §6" . $player->getName() . " §eуспешно обновлена на новую: §a" . $args[1]);
		
		if($player->getName() != $args[0]) $player->sendMessage("§6[§ePerms§6] §eВаша привилегия обновлена на новую: §6" . $args[1]);
		
		return true;
    }
}