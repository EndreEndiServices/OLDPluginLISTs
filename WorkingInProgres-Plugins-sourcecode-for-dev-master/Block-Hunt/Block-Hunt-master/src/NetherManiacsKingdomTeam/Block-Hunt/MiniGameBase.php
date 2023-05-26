<?php

namespace NetherManiacsKingdom\Block-Hunt;



abstract class MiniGameBase {		
	protected $plugin;
	public function __construct(BlockHuntPlugIn $plugin) {
		if($plugin === null){
			throw new \InvalidStateException("plugin may not be null");
		}
		$this->plugin = $plugin;
	}
	
	protected function getController() {
		return $this->getPlugin ()->controller;
	}
	protected function getPlugin() {
		return $this->plugin;
	}
	protected function getMsg($key) {
		return $this->plugin->messages->getMessageByKey ( $key );
	}
	protected function getSetup() {
		return $this->plugin->setup;
	}
	protected function getBuilder() {
		return $this->plugin->builder;
	}
	
	protected function getGameKit() {
		return $this->getPlugin()->gameKit;
	}
	
	protected function getProfileProvider() {
		return $this->plugin->profileprovider;		
	}
	
	protected function getLog() {
		return $this->plugin->getLogger();
	}
	
	protected function log($msg) {
		 $this->plugin->getLogger()->info($msg);
	}
	
	protected function getArenaManager() {
		return $this->getPlugin()->arenaManager;
	}
	protected function getConfig($key) {
		return $this->plugin->getConfig()->get($key);
	}
	
}