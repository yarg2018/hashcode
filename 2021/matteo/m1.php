<?php
	/**
	  * utilities for hashcode from yarg
	  *
	  * (C) 2021 by Matteo Pasotti <matteo.pasotti@gmail.com>
	  * (C) 2021 by Denis <>
	  * (C) 2021 by Andrea <>
	  * (C) 2021 by Roberto <>
	  */
	 
	/**
	  * Simple function to replicate PHP 5 behaviour
	  */
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}	 
	/**
	  * @method sort_mono_array
	  * @return boolean , true if success, false if something failed
	  * @param myarray array() to sort
	  * @param order string, ASC or DESC
	  */
	function sort_mono_array(&$myarray, $order)
	{
		return uasort($myarray, function ($a, $b) use ($order) {
				if(is_string($a))
					$cmp = strcmp($a,$b);
				elseif(is_numeric($a))
					$cmp = $a - $b;
				return (strcmp($order,"ASC")==0) ? $cmp : -$cmp;
			});
	}
	
	/**
	  * @method sort_array
	  * @return boolean , true if success, false if something failed
	  * @param myarray array() to sort
	  * @param keytosort --> key for the children array!!!
	  * @param order string, ASC or DESC
	  */
	function sort_multi_array(&$myarray, $keytosort, $order)
	{
		return uasort($myarray, function ($a, $b) use ($keytosort, $order) {
				if(is_string($a[$keytosort]))
					$cmp = strcmp($a[$keytosort],$b[$keytosort]);
				elseif(is_numeric($a[$keytosort]))
					$cmp = $a[$keytosort] - $b[$keytosort];
					#$cmp = ($a[$keytosort] > $b[$keytosort]) ? 1 : (($a[$keytosort] < $b[$keytosort]) ? -1 : 0);
				return (strcmp($order,"ASC")==0) ? $cmp : -$cmp;
			});
	}
	
	function create_mono_array($content, $delimiter, $bRE = false, $names = array(), $buildstats = false, $orderstats = false, &$grouparray = null)
	{
		$result = null;
		$phFound = false;
		
		if($bRE)
			$result = preg_split(@"/$delimiter/", $content);
		else
			$result = explode($delimiter, $content);
			
		if(count($names)>0)
		{
			$counter = 0;
			$assarray = array();
			$phPosition = 0;
			// the names array could contain special placeholder like @
			// PAY ATTENTION -- POOR PROGRAMMING PRACTICE: if a placeholder like this is specified, the other elements in increasing index order will be ignored
			foreach($names as $n)
			{
				if(strcmp(trim($n),"")==0)
					die("Invalid name-array passed to create_mono_array\n");
				// but if a placeholder has been found then the check is no longer required
				if(preg_match('/\@/',$n))
				{
					// force exit and rebuild the name array accordingly
					$phFound = true;
					break;
				}
			}
			if($phFound)
			{
				$phString = "";
				$phCounter = 0;
				for($nc = 0; $nc <count($names); $nc++)
				{
					if(preg_match('/\@/',$names[$nc]))
					{
						$phPosition = $nc;
						$phString = $names[$nc];
						// trim the array from the first placeholder position to the end
						array_slice($names,0,$nc-1);
						for($j = $nc; $j<count($result); $j++)
						{
							$names[$j] = preg_replace('/\@/', $phCounter, $phString);
							$phCounter++;
						}
						break;
					}
				}
			}
			for($i=0; $i<count($result); $i++)
			{
				$assarray[$names[$i]] = $result[$i];
			}
			
			if($phFound && $buildstats)
			{
				$tmparray = array_count_values(
					array_slice($assarray, $phPosition)
				);
				if(is_array($tmparray) && count($tmparray)>0)
				{
					if(!is_array($grouparray))
						$grouparray = $tmparray;
					else
					{
						foreach($tmparray as $tak => $tav)
						{
							if(in_array($tak,array_keys($grouparray)))
							{
								$grouparray[$tak]+=$tav;
							}
							else
							{
								$grouparray[$tak]=$tav;
							}
						}
					}
					if($orderstats)
					{
						sort_mono_array($grouparray, $orderstats);
					}
				}
			}
			
			$result = $assarray;
		}
		return $result;
	}
	
	/*
	 * @method create_multi_array()
	 * @return associative array coherent to the structure passed
	 * @param $content, array of string
	 * @param $struct_def, associative array that defines the rules to build the multidimensional array
	 *        it might be something like
	          $mystructdef = array(
								'key' => array(
									'delimiter' => string,
									'bRE' => bool,
									'skip' => integer|null,		// number of elements to skip forward or null if nothing must be skipped
									'rskip' => integer|null,		// number of elements to skip backward or null if nothing must be skipped
									'parent' => 0|null,				// the element that will become the parent or null for automatic - long
									'redundantkey' => true,      // store key as an attribute inside the children array
									'prefix' => string|null      // default to null, if parent null, autokey generation is ON and you could specify a prefix
									'children' => array()|null,	// an array specifing the position of the children on the same line of the parent [???]
									'order' => 'ASC|DESC'|null  // basic sorting support
								),
								'value' => array(
									'delimiter' => string,
									'bRE' => bool,
									'skip' => integer|null,		// number of elements to skip forward or null if nothing must be skipped
									'rskip' => integer|null,		// number of elements to skip backward or null if nothing must be skipped
									'parent' => 0,				// the element that will become the parent
									'children' => array()|null,	// an array specifing the position of the children on the same line of the parent [???]
									'names' => array()|null,    // respecting the position, you could name each elements to have an associative array (like a object) instead of a simple array
																// if one of the names contains @ an increasing integer/long will replace it
									'sort' => array(            // basic sorting support
														key => integer|string
														order => 'ASC|DESC'
											),														
								),
							);
	 */
	function create_multi_array($basearray, $struct_def = array())
	{
		$result = array();
		
		// build main associative array structure using keys
		//$debug = 0;
		$grouparray = (isset($struct_def['buildstats']) && $struct_def['buildstats']) ? array() : null; // the array that will contain the entire set of child for summary
		$autokey = ($struct_def['key']['parent'])==null ? true : false;
		$_key = 0;
		foreach($basearray as $ba)
		{
			if(isset($debug))
				$debug++;
			$mono = create_mono_array(
						$ba,
						$struct_def['key']['delimiter'],
						$struct_def['key']['bRE'],
						(isset($struct_def['value']['names']) && is_array($struct_def['value']['names'])) ? $struct_def['value']['names'] : array(),
						(isset($struct_def['buildstats']) && $struct_def['buildstats']),
						(isset($struct_def['orderstats']) && $struct_def['orderstats']),
						$grouparray
					);

			$key = ($autokey) ? $_key++ : $mono[$struct_def['key']['parent']];
			$key = isset($struct_def['key']['prefix']) ? $struct_def['key']['prefix'] . $key : $key;
			
			if(isset($struct_def['key']['redundantkey']) && $struct_def['key']['redundantkey'])
			{
				$mono['parentkey'] = $key;
			}
			
			if(!in_array($key, array_keys($result)))
			{
				$result[$key] = ($autokey) ? $mono : array_splice($mono, $struct_def['key']['parent']+1);
			}
			else
			{
				l($result);
				l("ERROR: Key [$key] already exists");
				die("FAILURE");
			}
			if(isset($debug) && $debug>=10) break;
		}

		
		$sort = isset($struct_def['key']['sort']) ? $struct_def['key']['sort'] : null;
		
		if(!is_null($sort))
		{
			// WARNING: sorting associative arrays loose keys definition in favor of integer/long
			foreach($sort as $s)
			{
				sort_multi_array($result, $s['key'], $s['order']);
			}
		}
		
		$result['_grouparray'] = $grouparray;
		
		return $result;
	}
	
	function parse_input_file($filename, $structure = null)
	{
		$content = create_mono_array(file_get_contents($filename),"\n");
		if(strcmp(trim($content[count($content)-1]),"")==0)
			array_pop($content); // drop last line (empty)
		$first_line = array_shift($content);
		return [$first_line, $content];
	}
	
	function group_entries($custom_array, $criteria)
	{
		/* result, associative array
		 * @param $custom_array array, associative array to analyze
		 * @param $criteria array, criteria for building groups
					criteria = array(
						'parent' => criteria,
						'child' => criteria,
						)
						
						 [99998] => Array
        (
            [n_ingredienti] => 9
            [ingredient0] => sc
            [ingredient1] => ad
            [ingredient2] => mc
            [ingredient3] => gb
            [ingredient4] => wc
            [ingredient5] => hd
            [ingredient6] => l
            [ingredient7] => vd
            [ingredient8] => ud
            [parentkey] => pizza99783
		 */
		$result = array();
		foreach($custom_array as $parent => $children)
		{
			foreach($children as $childk => $childv)
			{
				foreach($criteria as $ck => $cv)
				{
					if(preg_match('/'.$cv . '/', $childv))
					{
						
					}
					if(!in_array($childk, $result))
						$result[$childk]=1;
					else
						$result[$childk]++;
				}
			}
		}
		return $result;
	}
	
	function l($element)
	{
		if(!is_array($element))
			print("[*] $element\n");
		else
		{
			print("[*] Output given array:\n");
			print_r($element);
		}
	}
	
	function build_supermegaarray($filename)
	{
		list($fl, $content) = parse_input_file($filename);
		$intro_array = create_mono_array($fl, '\s', true);
		l("First line has " . count($intro_array) . " elements");
		
		l("Content line (first line excluded) has " . count($content) . " elements");
		
		l("First 4 lines for debug purpose");
		l(
			array(
				$content[0],
				$content[1],
				$content[2],
				$content[3]
			)
		);
		
		return array(
			$intro_array, 
			create_multi_array(
				$content, 
				array(
					'key' => array(
						'delimiter' => '\s',
						'bRE' => true,
						'parent' => null, //definisco la chiave automaticamente (progressivo, long)
						'prefix' => "pizza",
						'redundantkey' => true,
						'sort' => array(
								'f1' => array(
									'key'=>'n_ingredienti',
									'order'=>'DESC'
									),
							),
					),
					'value' => array(
						'delimiter' => '\s',
						'bRE' => true,
						'names' => array('n_ingredienti', 'ingredient@'),
					),
					'buildstats' => true,
					'orderstats' => true,
				)
			)
		);
	}
	
	function main()
	{
		$time_start = microtime_float();
		
		# file_summary("a_example");
		# file_summary("e_many_teams.in");
		list($intro_array, $b_little_bit_of_everything) = build_supermegaarray("b_little_bit_of_everything.in");
		
		l($b_little_bit_of_everything);
		
		$pizze_disponibili = $intro_array[0];
		
		$rawteams = array_slice($intro_array,1);
		l($rawteams);
		
		$teams = array();
		$peopleCount = 2;  //can't be less than two
		$n_ordini = 0;
		$pizze_necessarie = 0;
		
		foreach($rawteams as $rt)
		{
			array_push(
				$teams,
				array(
					'people' => $peopleCount++,
					'number' => $rt,
					)
				);
			$n_ordini += $rt;
			$pizze_necessarie += ($peopleCount * $rt);
		}
		
		sort_multi_array($teams, "people", 'DESC');
		l("Sorted teams");
		l($teams);
		l("Pizze necessarie: $pizze_necessarie");
		l("Pizze disponibili: $pizze_disponibili");
		l("No Ordini: $n_ordini");
		
		l("Begin");
		l("");
		
		$bNoMorePizzaLeft=false;
		$strip=array();
		$lines=array();
		foreach($teams as $t)
		{
			for($tn = 0; $tn < $t['number']; $tn++)
			{
				array_push($strip,$t['people']);
				for($i = 0; $i < $t['people']; $i++)
				{	
					if(count($b_little_bit_of_everything)==1)
					{
						$bNoMorePizzaLeft=true;
						break; //we reached the end of the array | MEMO last item of the supermegaarray is the synthesis of ingredients
					}
					$curr_pizza = array_shift($b_little_bit_of_everything);
					array_push($strip, preg_replace('/pizza/','',$curr_pizza['parentkey']));
				}
				if($bNoMorePizzaLeft)
					break;
				array_push($lines,implode(" ",$strip));
				$strip = array();
			}
			if($bNoMorePizzaLeft)
				break;
		}
		l("End");
		l("");
		$output = implode("\n", $lines);
		//$output = preg_replace('/^\s+/','',$output);
		$no_served_teams = count(preg_split('/\n/', $output));
		l("Number of output lines: " . $no_served_teams);
		l("Output demo");
		print($no_served_teams."\n");
		print($output."\n");
		
		$time_end = microtime_float();
		$time = $time_end - $time_start;
		l("Elapsed time: $time");
	}
	
	main();
?>
