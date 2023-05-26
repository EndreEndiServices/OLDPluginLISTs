<?php

namespace survivalgames\arena;

class ArenaList {
	/** @var Arena[] */
    private $arenas = [];
	
    public function get($arena) {
    	if(is_int($arena)) {
    		$arena = (int) $arena;
    		return isset($this->arenas[$arena]) ? $this->arenas[$arena] : null;
    	}else if($arena instanceof Arena){
    		$arena = array_search($arena, $this->arenas);
    		return $arena === false ? null : $arena;
    	}
    }
    
    public function add(Arena $arena) {
    	if($arena === null || in_array($arena, $this->arenas)) {
    		return false;
    	}
    	$this->arenas[] = $arena;
    	return true;
    }
    
    public function remove($arena) {
    	if($arena instanceof Arena && $this->has($arena)) {
    		unset($this->arenas[$this->get($arena)]);
    		return true;
    	}else if(is_numeric($arena) && isset($this->arenas[$arena])) {
    		$arena = (int) $arena;
    		unset($this->arenas[$arena]);
    		return true;
    	}
    	return false;
    }
    
    public function has(Arena $arena) {
    	is_int($this->get($arena));	
    }
    
    public function getAll() {
    	return $this->arenas;	
    }
    
    public function tickAll() {
   		foreach($this->arenas as $arena) {
   			$arena->tick();
   		} 	
    }
    
}