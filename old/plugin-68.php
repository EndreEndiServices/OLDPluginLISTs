<?php
/*
__PocketMine Plugin__
name=message
description=Join and message
version=1.0.0
author=smk020(곱등연가)
class=message
apiversion=8,9,10
*/

class message implements Plugin{
    private $api;
    public function __construct(ServerAPI $api, $server = false){
      $this->api = $api;
}
    public function init(){
      $this->api->addHandler("player.spawn", array($this, "eventHandler"), 6);
      $this->api->console->register("set", "[Message] -Message setting", array($this, "command"));
      $this->path =$this->api->plugin->createConfig($this, array());
      $this->conf =$this->api->plugin->readYAML($this->path ."config.yml");
}
public function eventHandler($data, $event) {
switch($event) {
case "player.spawn": $player =$this->api->player->getByEID($data->eid);
 $user =$player->iusername;
 $config =$this->api->plugin->readYAML($this->path ."config.yml");
if(!array_key_exists($user, $config)) {
 $this->conf[$player->iusername]["message"] ="Hi";
$about = $this->conf[$player->iusername]["message"];
$this->write($this->conf);
$this->api->chat->broadcast("[Message] $about");
} else
{
$about = $this->conf[$player->iusername]["message"];
$this->api->chat->broadcast("[Message] $about");
}
break;
}
}
public function command($cmd, $args, $issuer, $alias) {
$output ="";
 $cmd =strtolower($cmd);
 switch ($cmd) {
case "set":
$this->conf[$issuer->username]["message"] = implode(" " ,$args);
$this->write($this->conf);
break;
}
}
private function write($dat){
 $this->api->plugin->readYAML($this->path."config.yml");
 $this->api->plugin->writeYAML($this->path."config.yml", $dat);
 }
    public function __destruct(){}
}