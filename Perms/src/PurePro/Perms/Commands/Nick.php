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

class Nick extends Command
{
    public function __construct(PermsMain $plugin, $name, $description, $perm){
        $this->plugin = $plugin;
		$this->perm = $perm;
        parent::__construct($name, $description, null, array("setnick", "name"));
    }
	
	public function execute(CommandSender $sender, $label, array $args){
		if(!$sender->hasPermission($this->perm) && !$sender->isOp())
			return $sender->sendMessage("§6[§ePerms§6] §cНедостаточно прав для выполнения команды.");

		if(!isset($args[0]))
			return $sender->sendMessage("§6[§ePerms§6] §cИспользуйте: /nick <новый_префикс>");
		
		if(isset($args[1]))
			return $sender->sendMessage("§6[§ePerms§6] §cПрефикс не должен содержать пробелов!");
		
		if(!preg_match("#^[aA-zZ0-9\§_]+$#", $args[0]))
			return $sender->sendMessage("§6[§ePerms§6] §cНик должен быть только из цифр, букв и знаков цвета!");
		
		if(strlen($args[0]) > 20 or strlen($args[0]) < 3)
			return $sender->sendMessage("§6[§ePerms§6] §cНик не должен превышать 20 и быть меньше 3 символов.");
		
		switch(strtolower($args[0])){
			case "remove":
			case "rem":
			case "delete":
			case "del":
				$nick = null;
			break;
			default:
				$nick = $args[0];
			break;
		}
		
		$info = $this->plugin->getPlayerInfo($sender->getName());
		$this->plugin->setPlayerInfo($sender->getName(), array("group" => $info["group"], "nick" => $nick, "prefix" => $info["prefix"]));
		
		$this->plugin->updatePermissions($sender);
		$nameTag = $this->plugin->getNameTag($sender);
		$sender->setDisplayName($nameTag);
		$sender->setNameTag($nameTag . "\n§e* §fIsland§bAge§3.ru §e*");
		
		$sender->sendMessage("§6[§ePerms§6] §eНик успешно обновлен на " . $this->plugin->getPlayerInfo($sender->getName())["nick"]);
		
		return true;
    }
}