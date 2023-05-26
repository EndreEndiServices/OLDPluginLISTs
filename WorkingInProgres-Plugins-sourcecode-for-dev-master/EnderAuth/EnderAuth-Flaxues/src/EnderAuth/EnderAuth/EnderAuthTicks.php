<?php

namespace EnderAuth\EnderAuth;

use pocketmine\Server;
use pocketmine\scheduler\PluginTask;
/*
- Manages EnderAuth ticks. Many things are controlled here, but it's all optional!
*/
class EnderAuthTicks extends PluginTask{
    public function __construct(Loader $plugin){
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->disable = $this->owner->getConfig()->get("hotbar-disabled");
        $this->loginhotbar = $this->owner->getConfig()->get("hotbar-login");
        $this->registerhotbar = $this->owner->getConfig()->get("hotbar-register");
        $this->timeout = $this->owner->getConfig()->get("timeout");
    }
    public function onRun($currentTick){
    	# Hot bar messages.
        if($this->owner->hotbar){
            foreach($this->owner->getServer()->getOnlinePlayers() as $p){
                if($this->owner->safemode === true && $this->owner->status !== "enabled"){
                  $p->sendTip($this->disable);
                }
                elseif(isset($this->owner->loginmanager[$p->getId()]) && $this->owner->loginmanager[$p->getId()] === 1){
                   $p->sendTip($this->loginhotbar);
                }
                elseif(isset($this->owner->loginmanager[$p->getId()]) && $this->owner->loginmanager[$p->getId()] === 0){
                   $p->sendTip($this->registerhotbar);
                }
            }
        }
    	# Timeout.
    	if($this->owner->timeoutEnabled){
    		foreach($this->owner->getServer()->getOnlinePlayers() as $p){
    			if($this->owner->loginmanager[$p->getId()] !== true){
    				if(!isset($this->playerticks[$p->getId()])){
    					$this->owner->playerticks[$p->getId()] = 0;
    				}
    				$myticks = $this->owner->playerticks[$p->getId()];
    				$myticks++;
    				if($myticks * 20 > $this->owner->timeoutMax){
    					$p->kick($this->timeout);
    				}
    				$this->owner->playerticks[$p->getId()] = $myticks;
    			}
    		}
    	}
    	# Logger.
    	if(!isset($this->owner->mainlogger[$this->owner->loggercount])){
    		return;
    	}
        $message = $this->owner->mainlogger[$this->owner->loggercount];
    	$this->owner->loggercount++;
    	if($this->owner->status === "enabled"){
      		$prefix = "[EnderAuth]";
    	}
    	if($this->owner->status === "failed"){
      		$prefix = "[Failure]";
    	}
    	if($this->owner->status === null){
      		$prefix = "[PreloadError]";
    	}
    	$exception = "$prefix $message";
	    $this->owner->getServer()->getLogger()->info($exception);
	    if($this->owner->logger){
		  $file = $this->plugin->getDataFolder() . "EnderAuthlogs.log";
    	  	file_put_contents($file, $exception);
	    }
	    if($this->owner->loggercount > 1){ //Dump logger cache.
		  $this->owner->mainlogger = [];
		  $this->owner->loggercount = 0;
	    }  
	    $this->owner->lastlog = $exception;
    }
}
