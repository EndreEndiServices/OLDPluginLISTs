<?php

namespace ChatFilter\chat;

class ChatClasser {

	protected $isBad = false;
	protected $isProfane = false;
	protected $isDating = false;
	protected $isAdvertising = false;
	protected $isControversial = false;
	protected $isSpam = false;
	protected $textToCheck;
	protected $replacementText = "no replacement avail";
	protected $textPreProcessed = "could not pre-process the text";
	protected $textHarmlessGone = 'unable to remove harmless';
	protected $textToCheckNoWhiteSpace = "could not get rid of white space";
	protected $numberHarmlessRemoved = 0;
	protected $reason;
	protected $terseReason;
	protected $processingTimeMicroseconds;
	protected $ETStart;
	protected $ETEnd;
	protected $sepChars;
	protected $sepCharsOpt;
	protected $badWords;
	protected $harmlessWords;
	protected $datingWords;
	protected $advertisingWords;
	protected $controversialWords;

	public function __construct() {

		$this->sepChars = '(\'|\!|\@|\#|\$|\%|\^|\&|\*|\(|\)|\_|\+|\-|\=| ';   
		$this->sepChars .= '|\{|\}|\||\[|\]|\\\\|\:|\"|\;|\'|\<|\>|\?|\,|\.|\/|\"';  
		$this->sepChars .= '|\~|\`|\´|\d'; 
		$this->sepChars .= ')+'; 
		$this->sepCharsOpt = rtrim($this->sepChars,'+')."*";
		$this->badWords = new WordList(__DIR__ . "/dict/bad_english.csv");
		$this->badWords->addToList(__DIR__ . "/dict/bad_spanish.csv");
		$this->badWords->addToList(__DIR__ . "/dict/bad_german.csv");
		$this->harmlessWords = new WordList(__DIR__ . "/dict/harmless.csv", false); 
		$this->datingWords = new WordList(__DIR__ . "/dict/dating.csv");
		//$this->datingWords->dump();
		$this->advertisingWords = new WordList(__DIR__ . "/dict/advertising.csv");
		$this->controversialWords = new WordList(__DIR__ . "/dict/controversial.csv");
	}

	public function getIsBad() {
		return $this->isBad;
	}

	public function getIsProfane() {
		return $this->isProfane;
	}

	public function getIsDating() {
		return $this->isDating;
	}

	public function getIsAdvertising() {
		return $this->isAdvertising;
	}

	public function getIsControversial() {
		return $this->isControversial;
	}

	public function getIsSpam(){
		return $this->isSpam;
	}

	public function getTexToCheck() {
		return $this->textToCheck;
	}

	public function getTextPreprocessed() {
		return $this->textPreProcessed;
	}

	public function getTextHarmlessGone() {
		return $this->textHarmlessGone;
	}

	public function getNHarmless() {
		return $this->numberHarmlessRemoved;
	}

	public function getTextNoWhite() {
		return $this->textToCheckNoWhiteSpace;
	}

	public function getReason() {
		return $this->reason;
	}

	public function getTerseReason() {
		return $this->terseReason;
	}

	public function getProcessingMicroseconds() {
		return $this->processingTimeMicroseconds;
	}

	public function check($inString) {
		$this->resetToDefaultState();
		$ETStart = microtime();
		$this->textToCheck = $inString;
		$this->checkRaw($this->textToCheck);
		$this->textToCheck = strtolower($this->textToCheck);
		$this->textHarmlessGone = $this->harmlessWords->replaceFromList($this->textToCheck);
		$this->detectSpecialProfane($this->textHarmlessGone);
		$this->detectSpecialDating($this->textHarmlessGone);
		$this->detectSpecialAdvertising($this->textHarmlessGone);
		$this->textHarmlessGone = $this->unleet($this->textHarmlessGone);
		$this->textToCheckNoWhiteSpace = $this->removeAllWhiteSpace($this->textHarmlessGone);
		$this->checkBannedLists( $this->textToCheckNoWhiteSpace);
		$this->detectSpam($inString);
		$ETEnd = microtime();
		$this->processingTimeMicroseconds = 1000000 * ($ETEnd - $ETStart);
		return $this->isBad;
	}

	protected function checkBannedLists( $inString ){
		if ($this->badWords->checkLeet($inString)) {
			$this->isProfane = true;
			$this->isBad = true;
			$this->reason .= " PROFANE " . $this->badWords->reason;
			$this->terseReason .= 'P';
		}
		if ($this->datingWords->checkLeet($inString)) {
			$this->isDating = true;
			$this->isBad = true;
			$this->reason .= " DATING " . $this->datingWords->reason;
			$this->terseReason .= 'D';
		}
		if ($this->advertisingWords->checkLeet($inString)) {
			$this->isAdvertising = true;
			$this->isBad = true;
			$this->reason .= " ADV " . $this->advertisingWords->reason;
			$this->terseReason .= 'A';
		}
		if ($this->controversialWords->checkLeet($inString)) {
			$this->isControversial = true;
			$this->isBad = true;
			$this->reason .= " CONTROVERSY " . $this->controversialWords->reason;
			$this->terseReason .= 'C';
		}
	}

	protected function checkRaw($inString) {
		$inString = ' ' . $inString . ' ';		  // Add to the ends so things looking for a terminator after a word can work

		$patternList = array(// Reason this is hard to detect:
			// [[:punct:]] will look for any punctuation.  The double square brackets are needed.
			// And look also for double letters.
			// Note that the trailing 'i' makes this case insensitive
			// Each should be: description of what we are looking for => [ regex pattern, why ]
			'fu' => array("/".$this->sepChars."f".$this->sepCharsOpt."u/i", "Profane"),    // Note that you is whitelisted
			'fu' => array("/".$this->sepChars."ef+".$this->sepCharsOpt."u/i", "Profane"),    // Note that you is whitelisted
			'ef you' => array("/".$this->sepChars."ef+".$this->sepCharsOpt."you/i", "Profane"),
			' bch ' => array('/ bch /i', 'Profane'),
			's=x' => array('/s=x/i', 'Profane'), //
			'se*' => array('/se\*/i', 'Profane'),
			' s/x ' => array('/s[[:punct:]]x/i', 'Profane'),
			' b* ' => array('/ b(\*|=) /', 'Dating'),
			' gir/' => array('/ gir(\*|\\\|\/) /', 'Dating'),
			' g* ' => array('/ g(\*|=) /', 'Dating'),
			' ag.friend ' => array('/ a(b|g)[[:punct:]]friend/', 'Dating'),
			' ag.f ' => array('/ a(b|g)[[:punct:]]f /', 'Dating'),
			// '.com' => array('/[[:punct:]]+c+o+m+/i', 'Advertising'),
			// 'spaced .com' => array('/\s+[[:punct:]]+\s+c+o+m+/i', 'Advertising'), // As above but with spaces before and after the punctuation
			// '.net' => array('/[[:punct:]]+n+e+t+/i', 'Advertising'),
			// 'spaced .net' => array('/\s+[[:punct:]]+\s+n+e+t+/i', 'Advertising'),
			'inpvp' => array('/inpvp/i', 'Advertising'), // Look for this particular server directly.
			'8==D' => array('/8=+(D|>)/i', 'Profane'), // Penis sign
			'.|.' => array('/\.\|\./i', 'Profane'), // Penis sign
			'o|o' => array('/o\|o/i', 'Profane'), // Penis sign
			'(.)(.)' => array('/\(\.\)\s*\(\.\)/','Profane')  // boobs sign
		);

		foreach ($patternList as $badCombo => $patternAndReason) {
			if (preg_match($patternAndReason[0], $inString, $matches)) {
				if ($patternAndReason[1] == 'Advertising') {
					$this->reason .= " ADV checkRaw found: $badCombo. Matched: " . $matches[0];
					$this->terseReason .= 'A';
					$this->isAdvertising = true;
					$this->isBad = true;
				}
				if ($patternAndReason[1] == 'Profane') {
					$this->reason .= " PROFANE checkRaw found: $badCombo. Matched: " . $matches[0];
					$this->terseReason .= 'P';
					$this->isProfane = true;
					$this->isBad = true;
				}
				if ($patternAndReason[1] == 'Dating') {
					$this->reason .= " DATING checkRaw found: $badCombo. Matched: " . $matches[0];
					$this->terseReason .= 'D';
					$this->isDating = true;
					$this->isBad = true;
				}
			}
		}
	}

	///////////////////////////////////////  DETECT SPECIAL CASES ///////////////////////////////////////////
	/**
	 * Special cases are checked after removal of harmless.
	 * Looks for special cases that for one reason or another are hard to detect in the regular word list,
	 * or might cause false positives if included in a regular word list.
	 * This is also a line of defense if someone figures out a way through our regular filters, add to this.
	 * @param $inString
	 * @return bool
	 */
	protected function detectSpecialProfane($inString) {
		$inString = ' ' . $inString . ' ';		  // Add to the ends so things looking for a terminator after a word can work

		$patternList = array(// Reason this is hard to detect:
			'eff u' => '/ ef+ u/',
			'tit' => '/ tit+ /', // Word end in t, followed by it.  Also word "title"
			'tits' => '/ tit+s/',
			't i t' => '/ t i t /',
			't it' => '/ t it /',
			'ass' => '/ ass /',
			' sx' => '/ sx/',
			'a s s' => '/ a s s/'
		);

		// foreach ( $patternList as $key => $value ){echo( "$key => $value <br>"); }

		foreach ($patternList as $badCombo => $pattern) {
			if (preg_match($pattern, $inString, $matches)) {
				$this->reason = "Special Profane Found: $badCombo. Matched: " . $matches[0];
				$this->terseReason .= 'P';
				$this->isProfane = true;
				$this->isBad = true;
				return true;
			}
		}
		return false;
	}

	protected function detectSpecialDating($inString) {
		$inString = ' ' . $inString . ' ';		  // Add to the ends so things looking for a terminator after a word can work

		$patternList = array(// Reason this is hard to detect:
			' g|b f ' => "/".$this->sepChars."(g+|b+)".$this->sepCharsOpt."f/",
			' s*x' => "/".$this->sepChars."(s+)".$this->sepChars."x+/",
			' u r hot' => "/".$this->sepChars."u+".$this->sepChars."r+".$this->sepChars.'hot/',
			' u r so hot' => "/".$this->sepChars."u+".$this->sepChars."r+".$this->sepChars.'so'.$this->sepChars.'hot/'
		);

		foreach ($patternList as $badCombo => $pattern) {
			if (preg_match($pattern, $inString, $matches)) {
				$this->reason = "Special Dating Found: $badCombo. Matched: " . $matches[0];
				$this->terseReason .= 'D';
				$this->isDating = true;
				$this->isBad = true;
				return true;
			}
		}
		return false;
	}

	protected function detectSpecialAdvertising($inString) {
		$inString = ' ' . $inString . ' ';		  // Add to the ends so things looking for a terminator after a word can work
		$patternList = array(// Reason this is hard to detect:
			'dotnet' => '/\.net/'	 // dot net
		);
		foreach ($patternList as $badCombo => $pattern) {
			if (preg_match($pattern, $inString, $matches)) {
				$this->reason = "Special Advertising Found: $badCombo. Matched: " . $matches[0];
				$this->terseReason .= 'P';
				$this->isAdvertising = true;
				$this->isBad = true;
				return true;
			}
		}
		return false;
	}

	public function dump() {
		$this->badWords->dump();
	}

	///////////////////////////////////////////////////////  STRING PROCESSING  /////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Change letters with umlauts and other decorations to their normal letters.
	 * Use this only for things that unaruably may be interpreted as a particulat letter.
	 * For example, ö => 0.
	 * You still need to later leet check for things like 3 => e.
	 * @param $inString
	 * @return mixed
	 */
	function unleet($inString) {
		$outstring = $inString;
		$outstring = preg_replace('/Á|á|À|Â|à|Â|â|Ä|ä|Ã|ã|Å|å|α|Δ|Λ|λ/', 'a', $outstring);
		// $outstring = preg_replace('/ß|Β|β/', 'b', $outstring);
		$outstring = preg_replace( '/Ç|ç|¢|©/', 'c', $outstring);
		$outstring = preg_replace('/Þ|þ|Ð|ð/', 'd', $outstring);
		$outstring = preg_replace('/€|È|è|É|é|Ê|ê|∑|£|€/', 'e', $outstring);
		$outstring = preg_replace('/ƒ/', 'f', $outstring);
		// $outstring = preg_replace( '//', 'g', $outstring);
		// $outstring = preg_replace( '//', 'h', $outstring);
		$outstring = preg_replace( '/Ì|Í|Î|Ï|ì|í|î|ï/', 'i', $outstring);
		// $outstring = preg_replace( '//', 'j', $outstring);
		$outstring = preg_replace('/Κ|κ/', 'k', $outstring);
		$outstring = preg_replace('/£/', 'l', $outstring);
		//$outstring = preg_replace( '//', 'm', $outstring);
		$outstring = preg_replace('/η|ñ|Ν|Π/', 'n', $outstring);
		$outstring = preg_replace('/Ο|○|ο|Φ|¤|°|ø|ö|ó/', 'o', $outstring);
		$outstring = preg_replace('/ρ|Ρ|¶|þ/', 'p', $outstring);
		//$outstring = preg_replace( '//', 'q', $outstring);
		$outstring = preg_replace('/®/', 'r', $outstring);
		//$outstring = preg_replace('/\$/', 's', $outstring);  // Note that $ must be escapted
		$outstring = preg_replace('/Τ|τ/', 't', $outstring);
		$outstring = preg_replace('/υ|µ/', 'u', $outstring);
		$outstring = preg_replace('/ν/', 'v', $outstring);
		$outstring = preg_replace('/ω|ψ|Ψ/', 'w', $outstring);
		$outstring = preg_replace('/Χ|χ|×/', 'x', $outstring);
		$outstring = preg_replace('/¥|γ|ÿ|ý|Ÿ|Ý/', 'y', $outstring);
		// $outstring = preg_replace( '//', 'z', $outstring);
		return $outstring;
	}

	function removeAllWhiteSpace($inString) {
		$outString = preg_replace('/\s+/', "", $inString);
		// Condense multiple = to singles, also for speed of processing.
		$outString = preg_replace('/=+/', '=', $outString);
		return $outString;
	}

	private function resetToDefaultState() {
		$this->isProfane = false;
		$this->isDating = false;
		$this->isAdvertising = false;
		$this->isControversial = false;
		$this->isBad = false;
		$this->reason = "";
		$this->terseReason = '';
		$this->replacementText = "";
	}

	///////////////////////////////////////////// SPAM DETECTION //////////////////////
	// Some basic spam detection.
	// Spaces are normally 18% of English.  So, if space frequency drops below 9%, it is almost certainly spam.
	protected function detectSpam($inString) {


		// Block if this is a line of text without enough spaces
		$lengthOfInput = strlen( $inString);
		if ($lengthOfInput <= 1) return false;  // not spam if a single letter.
		$NSpaces = substr_count( $inString, ' ' );
		$spaceFrequency = $NSpaces / $lengthOfInput;
		// echo( "Length: $lengthOfInput Spaces: $NSpaces Space Frequency: $spaceFrequency <br>");

		if ( $lengthOfInput > 22 ){

			if ($spaceFrequency < 0.09 ) {
				$this->isBad = true;
				$this->isSpam = true;
				return true;
			}
		}

		return false;
	}

}
?>
