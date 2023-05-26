<?php

namespace Richen;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\scheduler\CallbackTask;
use pocketmine\Player;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\utils\Config;

class FlyText extends PluginBase implements Listener{
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
		
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		
		if($this->config->getAll() == null){
			$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array(
				"text1" => array('X,Y,Z' => '128,64,128', 'head' => 'пример заголовка1', 'text' => "пример летающего текста1"),
				"text2" => array('X,Y,Z' => '128,64,128', 'head' => 'пример заголовка2', 'text' => "пример летающего текста2")));
		}
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "addParticles")), 20 * 10);
	}
	
	public function addParticles(){
		$config = $this->config->getAll();
		foreach($config as $first => $second)
		{
			$get = $this->config->getAll()[$first];
			$vec = explode(',', $get['X,Y,Z']);
			$vec = new Vector3($vec[0], $vec[1], $vec[2]);
			$text = $get['text']; $head = $get['head'];
			$particle = new FloatingTextParticle($vec, $text, $head);
			$this->getServer()->getDefaultLevel()->addParticle($particle);
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, 'respawn'], [$particle]), 20 * 10);
		}
	}
	
	public function respawn(FloatingTextParticle $particle){
		$particle->setInvisible();
		$this->getServer()->getDefaultLevel()->addParticle($particle);
	}
}