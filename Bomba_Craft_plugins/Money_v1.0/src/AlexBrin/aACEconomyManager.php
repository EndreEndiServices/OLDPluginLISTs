<?php

	namespace Azik;

	class aACEconomyManager extends aACMain {

		public function __construct(aACMain $plugin) {
			$this->p = $plugin;
			$pManager = $plugin->getServer()->getPluginManager();
			$this->eco = $pManager->getPlugin("EconomyAPI") ?? $pManager->getPlugin("PocketMoney") ?? $pManager->getPlugin("MassiveEconomy") ?? null;
			unset($pManager);
			if($this->eco === null)
				$plugin->getLogger()->warning('Плагин на экономику отсутствует');
			else
				$plugin->getLogger()->info('§aНайден плагин на экономику: §d'.$this->eco->getName());
		}

		/**
		 * @param string $player
		 * @param integer $amount
		 */
		public function giveMoney($player, $amount) {
			if($this->eco === null)
				return "§cПлагин на экономику отсутствует!";
			$this->eco->setMoney($player, $this->getMoney($player) + $amount);
		}

		/**
		 * @param  string $player
		 * @return integer $balance
		 */
		public function getMoney($player) {
			switch($this->eco->getName()) {

				case 'EconomyAPI':
						$balance = $this->eco->myMoney($player);
					break;

				case 'PocketMoney':
						$balance = $this->eco->getMoney($player);
					break;

				case 'MassiveEconomy':
						$balance = $this->eco->getMoney($player);
					break;

				default:
					$balance = 0;

			}
			return $balance;
		}

		/**
		 * @var string $name
		 * @return mixed
		 */
		public function getEconomyPlugin($name = false) {
			if($name)
				return $this->eco->getName();
			return $this->eco;
		}

	}

?>