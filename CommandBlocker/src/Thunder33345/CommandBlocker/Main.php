<?php
/**
 * Created By Thunder33345
 **/
namespace Thunder33345\CommandBlocker;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{
	public
		$blockConsole = true,
		$blockRemote = true;
	protected
		$whiteListedPlayer = [],
		$blockedCommands = [],
		$logFile = null;

	public function onEnable()
	{
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->blockConsole = $this->getConfig()->get("block-console");
		$this->blockRemote = $this->getConfig()->get("block-remote");
		$this->blockedCommands = explode(",", $this->getConfig()->get("blocked-command"));
		$this->whiteListedPlayer = explode(",", $this->getConfig()->get("whitelisted-player"));
		$loggerEnabled = $this->getConfig()->get("log-to-file");
		if ($loggerEnabled === true) {
			if (!file_exists($this->getDataFolder() . "logs/")) {
				mkdir($this->getDataFolder() . "logs/", 0777, true);
			}
			$logfilename = $this->getDataFolder() . "logs/Logs_" . date("Y-m-d") . ".log";
			$this->logFile = fopen($logfilename, "a");
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function onDisable()
	{
		if ($this->logFile !== null) {
			fclose($this->logFile);
		}
	}

	public function isBlocked($cmd)
	{
		if (isset($this->blockedCommands)) {
			$cmd = strtolower($cmd);
			foreach ($this->blockedCommands as $bcmd) {
				$bcmd = strtolower($bcmd);
				if ($cmd === $bcmd) {
					return true;
				}
			}
		}
		return false;
	}

	public function isWhiteListed(Player $p)
	{
		if (isset($this->whiteListedPlayer)) {
			$p = $p->getName();
			$p = strtolower($p);
			foreach ($this->whiteListedPlayer as $wlp) {
				$wlp = strtolower($wlp);
				if ($p === $wlp) {
					return true;
				}
			}
		}
		return false;
	}

	public function logFile(Player $player, $state, $massage)
	{
		if ($this->logFile !== null) {
			$time = gmdate('Y-m-d h:i:s \G\M\T');
			$msg = "$time [$state] " . $player->getDisplayName() . '(' . $player->getAddress() . ') : ' . $massage . PHP_EOL;
			fwrite($this->logFile, "$msg");
		}
	}

	public function logCFile($exe, $state, $massage)
	{
		if ($this->logFile !== null) {
			$time = gmdate('Y-m-d h:i:s \G\M\T');
			$msg = "$time [$state] " . $exe . ' : ' . $massage . PHP_EOL;
			fwrite($this->logFile, "$msg");
		}
	}
}