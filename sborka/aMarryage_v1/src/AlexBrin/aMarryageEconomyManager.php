<?php

	namespace AlexBrin;

	use AlexBrin\aMarryage;

	use pocketmine\Player;

	class aMarryageEconomyManager extends aMarryage {
		/**
		 * @var $e - EconomyAPI
		 * @var $pm - PocketMoney
		 * @var $me - MassiveEconomy
		 */
		private $e, $pm, $me;
		
		public function __construct(aMarryage $plugin) {
			// parent::__construct($plugin);
			$this->plugin = $plugin;
			$pManager = $this->plugin->getServer()->getPluginManager();
			$this->e = $pManager->getPlugin("EconomyAPI");
			$this->pm = $pManager->getPlugin("PocketMoney");
			$this->me = $pManager->getPlugin("MassiveEconomy");
			if($this->e)
				$this->plugin->getLogger()->info("§aНайден плагин §bEconomyAPI");
			if($this->pm)
				$this->plugin->getLogger()->info("§aНайден плагин §bPocketMoney");
			if($this->me)
				$this->plugin->getLogger()->info("§aНайден плагин §bMassiveEconomy");
		}

		/**
		 * @param String $player
		 * @return boolean
		 */
		public function createFamily($player, $amount) {
			if($this->e) {
				$balance = $this->e->setMoney($player, $this->getMoney($player) - $amount);
				return true;
			}
			elseif($this->pm) {
				$balance = $this->pm->setMoney($player, $this->getMoney($player) - $amount);
				return true;
			}
			elseif($this->me) {
				$balance = $this->me->setMoney($player, $this->getMoney($player) - $amount);
				return true;
			}
			return false;
		}

		/**
		 * @param String $player
		 * @return integer $balance
		 */
		public function getMoney($player) {
			if($this->e)
				$balance = $this->e->myMoney($player);
			elseif($this->pm)
				$balance = $this->pm->getMoney($player);
			elseif($this->me)
				$balance = $this->me->getMoney($player);
			else $balance = false;
			return $balance;
		}



	}

?>