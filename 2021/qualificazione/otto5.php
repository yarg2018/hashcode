<?php
  /**
    * Hashcode 2021 Qualification from yarg
    *
    * (C) 2021 by Matteo <>
    * (C) 2021 by Denis <>
    * (C) 2021 by Andrea Ottini <ottini.andrea@gmail.com>
    * (C) 2021 by Roberto <>
    *
    * Changes compared to otto2.php:
    * - Sorting cars by number of street to be followed
    * - Checking that car can be shipped towards the end in time (<D) before recording the solution
    *
    */
$filename[0]="a.txt";

$filename[1]="b.txt";
$filename[2]="c.txt";
$filename[3]="d.txt";
$filename[4]="e.txt";
$filename[5]="f.txt";
/*
*/
$minutes = 0;

foreach($filename as $fname){
  $zero=time();
  echo "Startin ".$fname."\n";
  $file=str_replace(array("\n","\r"),'',file($fname));
	$tmp=explode(" ",$file[0]);

	$D=$tmp[0];
	$I=$tmp[1];
	$S=$tmp[2];
	$V=$tmp[3];
	$F=$tmp[4];

  $street = array();
  $incrocio = array();
  //$numero_strada = array();
	for($i=0;$i<$S;$i++){
		$tmp=explode(" ",$file[$i+1]);
		$street[$i]['id'] = $i;
    //$street[$i]['start']=$tmp[0];
		$street[$i]['end']=$tmp[1];
		$street[$i]['name']=$tmp[2];
		$street[$i]['len']=$tmp[3];
    //$numero_strada[$street[$i]['name']] = $i;
    if(!array_key_exists($street[$i]['end'],$incrocio))
      $incrocio[$street[$i]['end']] = array();
    array_push($incrocio[$street[$i]['end']], $street[$i]['name']);
	}
  $numero_strada = array_column($street, 'id', 'name');

  $car = array();
	for($i=0;$i<$V;$i++){
		$tmp=explode(" ",$file[$i+$S+1]);
		$car[$i]['id'] = $i;
    $car[$i]['n_strade'] = $tmp[0];
    $car[$i]['score'] = 0;
		for($l=0;$l<$car[$i]['n_strade'];$l++){
			$car[$i][$l] = $tmp[$l+1];
      $car[$i]['score'] = $car[$i]['score'] + $street[$numero_strada[$tmp[$l+1]]]['len'];
    }
    if($car[$i]['score'] > $D){
      // will not contribute to score; will just be in the traffic
      $car[$i]['score'] = 0;
    }
	}

  echo "durata=".$D."\n";
  echo "incroci=".$I." ".count($incrocio)."\n";
  echo "strade=".$S." ".count($street)."\n";
  echo "cars=".$V." ".count($car)."\n";
  echo "bonus=".$F."\nid";

//print_r($car);

echo "\nsorting...";

array_multisort(array_column($car, 'score'), SORT_DESC, $car);
for($i=0;$i<$S;$i++){
  $numero_strada[$street[$i]['name']] = $i;
  if(!array_key_exists($street[$i]['end'],$incrocio))
    $incrocio[$street[$i]['end']] = array();
  array_push($incrocio[$street[$i]['end']], $street[$i]['name']);
}
echo "Done in ".(time()-$zero).".\n";

echo "\nInizio calcoli...";

$result = array();
for($i=0;$i<$V;$i++){
  // for each car before storing results we check final $T is below $D
  $T = 0;
  $temp_result = array();

  for($chiave=0;$chiave<$car[$i]['n_strade'];$chiave++){
    $stradapercorsa = $car[$i][$chiave];
    if($fname=='a.txt')
      echo "\nauto: ".$i."; chiave: ".$chiave."; strada: ".$stradapercorsa."\n";
    $chiave_strada = $numero_strada[$stradapercorsa];
    $T = $T + $street[$chiave_strada]['len'];
    if(!array_key_exists($street[$chiave_strada]['end'],$result))
      $result[$street[$chiave_strada]['end']] = array();
    if(!array_key_exists($street[$chiave_strada]['name'],$result[$street[$chiave_strada]['end']]))
      $result[$street[$chiave_strada]['end']][$street[$chiave_strada]['name']] = array();
    if(!array_key_exists($T,$result[$street[$chiave_strada]['end']][$street[$chiave_strada]['name']])){
      $result[$street[$chiave_strada]['end']][$street[$chiave_strada]['name']][$T] = 1;
    }else{
      $result[$street[$chiave_strada]['end']][$street[$chiave_strada]['name']][$T]++;
      // need to increase $T because I have to wait a turn
      $T= $T+$result[$street[$chiave_strada]['end']][$street[$chiave_strada]['name']][$T];
    }
  }
}
echo "Done in ".(time()-$zero).".\n";

echo "\nOttimizzo...";
$generation = 0;
//echo "\n Generation ".$generation." completed: ".get_score_full($result);
$last_cycle = time() + ($minutes * 60); // gira per 5 minuti
while(time() < $last_cycle){
  $generation++;


}

foreach($result as $inc => $result_inc){
  $temp_count_street=0;
  echo "\nIncrocio ".$inc;
  foreach($result_inc as $str => $result_str){
    $temp_count_street++;
    $temp_max = 0;
    $temp_sum = 0;
    if(is_array($result_str)){
      if(count($result_str)==0)
        $temp_max = 0;
      else{
        $temp_max = max($result_str);
        $temp_sum = $temp_sum + array_sum($result_str);
        $result[$inc][$str]['max'] = $temp_max;
        $result[$inc][$str]['sum'] = $temp_sum;
      }
      //echo"\nStrata ".$str." max veicoli: ".$temp_max."; sum:".$temp_sum;
    }
  }
  unset($str);
  unset($result_str);
//  echo "\nTot: ".$temp_count_street."strade; sum: ".join(" ",array_column($result[$inc],'sum'));//array_sum($result[$inc]));
  foreach($result_inc as $str => $result_str){
    $result[$inc][$str]['schedule'] = ceil($temp_count_street * $result[$inc][$str]['sum'] / array_sum(array_column($result[$inc],'sum')));
    //$result[$inc][$str]['schedule'] = ceil($result[$inc][$str]['sum']);
  }
  unset($str);
  unset($result_str);

}
unset($inc);
unset($result_inc);



echo "Done in ".(time()-$zero).".\n";


echo "stampo...";
//print_r($result);
//print_r($result);
$stringa_output = array();
$stringa_output[0] = count($result);
foreach($result as $l => $line){
  array_push($stringa_output, $l);
  array_push($stringa_output, count($line));
  foreach($line as $line_street => $line_time){
      if($fname == "b.txt")
        print_r($line_time);
      array_push($stringa_output, $line_street." ".$line[$line_street]['schedule']);
  }
}
unset($result);
file_put_contents($fname.".out", join("\n", $stringa_output));
echo "OK;".(time()-$zero).";\n";
echo "result...".$stringa_output[0]."\n";
echo"------------\n";

}
?>
