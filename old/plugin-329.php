<?php

/*
__PocketMine Plugin__
name=DynamicSigns
version=1.0
description=Changing signs
author=Glitchmaster_PE
class=Signs
apiversion=11
*/

class Signs implements Plugin{

    public function __construct(ServerAPI $api, $server = false){
        $this->api = $api;
    }
    
    public function init(){
        $this->api->addHandler("tile.update",array($this,"eventHandler"));
        $this->path = $this->api->plugin->configPath($this);
        $this->config = new Config($this->path . "config.yml",CONFIG_YAML,array("Announcement 1" => array("Line 1" => "New Plugin!","Line 2" => "Called DynamicSigns!","Line 3" => "Made by Glitchmaster_PE","Line 4" => "He suggests you change this"), "Announcement 2" => array("Line 1" => "Another Announcement!", "Line 2" => "Might want to", "Line 3" => "Change this!", "Line 4" => " "),"Announcement 3" => array("Line 1" => " ","Line 2" => " ","Line 3" => " ","Line 4" => " ","Announcement 4" => array("Line 1" => " ","Line 2" => " ","Line 3" => " ","Line 4" => " "))));
        $this->config = $this->api->plugin->readYAML($this->path . "config.yml");
        $this->api->schedule(5*20,array($this,"UpdateSign"),array(),true);
        if(strlen($this->config["Announcement 1"]["Line 1"]) > 15 || strlen($this->config["Announcement 1"]["Line 2"]) > 15 || strlen($this->config["Announcement 1"]["Line 3"]) > 15 || strlen($this->config["Announcement 1"]["Line 4"]) > 15 || strlen($this->config["Announcement 2"]["Line 1"]) > 15 || strlen($this->config["Announcement 2"]["Line 2"]) > 15 || strlen($this->config["Announcement 2"]["Line 3"]) > 15 || strlen($this->config["Announcement 2"]["Line 4"]) > 15 || strlen($this->config["Announcement 3"]["Line 1"]) > 15 || strlen($this->config["Announcement 3"]["Line 2"]) > 15 || strlen($this->config["Announcement 3"]["Line 3"]) > 15 || strlen($this->config["Announcement 3"]["Line 4"]) > 15 || strlen($this->config["Announcement 4"]["Line 1"]) > 15 || strlen($this->config["Announcement 4"]["Line 2"]) > 15 || strlen($this->config["Announcement 4"]["Line 3"]) > 15 || strlen($this->config["Announcement 4"]["Line 4"]) > 15){
            console(FORMAT_RED."[DynamicSigns] Make sure your lines have AT MOST 15 letters/numbers/spaces/etc.");
        }
    }
    
    public function __destruct(){
        
    }
    
    public function eventHandler($data){
        if($data->class == TILE_SIGN){
            if($data->data["Text1"] === "Announcements" || $data->data["Text1"] === $this->config["Announcement 1"]["Line 1"] || $data->data["Text1"] === $this->config["Announcement 2"]["Line 1"] || $data->data["Text1"] === $this->config["Announcement 3"]["Line 1"] || $data->data["Text1"] === $this->config["Announcement 4"]["Line 1"]){
                $this->sign = $data;
            }
        }
    }
    
    public function UpdateSign(){
        if(isset($this->sign)){
            $r = array_rand($this->config);
            $this->sign->setText($this->config[$r]["Line 1"],$this->config[$r]["Line 2"],$this->config[$r]["Line 3"],$this->config[$r]["Line 4"]);
        }
    }
}