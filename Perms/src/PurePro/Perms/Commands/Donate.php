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

class Donate extends Command
{
    public function __construct(PermsMain $plugin, $name, $description, $perm){
        $this->plugin = $plugin;
		$this->perm = $perm;
        parent::__construct($name, $description);
    }

    public function execute(CommandSender $sender, $label, array $args){
        if(!$sender->hasPermission($this->perm) && !$sender->isOp())
			return $sender->sendMessage("§cУ Вас недостаточно прав для выполнения команды");
		
		if(isset($args[0])){
			switch(strtolower($args[0])){
				//case "guest":
				case "vip":
				$message = 
					"§6[§ePerms§6] §fИнформация о привилегии §eVIP§f:\n" .
					"§c• §fКрасивый префикс: §8[§3VIP§8] §7{$sender->getName()}\n" .
					"§c• §a/heal §f- вылечить себя;\n" .
					"§c• §a/kit vip §f- набор VIP;\n" .
					"§c• §a/repair §f- починка предмета в руке;\n" .
					"§c• §a/clear §f- очистить инвентарь;\n" .
					"§c• §fВход на полный сервер. Возможности Игрока.";
				$sender->sendMessage($message);
				return;
				
				case "hero":
				$message = 
					"§6[§ePerms§6] §fИнформация о привилегии §eHERO§f:\n" .
					"§c• §fКрасивый префикс: §8[§6HERO§8] §7{$sender->getName()}\n" .
					"§c• §a/fly §f- полет в выживании;\n" .
					"§c• §a/kit hero §f- набор HERO;\n" .
					"§c• §a/skin §f- смена размера скина;\n" . 
					"§c• §fВсе возможности: §7Игрок§f, §3VIP§f.";
				$sender->sendMessage($message);
				return;
				
				case "elite":
				case "elita":
				$message = 
					"§6[§ePerms§6] §fИнформация о привилегии §eELITE§f:\n" .
					"§c• §fКрасивый префикс: §8[§eELITE§8] §6{$sender->getName()}\n" .
					"§c• §a/god §f- режим бессмертия;\n" .
					"§c• §a/speed §f- смена скорости;\n" .
					"§c• §a/kit elite §f- набор ELITE;\n" . 
					"§c• §fВсе возможности: §7Игрок§f, §3VIP§f, §6HERO§f.";
				$sender->sendMessage($message);
				
				case "creative":
				case "creat":
				case "gm":
				$message = 
					"§6[§ePerms§6] §fИнформация о привилегии §eCreative§f:\n" .
					"§c• §fКрасивый префикс: §8[§eCreative§8] §e{$sender->getName()}\n" .
					"§c• §a/gm §f- смена режима игры;\n" .
					"§c• §a/top §f- телепорт в самый верх;\n" .
					"§c• §a/wild §f- телепорт в рандомную точку;\n" .
					"§c• §a+ §fОповещение о входе на сервере;\n" .
					"§c• §fВсе возможности: §7Игрок§f, §3VIP§f, §6HERO§f, §eELITE§f.";
				$sender->sendMessage($message);
				return;
				
				case "help":
				case "helper":
				$message = 
					"§6[§ePerms§6] §fИнформация о привилегии §eHelper§f:\n" .
					"§c• §fКрасивый префикс: §8[§6Helper§8] §6{$sender->getName()}\n" .
					"§c• §a• /tppos §f- телепорт на координаты;\n" .
					"§c• §a• /kick <ник> §f- кикнуть игрока;\n" .
					"§c• §a+ §fОповещение о входе на сервере;\n" .
					"§c• §fВсе возможности: §7Игрок§f, §3VIP§f, §6HERO§f, §eELITE§f, §eCreative§f.";
				$sender->sendMessage($message);
				return;
				
				case "moder":
				case "moderator":
				$message = 
					"§6[§ePerms§6] §fИнформация о привилегии §eModer§f:\n" .
					"§c• §fКрасивый префикс: §8[§9Moder§8] §7{$sender->getName()}\n" .
					"§c• §a• /tp §f- телепорт к игроку;\n" .
					"§c• §a+ §fОповещение о входе на сервере;\n" .
					"§c• §fВсе возможности: §7Игрок§f, §3VIP§f, §6HERO§f, §eELITE§f, §eCreative§f.";
				$sender->sendMessage($message);
				return;
				
				case "admin":
				case "administrator":
				$message = 
					"§6[§ePerms§6] §fИнформация о привилегии §eModer§f:\n" .
					"§c• §fКрасивый префикс: §8[§9Moder§8] §7{$sender->getName()}\n" .
					"§c• §a• /tp §f- телепорт к игроку;\n" .
					"§c• §a+ §fОповещение о входе на сервере;\n" .
					"§c• §fВсе возможности: §7Игрок§f, §3VIP§f, §6HERO§f, §eELITE§f, §eCreative§f.";
				$sender->sendMessage($message);
				return;
				
				case "ag":
				case "antigrief":
				case "antigriefer":
				$message = 
					"§6[§ePerms§6] §fИнформация о привилегии §eModer§f:\n" .
					"§c• §fКрасивый префикс: §8[§9Moder§8] §7{$sender->getName()}\n" .
					"§c• §a• /tp §f- телепорт к игроку;\n" .
					"§c• §a+ §fОповещение о входе на сервере;\n" .
					"§c• §fВсе возможности: §7Игрок§f, §3VIP§f, §6HERO§f, §eELITE§f, §eCreative§f.";
				$sender->sendMessage($message);
				return;
			}
		}
		
		$groups = $this->plugin->getGroups();
		array_shift($groups);
		$groups = str_replace(array("e","a"), array("е","а"), implode("§7, §3", $groups));
		
		$sender->sendMessage("§6[§ePerms§6] §eИспользуйте§7: §c/donate <группа>\n§6[§ePerms§6] §eСписок групп§7: §3" . $groups . "§7.");
		
        return true;
    }
}