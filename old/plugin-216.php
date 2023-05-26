<?php

/*
__PocketMine Plugin__
name=PluginLoader
version=1.1
author=onebone
apiversion=12,13
class=PluginLoader
*/

class PluginLoader implements Plugin{
	private $api, $plugins;
	
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->api->console->register("load", "<plugin name>", array($this, "commandHandler"),15);
	}								
	
	public function __destruct(){}
	
	public function commandHandler($cmd, $params){
		$output = "[PluginLoader] ";
		switch($cmd){
			case "load":
			if(count($params) == 0){
				console("[PluginLoader] /load <plugin name>");
			}else{
				$plugin = implode(" ",$params);
				if(file_exists(DATA_PATH."/plugins/$plugin.php")){
					$this->api->plugin->load(DATA_PATH."plugins/".$plugin.".php");
					$info = $this->getPHPPluginInfo(DATA_PATH."plugins/$plugin.php");
					$id = $this->api->plugin->getIdentifier($info["name"], $info["author"]);
				}elseif(file_exists(DATA_PATH."/plugins/$plugin.pmf")){
				//	$this->load(DATA_PATH."/plugins/".$plugin.".pmf");
					$this->api->plugin->load(DATA_PATH."plugins/$plugin.pmf"); // lol I can access to other plugin
					$info = $this->getPMFPluginInfo(DATA_PATH."plugins/$plugin.pmf");
					$id = $this->api->plugin->getIdentifier($info["name"], $info["author"]);
				}else{
					return "[PluginLoader] Plugin $plugin doesn't exists!!";
				}
				$p = $this->api->plugin->get($id);
				$p[0]->init();
				$output .= "Successfully loaded plugin.";
			}
		}
		return $output;
	}
	
	private function getPMFPluginInfo($file){
		$pmf = new PMFPlugin($file);
		return $pmf->getPluginInfo();
	}
	
	private function getPHPPluginInfo($file){
		$content = file_get_contents($file);
		$info = strstr($content, "*/", true);
		$content = str_repeat(PHP_EOL, substr_count($info, "\n")).substr(strstr($content, "*/"),2);
		if(preg_match_all('#([a-zA-Z0-9\-_]*)=([^\r\n]*)#u', $info, $matches) == 0){ //false or 0 matches
			console("[ERROR] Failed parsing of ".basename($file));
			return false;
		}
		$info = array();
		foreach($matches[1] as $k => $i){
			$v = $matches[2][$k];
			switch(strtolower($v)){
				case "on":
				case "true":
				case "yes":
					$v = true;
					break;
				case "off":
				case "false":
				case "no":
					$v = false;
					break;
			}
			$info[$i] = $v;
		}
		$info["code"] = $content;
		$info["class"] = trim(strtolower($info["class"]));
		return $info;
	}
}