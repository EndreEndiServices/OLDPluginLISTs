<?php

namespace Logger;

class Analyzer {

	private $textLog = "";
	private $graph = array(
		'tps' => array(),
		'cpu' => array(),
		'players' => array()
	);
	private $date = "";
	private $serverName = "";

	public function __construct($textLog, $date, $serverName, $filePath) {
		$this->textLog = $textLog;
		$this->date = $date;
		$this->serverName = $serverName;
		$log = $this->getFullLog();
		@file_put_contents($filePath, json_encode($log));
	}

	private function getFullLog() {
		$log = array(
			'server_name' => $this->serverName,
			'date' => $this->date,
			'tps_cpu' => $this->getTpsCpuStatistic(),
			'restarts_count' => $this->getRestarts(),
			'abnormal_starts_count' => $this->getAbNormalStarts(),
			'enters_count' => $this->getEnters(),
			'average_session_length' => $this->getSessionLength(),
			'unique_players_count' => $this->getUniqPlayersCount(),
			'kills_count' => $this->getKills(),
			'exits_count' => array(
				'timeout' => $this->getTimeoutDisconnect(),
				'client_disconnect' => $this->getClientDisconnect(),
				'server_is_full' => $this->getFullServerDisconnect(),
				'kick' => $this->getKickDisconnect()
			),
			'bunches_of_exits_count' => $this->getDisconnectRow(),
			'graph_data' => $this->graph
		);
		return $log;
	}

	private function getTpsCpuStatistic() {
		$log = array();
		$end = 0;
		while (($start = strpos($this->textLog, "SERVER STATUS:", $end)) !== false) {
			$end = strpos($this->textLog, "]", $start);
			if ($end === false) {
				break;
			}
			$end++;
			$msg = substr($this->textLog, $start, ($end - $start));
			if (($tpsStart = strpos($msg, "TPS:")) === false) {
				continue;
			}
			$tpsStart += 5;
			if (($tpsEnd = strpos($msg, "PLAYERS:")) === false) {
				continue;
			}
			$playerStart = $tpsEnd + 9;
			$tpsEnd -= 1;
			$tps = substr($msg, $tpsStart, ($tpsEnd - $tpsStart));

			if (($playerEnd = strpos($msg, "MEMORY:")) === false) {
				continue;
			}
			$playerEnd -= 1;
			$playerCount = substr($msg, $playerStart, ($playerEnd - $playerStart));

			if (($cpuStart = strpos($msg, "CPU:")) === false) {
				continue;
			}
			$cpuStart += 5;
			if (($cpuEnd = strpos($msg, "% ]")) === false) {
				continue;
			}
			$cpu = substr($msg, $cpuStart, ($cpuEnd - $cpuStart));
			if ($cpu > 100) {
				continue;
			}

			$this->graph['tps'][] = $tps;
			$this->graph['cpu'][] = $cpu;
			$this->graph['players'][] = $playerCount;

			$logRow = array(
				"tps" => $tps,
				"players" => $playerCount,
				"cpu" => $cpu,
			);

			$intervals = array(
				array(
					'min' => 0,
					'max' => 40,
					'label' => '<40'
				),
				array(
					'min' => 40,
					'max' => 60,
					'label' => '40-60'
				),
				array(
					'min' => 60,
					'max' => 80,
					'label' => '60-80'
				),
				array(
					'min' => 80,
					'max' => 100,
					'label' => '80-100'
				),
				array(
					'min' => 100,
					'max' => 120,
					'label' => '100-120'
				),
				array(
					'min' => 120,
					'max' => 140,
					'label' => '120-140'
				),
				array(
					'min' => 140,
					'max' => 160,
					'label' => '140-160'
				)
			);

			foreach ($intervals as $interval) {
				if ($playerCount >= $interval['min'] && $playerCount < $interval['max']) {
					if (!isset($log[$interval['label']])) {
						$log[$interval['label']] = array();
					}
					$log[$interval['label']][] = $logRow;
					break;
				}
			}
		}

		$resultLog = array();
		foreach ($log as $label => $rows) {
			$cpu = 0;
			$tps = 0;
			$count = count($rows);
			if ($count > 0) {
				foreach ($rows as $row) {
					$cpu += $row['cpu'];
					$tps += $row['tps'];
				}
				$resultLog[$label] = array(
					'tps' => $tps / $count,
					'cpu' => $cpu / $count
				);
			}
		}

		return $resultLog;
	}

	private function getSessionLength() {
		$sessionLog = array();
		$end = 0;
		while (($start = strpos($this->textLog, "SESSION LENGTH:", $end)) !== false) {
			$start += 16;
			$end = strpos($this->textLog, "]", $start);
			if ($end === false) {
				break;
			}
			$msg = substr($this->textLog, $start, ($end - $start - 1));
			if (($dayEnd = strpos($msg, "D")) === false) {
				continue;
			}
			$day = substr($msg, 0, $dayEnd);

			if (($hoursEnd = strpos($msg, "H")) === false) {
				continue;
			}
			$hours = substr($msg, ($dayEnd + 2), ($hoursEnd - $dayEnd - 2));

			if (($minutesEnd = strpos($msg, "M")) === false) {
				continue;
			}
			$minutes = substr($msg, ($hoursEnd + 2), ($minutesEnd - $hoursEnd - 2));

			if (($secondsEnd = strpos($msg, "S")) === false) {
				continue;
			}
			$seconds = substr($msg, ($minutesEnd + 2), ($secondsEnd - $minutesEnd - 2));

			$sessonLength = $day * 86400 + $hours * 3600 + $minutes * 60 + $seconds;
			$sessionLog[] = $sessonLength;
		}

		$sum = 0;
		foreach ($sessionLog as $time) {
			$sum += $time;
		}
		$avg = 0;
		$count = count($sessionLog);
		if ($sum > 0 && $count > 0) {
			$avg = round($sum / $count);
		}

		return sprintf('%02dD %02dH %02dM %02dS', $avg / 86400, ($avg % 86400) / 3600, ($avg % 3600) / 60, ($avg % 3600) % 60);
	}

	private function getKills() {
		return substr_count($this->textLog, "was killed by");
	}

	private function getRestarts() {
		return substr_count($this->textLog, "-SERVER STOP-");
	}

	private function getAbNormalStarts() {
		$end = 0;
		$count = 0;
		while (($start = strpos($this->textLog, "-SERVER START-", $end)) !== false) {
			$stop = strpos($this->textLog, "-SERVER STOP-", $start);
			$nextStart = strpos($this->textLog, "-SERVER START-", $start + 15);
			if ($nextStart === false) {
				break;
			}
			if ($nextStart !== false && ($stop !== false && $nextStart < $stop || $stop === false)) {
				$count++;
			}
			if ($stop !== false) {
				$end = $stop;
				continue;
			}
			$end = $nextStart;		
		}
		return $count;
	}

	private function getEnters() {
		return substr_count($this->textLog, "LOG IN: ");
	}

	private function getUniqPlayersCount() {
		$end = 0;
		$players = array();
		while (($start = strpos($this->textLog, "LOG IN: ", $end)) !== false) {
			$start += 8;
			$end = strpos($this->textLog, " was joined", $start);
			if ($end === false) {
				break;
			}
			$name = substr($this->textLog, $start, ($end - $start));
			$players[$name] = true;
		}
		return count($players);
	}

	private function getTimeoutDisconnect() {
		return substr_count($this->textLog, "REASON: timeout");
	}

	private function getClientDisconnect() {
		return substr_count($this->textLog, "REASON: client disconnect");
	}

	private function getFullServerDisconnect() {
		return substr_count($this->textLog, "REASON: Server is Full");
	}

	private function getKickDisconnect() {
		return substr_count($this->textLog, "KICK: ");
	}

	private function getDisconnectRow() {
		return substr_count($this->textLog, "disconnects in a row");
	}

}
