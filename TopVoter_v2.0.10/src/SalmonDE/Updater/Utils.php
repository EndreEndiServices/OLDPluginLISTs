<?php

namespace SalmonDE\Updater;

class Utils {

	public static function getDataFromService(string $pluginName): array{
		try{
			$raw = \pocketmine\utils\Utils::getURL(UpdateManager::SERVICE . '?plugin=' . $pluginName);
		}catch(\Exception $e){
			$raw = null;
		}
		$data = json_decode($raw, true);
		if(!is_array($data)){
			$data = [
				'error' => $e !== null ? $e->getMessage() : 'Failed gathering data',
			];

			return $data;
		}elseif($data['status'] === UpdateManager::FAILED){
			$data = [
				'error' => 'Service is online but failed to process the request: ' . $data['error'],
			];
		}

		return $data;
	}

	public static function deleteFile(string $path, array $fallback = null){
		@unlink($path);
		if(file_exists($path) && $fallback !== null){ //Hacky but something like a last desperated attempt to delete the file
			file_put_contents($fallback['path'] . 'delete' . $fallback['version'], $path);
		}
	}

	public static function saveFile(string $path, string $data){
		$file = fopen($path, 'x');
		fwrite($file, $data);
		fclose($file);
	}

	public static function checkFile(string $path, string $md5Hash, &$valid){
		$valid = true;
		if(!is_file($path)){
			$valid = false;
		}
		if(md5(file_get_contents($path)) !== $md5Hash){
			$valid = false;
		}
		if(filesize($path) < 1000){
			$valid = false;
		}
	}
}
