<?php

namespace NFlyText;


use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\utils\Config;

class NFlyText extends PluginBase implements Listener {

 

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		
        $this->getLogger()->info("§6Загрузка...");
        $this->getLogger()->info("§cПлагин от NoRFoLk");
        $this->getLogger()->info("§6Перевод и фикс недочетов - NoRFoLk");
        $this->getLogger()->info("Плагин скачан с группы - vk.com/plugs_pe"); 
        $this->getLogger()->info("§aЗагружено!");
		
		@mkdir($this->getDataFolder());
		$config = new Config($this->getDataFolder()."Text.yml", Config::YAML);
		
		if(empty($config->get("Texts"))){
			$config->set("Texts", array("Text1"));
			$config->save();
		}
		$allTexts = $config->get("Texts");
		foreach((array) $allTexts as $text){
			if(empty($config->get($text))){
				$config->set($text, array("world", array("0", "0", "0"), "Текст!"));
				$config->save();
			}
		}
		
		foreach($this->getServer()->getLevels() as $l){
			$this->getServer()->loadLevel($l->getName());
		}
		
		$this->UpdateAll();
    }
	public function UpdateAll(){
		$config = new Config($this->getDataFolder()."Text.yml", Config::YAML);
		$allTexts = $config->get("Texts");
		foreach((array) $allTexts as $text){
			if(!empty($config->get($text))){
				$all = $config->get($text);
				$world = $all[0];
				$coords = $all[1];
				$msg = $all[2];
				
				$msg = str_replace("&", "§", $msg);
				
				$pos = new Vector3($coords[0], $coords[1], $coords[2]);
				$level = $this->getServer()->getLevelByName($world);
				
				$level->addParticle(new FloatingTextParticle($pos->add(0.5, 0.0, 0.5),"",  $msg));
				
				
			} else {
				//$this->getLogger()->info("§cТекст -> §c".$text." §6<- Не загружен !");
			}
		}
	}
	public function onJoin(PlayerJoinEvent $event){
		$this->UpdateAll();
	}
	public function onLevelChange(EntityLevelChangeEvent $event){
		$this->UpdateAll();
	}
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
        $name = $sender->getName();
        $config = new Config($this->getDataFolder()."Text.yml", Config::YAML);
		
		if (strtolower($cmd->getName()) === "settext" && $sender->isOP()) {
            
			if(!empty($args[0]) && !empty($args[1])){
				
				if($sender instanceof Player){
					$textname = array_shift($args);
					$text = implode(" ", $args);
					
					$text = str_replace("§", "&", $text);
					
					$x = $sender->getX();
					$y = $sender->getY();
					$z = $sender->getZ();
					$world = $sender->getLevel()->getName();
					
					$config->set($textname, array($world, array($x, $y, $z), $text));
					
					$allTexts = $config->get("Texts");
					$allTexts[] = $textname;
					$config->set("Texts", $allTexts);
					$config->save();
					
					$text = str_replace("&", "§", $text);
					
					$pos = new Vector3($x, $y, $z);
					$level = $this->getServer()->getLevelByName($world);
					$level->addParticle(new FloatingTextParticle($pos->add(0.5, 0.0, 0.5),"", $text));
					
					$sender->sendMessage("§aСоздан новый текст. §1Название текста:§e ".$textname."");
				} else {
					$sender->sendMessage("§cВ консоли нельзя делать тексты §c!");
				}
				
			} else {
				$sender->sendMessage(" §c-> /bossl §e<Имя> §a<Текст>");
			}
			
        }
    }

}