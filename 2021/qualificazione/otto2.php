<?php

$filename[0]="a.txt";

$filename[1]="b.txt";
$filename[2]="c.txt";
$filename[3]="d.txt";
$filename[4]="e.txt";
$filename[5]="f.txt";
/*
*/

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
    $street[$i]['start']=$tmp[0];
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

		for($l=0;$l<$car[$i]['n_strade'];$l++){
			$car[$i][$l] = $tmp[$l+1];
    }
	}

  echo "durata=".$D."\n";
  echo "incroci=".$I." ".count($incrocio)."\n";
  echo "strade=".$S." ".count($street)."\n";
  echo "cars=".$V." ".count($car)."\n";
  echo "bonus=".$F."\nid";

//print_r($car);

if(false){
echo "\nsorting...";

array_multisort(array_column($street, 'len'), SORT_DESC, $street);
for($i=0;$i<$S;$i++){
  $numero_strada[$street[$i]['name']] = $i;
  if(!array_key_exists($street[$i]['end'],$incrocio))
    $incrocio[$street[$i]['end']] = array();
  array_push($incrocio[$street[$i]['end']], $street[$i]['id name']);
}
echo "Done in ".(time()-$zero).".\n";
}

echo "\nInizio calcoli...";
/*
$parametro = //ceil($D/20);
$T=0;
$result = array();
foreach($car as $c){
  $temp_time = 0;
  $temp_result = array();
  foreach($c as $chiave =>  $stradapercorsa){
    if($chiave == 'id' || $chiave == 'n_strade')
      continue;
    $chiave_strada = $numero_strada[$stradapercorsa];
    $temp_time = $temp_time + $street[$chiave_strada]['len'] + 1;
    if(!array_key_exists($street[$chiave_strada]['end'],$temp_result))
      $temp_result[$street[$chiave_strada]['end']] = array();
    $temp_result[$street[$chiave_strada]['end']][$street[$chiave_strada]['name']] = 1;
  }
  unset($chiave);
  unset($stradapercorsa);
  unset($c);
//  print_r($temp_result);
  if($temp_time < $D){
    foreach($temp_result as $k_temp => $value_temp){
      if(!array_key_exists($k_temp,$result)){
        $result[$k_temp] = array();
      }
      foreach($value_temp as $name_temp => $time_temp){
        $result[$k_temp][$name_temp] = $time_temp;
      }
      unset($name_temp);
      unset($time_temp);
    }
    unset($k_temp);
    unset($value_temp);
  }

}
*/
$T=0;
$result = array();
for($i=0;$i<$V;$i++){

  for($chiave=0;$chiave<$car[$i]['n_strade'];$chiave++){
    $stradapercorsa = $car[$i][$chiave];
    if($fname=='a.txt')
      echo "\nauto: ".$i."; chiave: ".$chiave."; strada: ".$stradapercorsa."\n";
    $chiave_strada = $numero_strada[$stradapercorsa];
    if(!array_key_exists($street[$chiave_strada]['end'],$result))
      $result[$street[$chiave_strada]['end']] = array();
    if(!in_array($street[$chiave_strada]['name'],$result[$street[$chiave_strada]['end']]))
      $result[$street[$chiave_strada]['end']][$street[$chiave_strada]['name']] = 1;

    $T++;
    $T = $T + $street[$chiave_strada]['len'];

  }
}
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
      array_push($stringa_output, $line_street." ".$line_time);
  }
}
file_put_contents($fname.".out", join("\n", $stringa_output));
echo "OK;".(time()-$zero).";\n";
echo "result...".$stringa_output[0]."\n";
echo"------------\n";

}
?>
