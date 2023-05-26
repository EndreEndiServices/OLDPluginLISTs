<?php

namespace bankSigns\flayzer;

use pocketmine\Player;
use pocketmine\utils\Config;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;

class main extends PluginBase implements Listener {

    public $jail = [];

	function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->config = new Config($this->getDataFolder() .'config.yml', Config::YAML);
		$this->signs = new Config($this->getDataFolder() .'signs.yml', Config::YAML);
		
		$this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, 'timer')), 20);
	}
	
	function timer() {
        foreach($this->getServer()->getOnlinePlayers() as $player){
			if(isset($this->jail[$player->getName()])){
				$player->sendPopup(str_replace('{time}', $this->jail[$player->getName()] - time()), $this->config->get('timepopup')));
					
				if($this->jail[$player->getName()] <= time()){
                    $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
					$player->sendMessage($this->config->get('teleportspawn'));
					unset($this->jail[$player->getName()]);
		        }
			}
		}
	}
	
	function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		
		$x = $event->getBlock()->getX();
		$y = $event->getBlock()->getY();
		$z = $event->getBlock()->getZ();
		
		if($x == $this->signs->get('x_bankadd') && $y == $this->signs->get('y_bankadd') && $z == $this->signs->get('z_bankadd')){
			$potluck = $this->signs->get('potluck_bankadd');
            $time = $this->signs->get('time_bankadd');
            $count = $this->signs->get('count_bankadd');
			
            if(mt_rand(1, 100) <= $potluck){
				foreach($this->getServer()->getOnlinePlayers() as $p){
				    $p->sendMessage(str_replace(['{nick}', '{count}'], [$player->getName(), $count], $this->config->get('bank1broadcast')));
			    }
				
                $player->sendMessage(str_replace('{count}', $count, $this->config->get('bank1me')));
				$this->economy->addMoney($player, $count);
			} else {
				$player->teleport(new \pocketmine\math\Vector3($this->config->get('jailx'), $this->config->get('jaily'), $this->config->get('jailz')));
				
				foreach($this->getServer()->getOnlinePlayers() as $p){
				    $p->sendMessage(str_replace('{nick}', $player->getName(), $this->config->get('bank1broadcastno')));
				}
				
				$this->jail[$player->getName()] = time() + $time*60;
				
                $player->sendMessage($this->config->get('bank1meno'));	
			}
		}elseif($x == $this->signs->get('x_bankadd1') && $y == $this->signs->get('y_bankadd1') && $z == $this->signs->get('z_bankadd1')){
			$potluck = $this->signs->get('potluck_bankadd1');
            $time = $this->signs->get('time_bankadd1');
            $count = $this->signs->get('count_bankadd1');
			
            if(mt_rand(1, 100) <= $potluck){
				foreach($this->getServer()->getOnlinePlayers() as $p){
				    $p->sendMessage(str_replace(['{nick}', '{count}'], [$player->getName(), $count], $this->config->get('bank1broadcast')));
			    }
				
                $player->sendMessage(str_replace('{count}', $count, $this->config->get('bank1me')));
				$this->economy->addMoney($player, $count);
			} else {
				$player->teleport(new \pocketmine\math\Vector3($this->config->get('jailx'), $this->config->get('jaily'), $this->config->get('jailz')));
				
				foreach($this->getServer()->getOnlinePlayers() as $p){
				    $p->sendMessage(str_replace('{nick}', $player->getName(), $this->config->get('bank1broadcastno')));
				}
				
				$this->jail[$player->getName()] = time() + $time*60;
				
                $player->sendMessage($this->config->get('bank1meno'));
			}
		}elseif($x == $this->signs->get('x_bankadd2') && $y == $this->signs->get('y_bankadd2') && $z == $this->signs->get('z_bankadd2')){
			$potluck = $this->signs->get('potluck_bankadd2');
            $time = $this->signs->get('time_bankadd2');
            $count = $this->signs->get('count_bankadd2');
			
            if(mt_rand(1, 100) <= $potluck){
				foreach($this->getServer()->getOnlinePlayers() as $p){
				    $p->sendMessage(str_replace(['{nick}', '{count}'], [$player->getName(), $count], $this->config->get('bank1broadcast')));
			    }
				
                $player->sendMessage(str_replace('{count}', $count, $this->config->get('bank1me')));
				$this->economy->addMoney($player, $count);
			} else {
				$player->teleport(new \pocketmine\math\Vector3($this->config->get('jailx'), $this->config->get('jaily'), $this->config->get('jailz')));
				
				foreach($this->getServer()->getOnlinePlayers() as $p){
				    $p->sendMessage(str_replace('{nick}', $player->getName(), $this->config->get('bank1broadcastno')));
				}
				
				$this->jail[$player->getName()] = time() + $time*60;
				
                $player->sendMessage($this->config->get('bank1meno'));	
			}
        }
    }		
	
	function onSign(SignChangeEvent $event){
		$player = $event->getPlayer();
		
		$x = $event->getBlock()->getX();
        $y = $event->getBlock()->getY();
        $z = $event->getBlock()->getZ();
		
		if($event->getLines()[0] == 'bankadd' && $event->getLines()[1] != null && $event->getLines()[2] != null && $event->getLines()[3] != null){
			if($player->hasPermission('flayzer.use.bank.sign')){
				$potluck = $event->getLines()[1];
				$time = $event->getLines()[2];
				$count = $event->getLines()[3];
				
				$event->setLine(0, '§fПопробывать ограбление');
                $event->setLine(1, "§fШанс:§c $potluck");
                $event->setLine(2, "§fВремя:§c $time");
                $event->setLine(3, "§fДеньги:§b $count");
				
				$this->signs->set('x_bankadd', $x);
                $this->signs->set('y_bankadd', $y);
                $this->signs->set('z_bankadd', $z);
                $this->signs->set('potluck_bankadd', $potluck);
                $this->signs->set('time_bankadd', $time);
                $this->signs->set('count_bankadd', $count);
				$this->signs->save();
			}
		}elseif($event->getLines()[0] == 'bankadd1' && $event->getLines()[1] != null && $event->getLines()[2] != null && $event->getLines()[3] != null){
			if($player->hasPermission('flayzer.use.bank.sign')){
				$potluck = $event->getLines()[1];
				$time = $event->getLines()[2];
				$count = $event->getLines()[3];
				
				$event->setLine(0, '§fПопробывать ограбление');
                $event->setLine(1, "§fШанс:§c $potluck");
                $event->setLine(2, "§fВремя:§c $time");
                $event->setLine(3, "§fДеньги:§b $count");
				
				$this->signs->set('x_bankadd1', $x);
                $this->signs->set('y_bankadd1', $y);
                $this->signs->set('z_bankadd1', $z);
                $this->signs->set('potluck_bankadd1', $potluck);
                $this->signs->set('time_bankadd1', $time);
                $this->signs->set('count_bankadd1', $count);
				$this->signs->save();
			}
		}elseif($event->getLines()[0] == 'bankadd2' && $event->getLines()[1] != null && $event->getLines()[2] != null && $event->getLines()[3] != null){
			if($player->hasPermission('flayzer.use.bank.sign')){
				$potluck = $event->getLines()[1];
				$time = $event->getLines()[2];
				$count = $event->getLines()[3];
				
				$event->setLine(0, '§fПопробывать ограбление');
                $event->setLine(1, "§fШанс:§c $potluck");
                $event->setLine(2, "§fВремя:§c $time");
                $event->setLine(3, "§fДеньги:§b $count");
				
				$this->signs->set('x_bankadd2', $x);
                $this->signs->set('y_bankadd2', $y);
                $this->signs->set('z_bankadd2', $z);
                $this->signs->set('potluck_bankadd2', $potluck);
                $this->signs->set('time_bankadd2', $time);
                $this->signs->set('count_bankadd2', $count);
				$this->signs->save();
			}
		}
	}
}
?>