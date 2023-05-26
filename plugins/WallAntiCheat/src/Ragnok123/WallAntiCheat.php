<?php
namespace Ragnok123;

use pocketmine\plugin\PluginBase;
use pocketmine\plugin\MethodEventExecutor;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use pocketmine\event\EventPriority;
use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;

use Ragnok123\task\ExecuteTask;

class WallAntiCheat extends PluginBase implements Listener
{
	private $state = [];
	private $damagers = [];

	/* STATE FUNCTIONS */
	public function playerName($Player)
	{
		if ($Player instanceof Player)
			return $Player->getName();
		return $Player;
	}

	public function getState($label, $Player, $default)
	{
		$n = $this->playerName($Player);
		if (!isset($this->state[$n]))
			return $default;
		if (!isset($this->state[$n][$label]))
			return $default;
		return $this->state[$n][$label];
	}

	public function setState($label, $Player, $val)
	{
		$n = $this->playerName($Player);
		if (!isset($this->state[$n]))
			$this->state[$n] = [];
		$this->state[$n][$label] = $val;
	}

	public function unsetState($label, $player)
	{
		$n = $this->playerName($player);
		if (!isset($this->state[$n]))
			return;
		if (!isset($this->state[$n][$label]))
			return;
		unset($this->state[$n][$label]);
	}

	public function getStates($label)
	{
		$states = [];

		foreach ($this->state as $player => $labels)
			if (isset($labels[$label]))
				$states[$player] = $labels[$label];

		return $states;
	}

	public function unsetStates($label)
	{
		foreach ($this->getStates($label) as $Player => $Value)
			$this->unsetState($label, $Player);
	}

	/* EVENTS FUNCTIONS */
	/**
	 * @priority <HIGHEST>
	 */
	public function onEntityDamage(EntityDamageEvent $event)
	{
		$Victim = $event->getEntity();
		if (($event instanceof EntityDamageByEntityEvent) && (!($event instanceof EntityDamageByChildEntityEvent))) {
			$Damager = $event->getDamager();

			if ($Damager instanceof Player) {
				if ($this->getState("damaged", $Damager, false)) {
					$event->setCancelled(true);
					return;
				};
				if (($Damager->getGamemode() !== Player::CREATIVE) && ($Damager->distance($Victim) > 4)) {
					$event->setCancelled(true);
					return;
				};
				$this->setState("damaged", $Damager, true);
				ExecuteTask::Execute($this, function () use ($Damager) {
					$this->setState("damaged", $Damager, false);
				}, 6);
			};
		};
	}

	/* MISC FUNCTIONS */


	/* OTHER FUNCTIONS */

	/* PLUGIN FUNCTIONS */
	public function onEnable()
	{
		//$this->saveDefaultConfig();
		//$this->reloadConfig();

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		//$this->getServer()->getPluginManager()->registerEvent("pocketmine\event\entity\EntityDamageEvent",$this,EventPriority::MONITOR,new MethodEventExecutor("onEntityDamage"),$this);
	}

	public function onDisable()
	{

	}
}