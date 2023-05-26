<?php

namespace mcg76\skywars\utils;

/**
 * MCG76 FileUtil
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 @author minecraftgenius76@gmail.com
 *        
 */
class FileUtil {
	
	/**
	 * Copy a file, or recursively copy a folder and its contents
	 * 
	 * @param string $source
	 *        	Source path
	 * @param string $dest
	 *        	Destination path
	 * @param string $permissions
	 *        	New folder creation permissions
	 * @return bool Returns true on success, false on failure
	 */
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
	public function recurse_copy($src, $dst) {
		$dir = opendir ( $src );
		@mkdir ( $dst );
		while ( false !== ($file = readdir ( $dir )) ) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir ( $src . '/' . $file )) {
					recurse_copy ( $src . '/' . $file, $dst . '/' . $file );
				} else {
					copy ( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}
		closedir ( $dir );
	}
}