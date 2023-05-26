<?php
/*
__PocketMine Plugin__
 name=Mentions
 description=A social tagging system
 version=0.1.2
 author=Falk
 class=mentionMe
 apiversion=10,11,12,13
 */
class mentionMe implements Plugin {
  private $api, $path;
  public function __construct(ServerAPI $api, $server = false) {
    $this->api = $api;
  }

  public function init() {
    $this->max = 5;
    $this->api->addHandler("player.chat", array($this, "eventHandle"), 50);
    $this->api->addHandler("player.spawn", array($this, "getMentions"), 50);
    $this->api->console->register("mentions", "Subscribe to mentions service", array($this, "command"));
    $this->api->ban->cmdWhitelist("mentions");
    $this->config = new Config($this->api->plugin->configPath($this)."mentions.yml", CONFIG_YAML, array());
    $this->mentions = $this->api->plugin->readYAML($this->api->plugin->configPath($this). "mentions.yml");
    console("[Mentions] Plugin enabled!");
  }

  public function __destruct() {
  }
  public function command($cmd, $params, $issuer, $alias, $args, $issuer) {
    if (!($issuer instanceof Player)) return "This is an in game command";
    if (array_key_exists($issuer->username, $this->mentions)) {
      $issuer->sendChat("[Mentions] Cleared mentions account!");
      $this->mentions[$issuer->username] = array();
    }
    else {
      $this->mentions[$issuer->username] = array();
      $issuer->sendChat("[Mentions] Success! Account created!");
    }
    $this->saveConfig();
  }

  public function eventHandle($data, $event) {
    if (strpos($data['message'], "@") !== false) {
      preg_match_all("#@([\\d\\w]+)#", $data['message'], $users);
      $users = array_unique($users[1]);
      foreach ($users as $user) {
        if (array_key_exists($user, $this->mentions)) {
          $this->mentions[$user][] = "<" . $data['player']->username . "> " . $data['message'];
          if (count($this->mentions[$user]) >= $this->max) array_shift($this->mentions[$user]);
        }
      }
      $this->saveConfig();
    }
  }
  public function getMentions($data, $event) {
    if (array_key_exists($data->username, $this->mentions) && count($this->mentions[$data->username]) >= 1) {
      $data->sendChat("--- Mentions Digest ---");
      foreach ($this->mentions[$data->username] as $key => $value) {
        $data->sendChat($key . ": " . $value);
      }
    }
    else $data->sendChat("[Mentions] Enable mentions with /mentions");
  }
  public function saveConfig() {
    $this->api->plugin->writeYAML($this->api->plugin->configPath($this)."mentions.yml", $this->mentions);
  }
}
