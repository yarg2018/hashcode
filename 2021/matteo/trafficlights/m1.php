<?php
	/**
	  * utilities for hashcode from yarg
	  *
	  * (C) 2021 by Matteo Pasotti <matteo.pasotti@gmail.com>
	  * (C) 2021 by Denis <>
	  * (C) 2021 by Andrea <>
	  * (C) 2021 by Roberto <>
	  */
	 
	$M1_DISABLE_DEBUGOUTPUT = false;

	 
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
	
	function create_mono_array($content, $delimiter, $bRE = false, $names = array(), $buildstats = false, $orderstats = false, &$grouparray = null, $enrichment = null)
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
			
			$mustEnrich = (isset($enrichment) && is_callable($enrichment['function']) && !is_null($enrichment));
			
			for($i=0; $i<count($result); $i++)
			{
				if($mustEnrich && 
					preg_match('/^'.preg_replace('/\@/', '', $phString).'/', $names[$i]))
				{
					$assarray[$names[$i]] = array();
					$assarray[$names[$i]]['base'] = $result[$i];
					$assarray[$names[$i]][$enrichment['keyname']] = $enrichment['function'](
							$result[$i],
							$enrichment['arraytosearch']
							);
				}
				else
				{
					$assarray[$names[$i]] = $result[$i];
				}
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
						l("Ordering stats");
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
		
		$names = (isset($struct_def['value']['names']) && is_array($struct_def['value']['names'])) ? $struct_def['value']['names'] : array();
		$build_stats = (isset($struct_def['buildstats']) && $struct_def['buildstats']);
		$order_stats = (isset($struct_def['orderstats']) && $struct_def['orderstats']);
		$enrich = (isset($struct_def['value']['enrich']) && is_callable($struct_def['value']['enrich']['function']))?$struct_def['value']['enrich']:null;
		
		$redundant_key = isset($struct_def['key']['redundantkey']) && $struct_def['key']['redundantkey'];
		
		$progress = 0;
		$progress_max = count($basearray)-1;
		foreach($basearray as $ba)
		{
			print("[".round($progress++/$progress_max*100,0)."%]\r");
			if(isset($debug))
				$debug++;
			$mono = create_mono_array(
						$ba,
						$struct_def['key']['delimiter'],
						$struct_def['key']['bRE'],
						$names,
						$build_stats,
						$order_stats,
						$grouparray,
						$enrich,
					);
			
			$key = ($autokey) ? $_key++ : $mono[$struct_def['key']['parent']];
			$key = isset($struct_def['key']['prefix']) ? $struct_def['key']['prefix'] . $key : $key;		
			
			if($redundant_key)
			{
				$mono['parentkey'] = $key;
			}
			
			if(!isset($result[$key]))
				$result[$key] = null;
			$result[$key] = ($autokey) ? $mono : array_splice($mono, $struct_def['key']['parent']+1);
			
			/*if(!in_array($key, array_keys($result)))
			{
				$result[$key] = ($autokey) ? $mono : array_splice($mono, $struct_def['key']['parent']+1);
			}
			else
			{
				l($result);
				l("ERROR: Key [$key] already exists");
				die("FAILURE");
			}*/
			if(isset($debug) && $debug>=10) break;
		}

		
		$sort = isset($struct_def['key']['sort']) ? $struct_def['key']['sort'] : null;
		
		if(!is_null($sort))
		{
			l("Sorting multi array");
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
		global $M1_DISABLE_DEBUGOUTPUT;
		if($M1_DISABLE_DEBUGOUTPUT) return;
		if(!is_array($element))
			print("[*] $element\n");
		else
		{
			print("[*] Output given array:\n");
			print_r($element);
		}
	}
	
	function build_supermegaarray($content, $key_structure, $value_structure)
	{	
		/*if(!is_null($costraints))
		{
			if(is_array($costraints))
			{
				$list_of_content = array();
				$curr_row = 0;
				foreach($costraints as $c)
				{
					array_push(
						$list_of_content, 
						array_splice(
							$content, 
							$curr_row, 
							$intro_array[$c['ia_idx']]
							)
						);
				}
			}
		}*/
		
		l("Content line has " . count($content) . " elements");
		
		return create_multi_array(
				$content, 
				array(
					'key' => $key_structure,
					'value' => $value_structure,
					'buildstats' => false,
					'orderstats' => false,
				)
			);
	}
	
	
	
	function elaborate_single_file($fname)
	{	
		$time_start = microtime_float();
				
		$costraints = array(
			array('ia_idx'=>2),
			array('ia_idx'=>3),
			);
			
		list($fl, $content) = parse_input_file($fname);
		$intro_array = create_mono_array($fl, '\s', true);
		l("First line has " . count($intro_array) . " elements");
		
		$sim_duration = $intro_array[0];
		
		$intersections = $intro_array[1];
		
		$streets = $intro_array[2];
		
		$cars = $intro_array[3];
		
		$premio = $intro_array[4];
		
		l("Building streets array");
		$streets_array = build_supermegaarray(
							array_slice($content,0,$intro_array[2]),
							array(
								'delimiter' => '\s',
								'bRE' => true,
								'parent' => null, //definisco la chiave automaticamente (progressivo, long)
								'prefix' => "streets",
								'redundantkey' => true,
								/*'sort' => array(
									'f1' => array(
										'key'=>'street_length',
										'order'=>'DESC'
										),
									),*/
								),
							array(
								'delimiter' => '\s',
								'bRE' => true,
								'names' => array('intersection_start', 'intersection_end', 'street_name', 'street_length'),
							)
						);
		
		l("Building cars array");
		$array_to_search = array_flip(
			array_column($streets_array, 'street_name', 'parentkey')
		);
		$cars_array = build_supermegaarray(
							array_slice($content,$intro_array[2],$intro_array[3]),
							array(
								'delimiter' => '\s',
								'bRE' => true,
								'parent' => null, //definisco la chiave automaticamente (progressivo, long)
								'prefix' => "cars",
								'redundantkey' => true,
								),
							array(
								'delimiter' => '\s',
								'bRE' => true,
								'names' => array('no_streets_to_travel', 'street@'),
								'enrich' => array(
									'keyname' => 'street_dtl',
									//'keytosearch' => 'street@',
									'arraytosearch' => $array_to_search,
									'function' => function($street_name, $sarray)
													{
														return $sarray[$street_name];
													}
									/*'arraytosearch' => &$streets_array,
									'function' => function($street_name, $sarray){
										foreach($sarray as $street)
										{
											if($street['street_name']===$street_name)
											{
												return $street;
											}
										}
										},*/
									),
							)		
						);
						
		#list($intro_array, $b_little_bit_of_everything) = build_supermegaarray("b.txt");
		#list($intro_array, $b_little_bit_of_everything) = build_supermegaarray("c.txt");
		#list($intro_array, $b_little_bit_of_everything) = build_supermegaarray("d.txt");
		#list($intro_array, $b_little_bit_of_everything) = build_supermegaarray("e.txt");
		
		/*
		l("Streets");
		l($streets_array);
		
		l("Cars");
		l($cars_array);
		*/
		
		l("Begin");
		l("");
		
		$tempo = 0;
		$result = array();
		
		array_pop($cars_array);
		
		foreach($cars_array as $car)
		{
			$streets_to_travel=0;
			foreach($car as $key => $val)
			{
				if(preg_match('/street/', $key))
					$streets_to_travel++;
			}
			$streets_to_travel--;
			for($scount = 0; $scount < $streets_to_travel; $scount++)
			{
				$curr_street = $streets_array[$car["street$scount"]['street_dtl']];
				if(!array_key_exists($curr_street['intersection_end'], $result))
					$result[$curr_street['intersection_end']]=array();
				if(!in_array($curr_street['street_name'],$result[$curr_street['intersection_end']]))
				{
					$result[$curr_street['intersection_end']][$curr_street['street_name']] = 1;
				}
				$tempo++;
				$tempo += $curr_street['street_length'];
			}
		}
		
		l("Writing data to file");
		
		$stringa_output = array();
		$stringa_output[0] = count($result);
		foreach($result as $l => $line)
		{
			array_push($stringa_output, $l);
			array_push($stringa_output, count($line));
			foreach($line as $line_street => $line_time)
			{
				array_push($stringa_output, $line_street." ".$line_time);
			}
		}
		file_put_contents($fname.".out", join("\n", $stringa_output));
		l("End");
		l("");
		
		/*
		$output = implode("\n", $lines);
		//$output = preg_replace('/^\s+/','',$output);
		$no_served_teams = count(preg_split('/\n/', $output));
		l("Number of output lines: " . $no_served_teams);
		l("Output demo");
		print($no_served_teams."\n");
		print($output."\n");
		*/
		$time_end = microtime_float();
		$time = $time_end - $time_start;
		l("Elapsed time: $time");
	}
	
	function main()
	{
		global $M1_DISABLE_DEBUGOUTPUT;
		
		$shortopts = "s";
		$longopts = array(
			'silent',
			);
		$options = getopt($shortopts, $longopts);
		
		if(isset($options['s']))
		{
			$M1_DISABLE_DEBUGOUTPUT=true;
		}
		
		$infiles = array('a','b','c','d','e','f');
		
		foreach($infiles as $fname)
		{
			l("Processing file: $fname.txt");
			elaborate_single_file($fname . ".txt");
		}
	}
	
	main();
?>
