<?php
	/**
	  * utilities for hashcode from yarg
	  *
	  * (C) 2021 by Matteo Pasotti <matteo.pasotti@gmail.com>
	  * (C) 2021 by Denis <>
	  * (C) 2021 by Andrea <>
	  * (C) 2021 by Roberto <>
	  */
	  
	function create_mono_array($content, $delimiter, $bRE = false)
	{
		if($bRE)
			return preg_split(@"/$delimiter/", $content);
		else
			return explode($delimiter, $content);
	}
	
	/*
	 * @method create_multi_array()
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
									'children' => array()|null,	// an array specifing the position of the children on the same line of the parent [???]
								),
								'value' => array(
									'delimiter' => string,
									'bRE' => bool,
									'skip' => integer|null,		// number of elements to skip forward or null if nothing must be skipped
									'rskip' => integer|null,		// number of elements to skip backward or null if nothing must be skipped
									'parent' => 0,				// the element that will become the parent
									'children' => array()|null,	// an array specifing the position of the children on the same line of the parent [???]
								),
							);
	 */
	function create_multi_array($basearray, $struct_def = array())
	{
		$result = array();
		
		// build main associative array structure using keys
		//$debug = 0;
		$autokey = ($struct_def['key']['parent'])==null ? true : false;
		$_key = 0;
		foreach($basearray as $ba)
		{
			$debug++;
			$mono = create_mono_array($ba, $struct_def['key']['delimiter'], $struct_def['key']['bRE']);
			$key = ($autokey) ? $_key++ : $mono[$struct_def['key']['parent']];
			if(!in_array($key, array_keys($result)))
			{
				$result[$key] = ($autokey) ? $mono : array_splice($mono, $struct_def['key']['parent']+1);
			}
			else
			{
				print_r($result);
				l("ERROR: Key [$key] already exists");
				die("FAILURE");
			}
			if(isset($debug) && $debug>=10) break;
		}
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
	
	function group_entries($custom_array)
	{
		/* result, associative array
		 * key => occurrence
		 * value => count of the occurrencies into the given dataset
		 */
		$result = array();
		foreach($custom_array as $item)
		{
			if(!in_array($item, $result))
			{
				$result[$item] = 0;
			}
			else
			{
				$result[$item]++;
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
	
	function file_summary($filename)
	{
		list($fl, $content) = parse_input_file($filename);
		l("First line has " . count(create_mono_array($fl, '\s',true)) . " elements");
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
		
		print_r(create_multi_array($content, array(
										'key' => array(
											'delimiter' => '\s',
											'bRE' => true,
											'parent' => null, //definisco la chiave automaticamente (progressivo, long)
										),
										'value' => array(
											'delimiter' => '\s',
											'bRE' => true,
										),
									))
									);
	}
	
	function main()
	{
		file_summary("a_example");
	}
	
	main();
?>