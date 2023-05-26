<?php

namespace BedWars\manager;

use pocketmine\Server;
use BedWars\arena\Arena;

class WorldManager{
    
    public $arena;
    
    public function __construct(Arena $arena){
        $this->arena = $arena;
    }
    
        public function addWorld($worldname) {
		$base = $worldname;
		$source = Server::getInstance ()->getDataPath () . "worlds/bedwars/" . $base . "/";
		$dest = Server::getInstance ()->getDataPath () . "worlds/" . $worldname."/";
		
		if ($this->xcopy ( $source, $dest )) {
			try {
				Server::getInstance ()->loadLevel ( $worldname );
			} catch ( \Exception $e ) {
			}
			Server::getInstance ()->loadLevel ( $worldname );
		}
	}

        public function deleteWorld($worldname) {
		$levelpath = Server::getInstance ()->getDataPath () . "worlds/" . $worldname ."/";
		$this->unlinkRecursive ( $levelpath, true );
	}
        
        function xcopy($source, $dest, $permissions = 0755) {
		// Check for symlinks
		if (is_link ( $source )) {
			return symlink ( readlink ( $source ), $dest );
		}
		
		// Simple copy for a file
		if (is_file ( $source )) {
			return copy ( $source, $dest );
		}
		
		// Make destination directory
		if (! is_dir ( $dest )) {
			mkdir ( $dest, $permissions );
		}
		
		// Loop through the folder
		$dir = dir ( $source );
		while ( false !== $entry = $dir->read () ) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			
			// Deep copy directories
			$this->xcopy ( "$source/$entry", "$dest/$entry", $permissions );
		}
		
		// Clean up
		$dir->close ();
		return true;
	}
	
	/**
	 * Recursively delete a directory
	 *
	 * @param string $dir
	 *        	Directory name
	 * @param boolean $deleteRootToo
	 *        	Delete specified top-level directory as well
	 */
	public function unlinkRecursive($dir, $deleteRootToo) {
		if (! $dh = @opendir ( $dir )) {
			return;
		}
		while ( false !== ($obj = readdir ( $dh )) ) {
			if ($obj == '.' || $obj == '..') {
				continue;
			}
			
			if (! @unlink ( $dir . '/' . $obj )) {
				$this->unlinkRecursive ( $dir . '/' . $obj, true );
			}
		}
		
		closedir ( $dh );
		
		if ($deleteRootToo) {
			@rmdir ( $dir );
		}
		
		return;
	}
}

