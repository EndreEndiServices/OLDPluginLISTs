<?php

/*
__PocketMine Plugin__
name=EssentialsProtect
description=EssentialsProtect
version=2.0
author=KsyMC
class=EssentialsProtect
apiversion=10
*/

class EssentialsProtect implements Plugin{
	private $api, $path, $config;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
		EssentialsAPI::setEssentialsProtect($this);
	}
	
	public function init(){
		$this->api->event("server.close", array($this, "handler"));
		$this->api->addHandler("player.block.break", array($this, "permissionsCheck"), 7);
		$this->api->addHandler("player.block.place", array($this, "permissionsCheck"), 7);
		$this->api->addHandler("player.block.touch", array($this, "permissionsCheck"), 7);
		$this->readConfig();
	}
	
	public function __destruct(){
	}
	
	public function readConfig(){
		$this->path = DATA_PATH."/plugins/Essentials/";
		$this->config = $this->api->plugin->readYAML($this->path."config.yml");
	}
	
	public function handler(&$data, $event){
		switch($event){
			case "server.close":
				break;
		}
	}
	
	public function permissionsCheck($data, $event){
		if(!$this->api->ban->isOp($data["player"]->username)){
			switch($event){
				case "player.block.touch":
					$items = BlockAPI::fromString($this->config["blacklist"]["usage"], true);
					$type = "item";
					break;
				case "player.block.break":
					$items = BlockAPI::fromString($this->config["blacklist"]["break"], true);
					$type = "target";
					break;
				case "player.block.place":
					$items = BlockAPI::fromString($this->config["blacklist"]["placement"], true);
					$type = "item";
					break;
			}
			foreach($items as $item){
				if($data[$type]->getID() === $item->getID() and $data[$type]->getMetadata() === $item->getMetadata()){
					return false;
				}
			}
		}
	}
}