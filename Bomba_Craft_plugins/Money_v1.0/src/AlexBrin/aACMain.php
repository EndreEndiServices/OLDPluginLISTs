<?php

	namespace AlexBrin;

	use pocketmine\plugin\PluginBase;
	use pocketmine\utils\Config;

	use pocketmine\command\Command;
	use pocketmine\command\CommandSender;

	use Azik\aACEconomyManager;

	use pocketmine\Player;
	use pocketmine\Server;

	class aACMain extends PluginBase {
		public $config, $pp, $users;

		public function onEnable() {
			$f = $this->getDataFolder();
			if(!is_dir($f))
				@mkdir($f);
			$this->saveResource('config.yml');
			$this->config = (new Config($f.'config.yml', Config::YAML))->getAll();
			$this->users = (new Config($f.'users.yml', Config::YAML))->getAll();
			$this->eco = new aACEconomyManager($this);
			$this->pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
			if($this->pp === null) {
				$this->getLogger()->warning("Плагин PurePerms не найден. Выключение плагина");
				$this->getServer()->getPluginManager()->disablePlugin($this);
			}
		}

		public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
			if($sender instanceof Player) {
				if(strtolower($command->getName()) == 'addcoins') {
					if(count($args) != 1) {
						$sender->sendMessage($this->config['usage']);
						return true;
					}
					$money = (int) $args[0];
					$name = strtolower($sender->getName());
					$group = strtolower($this->pp->getUserDataMgr()->getGroup($sender)->getName());
					$time = time();
					if(!isset($this->config['limit'][$group]['time']) || !isset($this->config['limit'][$group]['max'])) {
						$this->getLogger()->warning("Группа игрока $name ($group) отсутствует в конфиге или настроена не правильно");
						return true;
					}
					if(!isset($this->users[$name]))
						$this->users[$name] = 0;
					if($this->users[$name] > $time) {
						$sender->sendMessage(str_replace('{time}', $this->users[$name] - $time, $this->config['wait']));
						return true;
					}
					if($money > $this->config['limit'][$group]['max']) {
						$sender->sendMessage(str_replace('{count}', $this->config['limit'][$group]['max'], $this->config['over']));
						return true;
					}
					$this->users[$name] = $time + $this->config['limit'][$group]['time'] * 60;
					$this->eco->giveMoney($name, $money);
					$sender->sendMessage(str_replace('{count}', $money, $this->config['give']));
					$this->save();
				}
			} else $sender->sendMessage("§cOnly For Players!");
		}

		private function save() {
			$cfg = new Config($this->getDataFolder().'users.yml', Config::YAML);
			$cfg->setAll($this->users);
			$cfg->save();
			unset($cfg);
		}
		
	}


?>