<?php

	namespace AlexBrin;

	use pocketmine\plugin\PluginBase;
	use pocketmine\utils\Config;

	use pocketmine\command\Command;
	use pocketmine\command\CommandSender;

	use pocketmine\event\Listener;
	use pocketmine\event\player\PlayerPreLoginEvent;
	use pocketmine\event\player\PlayerCommandPreprocessEvent;

	use pocketmine\Player;
	use pocketmine\Server;

	class aKBI_Main extends PluginBase implements Listener {
		private $config,
						$ban;

		public function onEnable() {
			$f = $this->getDataFolder();
			if(!is_dir($f))
				@mkdir($f);
			$this->saveResource('config.yml');
			$this->config = (new Config($f.'config.yml', Config::YAML))->getAll();
			$this->ban    = (new Config($f.'ban.json', Config::JSON))->getAll();
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}

		public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
			if(strtolower($label) == 'kb') {
				$act = $args[0]; unset($args[0]);
				switch($act) {

					case 'kick':
							if(count($args) < 2) {
								$sender->sendMessage($this->config['usage']);
								return false;
							}
							if(!$sender->hasPermission('kbi.kick')) {
								$sender->sendMessage($this->config['permNotEx']);
								return false;
							}
							$player = $this->getServer()->getPlayer($args[1]);
							unset($args[1]);
							if($player instanceof Player) {
								$reason = '';
								foreach($args as $word)
									$reason .= $word.' ';
								$reason = trim($reason);
								if($reason == '')
									$reason = $this->config['defaultReason'];
								if($this->config['broadcast'] == true)
									$this->getServer()->broadcastMessage(str_replace(['{player}', '{target}', '{reason}'], [$sender->getName(), $player->getName(), $reason], $this->config['broadcastKickMessage']));
								$reason = str_replace(['{player}', '{reason}'], [$sender->getName(), $reason], $this->config['kick']);
								$player->close("", $reason);
							} else {
								$sender->sendMessage($this->config['playerOffline']);
							}
						break;

					case 'ban':
							if(count($args) < 1) {
								$sender->sendMessage($this->config['usage']);
								return false;
							}
							if(!$sender->hasPermission('kbi.ban')) {
								$sender->sendMessage($this->config['permNotEx']);
								return false;
							}
							$player = strtolower($args[1]); unset($args[1]);
							$reason = '';
							foreach($args as $word)
								$reason .= $word.' ';
							$reason = trim($reason);
							if($reason == '')
								$reason = $this->config['defaultReason'];
							if($this->config['broadcast'] == true)
									$this->getServer()->broadcastMessage(str_replace(['{player}', '{target}', '{reason}'], [$sender->getName(), $player, $reason], $this->config['broadcastBanMessage']));
							$reason = str_replace(['{player}', '{reason}'], [$sender->getName(), $reason], $this->config['ban']);
							$this->ban[$player] = [
								'player' => $sender->getName(),
								'reason' => $reason
							];
							$this->saveBan();
							$sender->sendMessage(str_replace('{player}', $player, $this->config['playerBan']));
							$player = $this->getServer()->getPlayer($player);
							if($player instanceof Player)
								$player->close('', $reason, true);
						break;

					case 'unban':
					case 'pardon':
							if(!$sender->hasPermission('kbi.unban')) {
								$sender->sendMessage($this->config['permNotEx']);
								return false;
							}
							if(count($args) < 1) {
								$sender->sendMessage($this->config['usage']);
								return false;
							}
							$player = strtolower($args[1]);
							if(isset($this->ban[$player])) {
								unset($this->ban[$player]);
								$this->saveBan();
							}
							$sender->sendMessage(str_replace('{player}', $player, $this->config['playerUnban']));
						break;

				}

			}
		}

		public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) {
			$message = explode(' ', $event->getMessage());
			if($message[0] != '/kb')
				return false;
			if($message[1] == 'ban') {
				$player = $this->getServer()->getPlayer($message[2]);
				if($player == null)
					$player = $this->getServer()->getOfflinePlayer($message[2]);
				if($player == null)
					return false;
				if($player->hasPermission('kbi.immunity')) {
					if($event->getPlayer()->hasPermission('kbi.admin'))
						return false;
					$event->getPlayer()->sendMessage(str_replace('{player}', $player->getName(), $this->config['immunity']));
					$event->setMessage(null);
				}
			}

		}

		public function onPlayerPrelogin(PlayerPreLoginEvent $event) {
			$player = $event->getPlayer();
			$name = strtolower($player->getName());
			if(isset($this->ban[$name])) 
				$player->close("", $this->ban[$name]['reason']);
		}

		private function saveBan() {
			$ban = new Config($this->getDataFolder().'ban.json', Config::JSON);
			$ban->setAll($this->ban);
			$ban->save();
			unset($ban);
		}

	}

?>