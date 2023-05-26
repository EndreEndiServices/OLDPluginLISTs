<?php

	namespace AlexBrin;

	use pocketmine\scheduler\PluginTask;

	class aKitsTimer extends PluginTask {

		public function __construct(aKits $plugin) {
			parent::__construct($plugin);
			$this->p = $plugin;
			$plugin->getLogger()->info('§dАвтосохранение данных 1 раз в 5 минут');
		}

		public function onRun($tick) {
			$this->p->save();
		}

	}

?>