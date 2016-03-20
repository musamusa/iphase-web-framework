<?php
/*
*************************************************************************** 
*   Copyright (C) 2008 by Felipe Ribeiro                                  * 
*   felipernb@gmail.com                                                   * 
*   http://www.feliperibeiro.com                                          * 
*                                                                         * 
*   Permission is hereby granted, free of charge, to any person obtaining * 
*   a copy of this software and associated documentation files (the       * 
*   "Software"), to deal in the Software without restriction, including   * 
*   without limitation the rights to use, copy, modify, merge, publish,   * 
*   distribute, sublicense, and/or sell copies of the Software, and to    * 
*   permit persons to whom the Software is furnished to do so, subject to * 
*   the following conditions:                                             * 
*                                                                         * 
*   The above copyright notice and this permission notice shall be        * 
*   included in all copies or substantial portions of the Software.       * 
*                                                                         * 
*   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,       * 
*   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF    * 
*   MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.* 
*   IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR     * 
*   OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, * 
*   ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR * 
*   OTHER DEALINGS IN THE SOFTWARE.                                       * 
*************************************************************************** 
*/ 


/**
 * This class implements the Spell correcting feature, useful for the 
 * "Did you mean" functionality on the search engine. Using a dicionary of words
 * extracted from the product catalog.
 * 
 * Based on the concepts of Peter Norvig: http://norvig.com/spell-correct.html
 * 
 * @author Felipe Ribeiro <felipernb@gmail.com>
 * @date September 18th, 2008
 * @package catalog
 *
 */
class spellcheck {
	private static $NWORDS;
	
	/**
	 * Reads a text and extracts the list of words
	 *
	 * @param string $text
	 * @return array The list of words
	 */
	private static function  words($text) {
		$matches = array();
		preg_match_all("/[a-z]+/",strtolower($text),$matches);
		return $matches[0];
	}
	
	/**
	 * Creates a table (dictionary) where the word is the key and the value is it's relevance 
	 * in the text (the number of times it appear)
	 *
	 * @param array $features
	 * @return array
	 */
	private static function train(array $features) {
		$model = array();
		$count = count($features);
		for($i = 0; $i<$count; $i++) {
			$f = $features[$i];
			$model[$f] +=1;
		}
		return $model;
	}
	
	/**
	 * Generates a list of possible "disturbances" on the passed string
	 *
	 * @param string $word
	 * @return array
	 */
	private static function edits1($word) {
		$alphabet = 'abcdefghijklmnopqrstuvwxyz';
		$alphabet = str_split($alphabet);
		$n = strlen($word);
		$edits = array();
		for($i = 0 ; $i<$n;$i++) {
			$edits[] = substr($word,0,$i).substr($word,$i+1); 		//deleting one char
			foreach($alphabet as $c) {
				$edits[] = substr($word,0,$i) . $c . substr($word,$i+1); //substituting one char
			}
		}
		for($i = 0; $i < $n-1; $i++) {
			$edits[] = substr($word,0,$i).$word[$i+1].$word[$i].substr($word,$i+2); //swapping chars order
		}
		for($i=0; $i < $n+1; $i++) {
			foreach($alphabet as $c) {
				$edits[] = substr($word,0,$i).$c.substr($word,$i); //inserting one char
			}
		}

		return $edits;
	}
	
	/**
	 * Generate possible "disturbances" in a second level that exist on the dictionary
	 *
	 * @param string $word
	 * @return array
	 */
	private static function known_edits2($word) {
		$known = array();
		foreach(self::edits1($word) as $e1) {
			foreach(self::edits1($e1) as $e2) {
				if(array_key_exists($e2,self::$NWORDS)) $known[] = $e2;				
			}
		}
		return $known;
	}
	
	/**
	 * Given a list of words, returns the subset that is present on the dictionary
	 *
	 * @param array $words
	 * @return array
	 */
	private static function known(array $words) {
		$known = array();
		foreach($words as $w) {
			if(array_key_exists($w,self::$NWORDS)) {
				$known[] = $w;

			}
		}
		return $known;
	}
	
	
	/**
	 * Returns the word that is present on the dictionary that is the most similar (and the most relevant) to the
	 * word passed as parameter, 
	 *
	 * @param string $word
	 * @return string
	 */
	public static function correct($word) {
		$word = trim($word);
		if(empty($word)) return;
		
		$word = strtolower($word);
		
		if(empty(self::$NWORDS)) {
			
			/* To optimize performance, the serialized dictionary can be saved on a file
			instead of parsing every single execution */
			if(!file_exists(dirname(__FILE__).'/serialized_dictionary.txt')) {
				self::$NWORDS = self::train(self::words(file_get_contents(dirname(__FILE__)."/big.txt")));
				$fp = fopen(dirname(__FILE__)."/serialized_dictionary.txt","w+");
				fwrite($fp,serialize(self::$NWORDS));
				fclose($fp);
			} else {
				self::$NWORDS = unserialize(file_get_contents(dirname(__FILE__)."/serialized_dictionary.txt"));
			}
		}
		$candidates = array(); 
		if(self::known(array($word))) {
			return $word;
		} elseif(($tmp_candidates = self::known(self::edits1($word)))) {
			foreach($tmp_candidates as $candidate) {
				$candidates[] = $candidate;
			}
		} elseif(($tmp_candidates = self::known_edits2($word))) {
			foreach($tmp_candidates as $candidate) {
				$candidates[] = $candidate;
			}
		} else {
			return $word;
		}
		$max = 0;
		foreach($candidates as $c) {
			$value = self::$NWORDS[$c];
			if( $value > $max) {
				$max = $value;
				$word = $c;
			}
		}
		return $word;
	}
	
	static function getBigWords(){
		if(!file_exists(dirname(__FILE__).'/serialized_dictionary.txt')) {
			if(file_exists(file_get_contents(dirname(__FILE__)."/big.txt"))){
				self::$NWORDS = self::train(self::words(file_get_contents(dirname(__FILE__)."/big.txt")));
				$fp = fopen(dirname(__FILE__)."/serialized_dictionary.txt","w+");
				fwrite($fp,serialize(self::$NWORDS));
				fclose($fp);
			}
		} else {
			self::$NWORDS = unserialize(file_get_contents(dirname(__FILE__)."/serialized_dictionary.txt"));
		}
		return self::$NWORDS;
	}
	
	static function trains() {
		if(!file_exists(dirname(__FILE__).'/serialized_dictionary2.txt')) {
			 $contents = file_get_contents(dirname(__FILE__)."/big.txt");
			  preg_match_all('/\w+/', $contents, $matches);
			  unset($contents);
			  $dictionary = array();
			  foreach ($matches[0] as $word) {
				 $word = strtolower($word);
				 $soundex_key = soundex($word);
				 if (!isset($dictionary[$soundex_key][$word])) {
					$dictionary[$soundex_key][$word] = 0;
				 }
				 $dictionary[$soundex_key][$word] += 1;
			  }
			  unset($matches);
			$fp = fopen(dirname(__FILE__)."/serialized_dictionary2.txt","w+");
			fwrite($fp, serialize($dictionary));
			fclose($fp);
			return $dictionary;
		} else {
			$dictionary = unserialize(file_get_contents(dirname(__FILE__)."/serialized_dictionary2.txt"));
			return $dictionary;
		}	 
	}
	static function corrected($word) {
		$dic = self::trains();
	  if (array_key_exists($word, $dic)) {
		 return $word;
	  }
	
	  $search_result = $dic[soundex($word)];
	  if(array_key_exists($word, $search_result)){
			  return $word;
		}
		if(!empty($search_result)){
		  foreach ($search_result as $key => &$res) {
			 $dist = levenshtein($key, $word);
			  echo $key.":".$dist."<br>";
			 // consider just distance equals to 1 (the best) or 2
			 if ($dist == 1 || $dist == 2) {
				$res = $res / $dist;
			 }
			 // discard all the other candidates that have distances other than 1 and 2
			 // from the original word
			 else {
				unset($search_result[$key]);
			 }
		  }
		
		  // reverse sorting of the words by frequence
		  arsort($search_result);
		  // return the first key of the array (which will be the word suggested)
		  
		  foreach ($search_result as $key => $res) {
			 return $key;
		  }
		}
		return $word;
	}
}