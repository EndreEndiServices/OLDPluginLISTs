<?php
namespace LDX\TimeCommander;
use pocketmine\scheduler\Task;
use pocketmine\Server;
class TimeCommand extends Task {
  public function __construct($plugin,$cmd) {
    $this->plugin = $plugin;
    $this->cmd = $cmd;
    $this->start = false;
    parent:: __construct($plugin);
  }
  public function onRun(int $ticks) {
    if($this->start) {
      $this->plugin->runCommand($this->cmd);
    } else {
      $this->start = true;
    }
  }
}
?>
