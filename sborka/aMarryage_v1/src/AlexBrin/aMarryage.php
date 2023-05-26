<?php

	namespace AlexBrin;

	use pocketmine\plugin\PluginBase;
	use pocketmine\utils\Config;
	use pocketmine\command\Command;
	use pocketmine\command\CommandSender;
	use pocketmine\event\Listener;
	use pocketmine\event\entity\EntityDamageEvent;
	use pocketmine\event\entity\EntityDamageEventByEntity;
	use pocketmine\Player;
	use pocketmine\Server;

	use AlexBrin\aMarryageCommand;
	use AlexBrin\aMarryageEconomyManager;
	use AlexBrin\aMarryageListener;

	class aMarryage extends PluginBase implements Listener {
		/**
		 * @var array Configuration
		 */
		public $config,
					 /**
					  *  @var family
					  */
					 $families;
		/**
		 * @var Economy Plugin
		 */
		private	$economy,
						$cmd;

		public function onEnable() {
			$folder = $this->getDataFolder();
			if(!is_dir($folder))
				@mkdir($folder);
			$this->saveResource("config.yml");
			$this->config = (new Config($folder."config.yml", Config::YAML))->getAll();
			$this->families = (new Config($folder."families.yml", Config::YAML))->getAll();
			unset($folder);
			$this->cmd = new aMarryageCommand($this);
			$this->eco = new aMarryageEconomyManager($this);
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			$this->getLogger()->info("§aПлагин загружен");
		}

		public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
			$this->cmd->onCommand($sender, $command, $label, $args);
		}

		public function onEntityDamage(EntityDamageEvent $event) {
			if($event instanceof EntityDamageEventByEntity)
				if($event->getEntity() instanceof Player && $event->getDamager() instanceof Player)
					$this->listener->onEntityDamage($event);
		}

		/**
		 * @var String $player
		 * @return bool
		 */
		public function isMarried($player) {
			foreach($this->families as $family)
				if($player == $family["first"] || $player == $family["second"])
					return true;
			return false;
		}


		/**
		 * @return string
		 */
		public function getPrefix($player) {
			if($this->isMarried($player))
				return $this->config["prefixPP"];
			else return '';
		}

		public function save() {
			$cfg = new Config($this->getDataFolder()."families.yml", Config::YAML);
			$cfg->setAll($this->families);
			$cfg->save();
			unset($cfg);
		}

	}

?>