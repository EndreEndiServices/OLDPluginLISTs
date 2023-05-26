<?php

namespace survivalgames\task;

use pocketmine\scheduler\AsyncTask;

class MapCopyTask extends AsyncTask {
	private $source;
	private $dest;
	
	public function __construct($source, $dest) {
		$dir = "./worlds/";
		$this->source = $dir . $source;
		$this->dest = $dir . $source;
	}
	
	public function onRun() {
		$this->setResult($this->copyDirectory($this->source, $this->dest));	
	}
    
	private function copyDirectory($s, $d) {
		if(!is_dir($s)) {
			return false;
		}
		if(!is_dir($d)) {
			@mkdir($d);
		}
		
		$files = array_diff(scandir($s), [".", ".." ]); // Remove those pesky dots.
		foreach($files as $f) {
			if(is_dir($f)) {
				if(!$this->copyDirectory($s . "/" . $f, $d . "/" . $f)) {
					return false;
				}
			}else{
				if(!@copy($s . "/" . $f, $d . "/" . $f)) {
					return false;
				}
			}
		}
		return true;
	}
	
	public function getSource() {
		return $this->source;
	}
	
	public function getDestination() {
		return $this->destination;	
	}
	
}
