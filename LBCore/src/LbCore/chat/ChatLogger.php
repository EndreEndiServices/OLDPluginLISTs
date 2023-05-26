<?php

namespace LbCore\chat;

class ChatLogger {

	protected $logger;

	public function __construct($logger) {
		$this->logger = $logger;
	}

	public function log($message, $isBad = false, $reason = '', $playerName = '') {
		static $blockedBufferCounter = 19;
		static $chatBufferCounter = 19;
		static $blockedBuffer = array();
		static $chatBuffer = array();

		if ($isBad) {
			$suffix = 'blocked';
			$lineTag = $playerName . ' ' . $reason . ': ';
			$writeLine = $lineTag . $message . "\r\n";
			$blockedBuffer[] = $writeLine; // This pushes to end of array.
			if (++$blockedBufferCounter >= 20) {
				$this->writeBufferToLogFile($suffix, $blockedBuffer);
				$blockedBufferCounter = 0;
				$blockedBuffer = array();	   // Reinstantiating array clears the buffer
				return true;
			}
		} else {
			$suffix = 'chat';
			$lineTag = $playerName . ': ';
			$writeLine = $lineTag . $message . "\r\n";
			$chatBuffer[] = $writeLine;
			if (++$chatBufferCounter >= 20) {
				$this->writeBufferToLogFile($suffix, $chatBuffer);
				$chatBufferCounter = 0;
				$chatBuffer = array();
				return true;
			}
		}
		return false;
	}

	/**
	 * Open a file with today's date as part of the name, for appending.
	 * Will call the program that deletes old files every time it needs to create a new file.
	 *
	 * @param $suffix
	 * @return null|resource
	 */
	protected function openDatedLogFile($suffix) {
		$logDir = 'logs/';
		// If the logging directory does not exist create it.
		if (!is_dir($logDir)) {
			mkdir($logDir, 0777, true);
		}
		$logFilename = $logDir . date("Y.m.d") . '_' . $suffix . '.txt';

		if (!file_exists($logFilename)) {
			$this->logger->info('Creating New Log File: ' . $logFilename);
			$this->deleteOldFiles();
		}
		$logFileHandle = fopen($logFilename, 'a');
		return $logFileHandle;
	}

	protected function deleteOldFiles() {
		$logDir = 'logs/';
		
		if (!is_dir($logDir)) {
			$this->logger->error("deleteOldFiles Specified path is not a directory:" . "{$logDir}\n");
			return;
		}
		
		$dirHandler = opendir($logDir);
		if ($dirHandler === false) {
			$this->logger->error("deleteOldFiles could not open directory:" . "{$logDir}\n");
			return;
		}

		while ($file = readdir($dirHandler)) {
			$filePath = $logDir . $file;
			if ($this->isFileTooOld($filePath)) {
				unlink($filePath);  // This deletes the file.
			}
		}
		closedir($dirHandler);
	}

	private function writeBufferToLogFile($logType, $buffer) {
		$logFileHandle = $this->openDatedLogFile($logType);
		if ($logFileHandle === false) {
			return false;
		}
		foreach ($buffer as $line) {
			fwrite($logFileHandle, $line);
		}
		fclose($logFileHandle);
	}
	
	private function isFileTooOld($path) {
		$howOld = '-10 days';
		return !is_dir($path) && filemtime($path) < strtotime($howOld);
	}

}
