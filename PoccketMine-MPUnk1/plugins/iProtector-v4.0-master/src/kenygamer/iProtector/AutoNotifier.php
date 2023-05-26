<?php

namespace kenygamer\iProtector;

use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Internet;

class AutoNotifier{
  
  private $plugin;
  /**
   * @var string $name Plugin name
   * @var string $version Plugin version
   */
  private $name, $version;
  /** @var array */
  private $releases;
  
  public function __construct($plugin){
    $this->plugin = $plugin;
    $this->name = $plugin::PLUGIN_NAME;
    $this->version = $plugin::PLUGIN_VERSION;
    $releases = Internet::getURL("https://raw.githubusercontent.com/kenygamer/pmmp-plugins/master/".$plugin::PLUGIN_NAME."/releases.json");
    if($releases === false){
      $plugin->getLogger()->error("[AutoNotifier] Host raw.githubusercontent.com timed out");
      return;
    }
    $this->releases = json_decode($releases, true);
    if(json_last_error() !== JSON_ERROR_NONE){
      $plugin->getLogger()->error("[AutoNotifier] Host raw.githubusercontent.com returned an invalid response");
      return;
    }
    $this->check();
  }
  
  /**
   * Checks version status
   *
   * @return void
   */
  private function check(){
    $this->plugin->getLogger()->info("[AutoNotifier] You are running $this->name v$this->version");
    if(!$this->isOutdated()){
      $this->plugin->getLogger()->info("[AutoNotifier] You are up-to-date");
      return;
    }
    $last = end($this->releases);
    $this->plugin->getLogger()->warning("[AutoNotifier] A new version (v".$last["version"].") has been released. Consider upgrading $this->name");
    $this->plugin->getLogger()->info("--- Changelog ---");
    foreach($last["features"] as $feature){
      $this->plugin->getLogger()->info("* ".$feature);
    }
  }
  
  /**
   * Checks if current version is outdated
   *
   * @return bool
   */
  private function isOutdated() : bool{
    return version_compare($this->version, end($this->releases)["version"]) === -1;
  }
  
}
