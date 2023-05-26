<?php

namespace SarchCore;

use pocketmine\plugin\PluginBase;
use SarchCore\Bounties\BountyManager;
use SarchCore\CustomWeapons\CustomWeaponsManager;
use SarchCore\Cheat\CheatManager;
use SarchCore\Staff\StaffManager;
use SarchCore\Security\SecurityManager;
use SarchCore\Envoys\EnvoyManager;
use SarchCore\Tasks\MobClearTask;
use SarchCore\Commands\BountyCommand;
use SarchCore\Message\JoinMSG;
use SarchCore\Message\Bcast;

class SarchCore extends PluginBase {

	protected $bountymanager, $weaponsmanager, $staffmanager, $securitymanager, $message, $JoinMSG;

	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents(($this->bountymanager = new BountyManager($this)), $this);
		$this->getServer()->getPluginManager()->registerEvents(($this->staffmanager = new StaffManager($this)), $this);
		$this->getServer()->getPluginManager()->registerEvents(($this->cheatmanager = new CheatManager($this)), $this);
		$this->getServer()->getPluginManager()->registerEvents(($this->weaponsmanager = new CustomWeaponsManager($this)), $this);
		$this->getServer()->getPluginManager()->registerEvents(($this->securitymanager = new SecurityManager($this)), $this);
		$this->getServer()->getPluginManager()->registerEvents(($this->joinmsg = new JoinMSG($this)), $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new MobClearTask($this), 20 * (60 * 5));
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Bcast($this), 2000, 2000);
		$this->getServer()->getCommandMap()->register("bounty", new BountyCommand($this));
	}
	public function getBountyManager() {
		return $this->bountymanager;
	}
	public function getSecurityManager() {
		return $this->securitymanager;
	}
}
