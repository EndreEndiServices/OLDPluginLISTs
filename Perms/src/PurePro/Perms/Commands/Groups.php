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

class Groups extends Command
{
    public function __construct(PermsMain $plugin, $name, $description, $perm){
        $this->plugin = $plugin;
		$this->perm = $perm;
        parent::__construct($name, $description, null, array("group", "gp"));
    }

    public function execute(CommandSender $sender, $label, array $args){
		if(!$sender->hasPermission($this->perm) && !$sender->isOp())
			return $sender->sendMessage("§6[§ePerms§6] §cНедостаточно прав для выполнения команды.");
		
		$groups = $this->plugin->getGroups();
		$groups = str_replace(array("e","a"), array("е","а"), implode("§7, §3", $groups));
		$sender->sendMessage("§6[§ePerms§6] §bСписок групп на сервере§7: §3" . $groups . "§7.");

        return true;
    }
}