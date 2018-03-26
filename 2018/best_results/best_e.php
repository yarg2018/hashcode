<?php
require_once("2018/utility.php");

$input_files = array('2018/d_metropolis','2018/e_high_bonus','2018/c_no_hurry','2018/a_example','2018/b_should_be_easy');
//input_files = array('2018/a_example');

$hashcode_score=array();
foreach($input_files as $input_file)
{
    print("== lavoro $input_file\n");
	$file=str_replace(array("\n","\r"),"",file($input_file . ".in"));
	$n=0;
	$tmp=explode( " ",$file[$n++]);

    $rows=$tmp[0];
    $columns=$tmp[1];
    $vehicles=$tmp[2];
    $rides=$tmp[3];
    $bonus =$tmp[4];
    $steps=$tmp[5];

    print("rows=$rows; cols=$columns; veich=$vehicles; corse=$rides; bonus=$bonus; steps=$steps\n");
    $corse = array();

    $ride = array('id'=>array(),'a'=>array(),'b'=>array(),'x'=>array(),'y'=>array(),'s'=>array(),'f'=>array(),'distance'=>array());

    for ($i=0; $i< $rides; $i++){
        list($ride['a'][$i],$ride['b'][$i],$ride['x'][$i],$ride['y'][$i],$ride['s'][$i],$ride['f'][$i]) = explode(" ",$file[$n++]);
        $ride['distance'][$i] = distanza($ride['a'][$i],$ride['b'][$i],$ride['x'][$i],$ride['y'][$i]);
        $ride['id'][$i] = $i;
    }

    array_multisort(
        $ride['distance'],SORT_DESC, SORT_NUMERIC,
        $ride['s'],SORT_ASC, SORT_NUMERIC,
        $ride['id'],
        $ride['a'],
        $ride['b'],
        $ride['x'],
        $ride['y'],
        $ride['f']
    );

    for($j=0;$j<count($ride['a']);$j++)
    {
        $corse[] = array(
            'id'	=>	$ride['id'][$j],
            'a'	=>	$ride['a'][$j],
            'b'	=>	$ride['b'][$j],
            'x'	=>	$ride['x'][$j],
            'y'	=>	$ride['y'][$j],
            's'	=>	$ride['s'][$j],
            'f'	=>	$ride['f'][$j],
            'distance'=>	$ride['distance'][$j]);
    }


$auto_x=array();
$auto_y=array();
$out=array();
$auto_available=array();
$invalid_couple = array();

for($v=0; $v<$vehicles; $v++){
	$auto_available[$v]=0;
	$out[$v]=array();
	$auto_x[$v]=0;
	$auto_y[$v]=0;
  $invalid_couple[$v]=array();
}

$time_to_start=0;
$time_corsa=array();
$best_attesa=20*($steps+$rows+$columns);
$hashcode_score[$input_file]=0;
$counter=0;
while(min($auto_available)<$steps && count($corse)>0){
  $counter++;
	$v=array_search(min($auto_available), $auto_available);
  if($counter % 1000 == 0){
    print($input_file[5].">auto $v available at ".$auto_available[$v]."; remaining ".count($corse)." rides\n");
  }
  if($auto_available[$v]>=$steps){
    break;
  }
  foreach(array_keys($corse) as $c_key){
			$c = $corse[$c_key];
      $dist=distanza($auto_x[$v], $auto_y[$v],$c['a'],$c['b']);
			$time_to_start=$auto_available[$v]+$dist;
      $time_corsa[$c_key] = max($time_to_start, $c['s'])+$c['distance'];
      if($time_corsa[$c_key]>$c['f']){
        $score[$c_key] = -$best_attesa;
      }else{
        $score[$c_key] = $c['distance']-$dist;//-3*max($c['s']-$time_to_start,0);
      }
		}
  $chiave=array_search(max($score),$score);
  if($score[$chiave]==-$best_attesa){
     $auto_available[$v]=$steps+1;
     continue;
  }
  if(array_key_exists($chiave,$corse)){
    if($time_corsa[$chiave]>$steps){
      //print("==>Inalid ride: $v".$corse[$chiave]['id']."\n");
      //non dovrebbe succedere
      $auto_available[$v]=$steps+1;
    }else{
      //print("==>Valid ride: $v".$corse[$chiave]['id']."\n");
      $hashcode_score[$input_file]+=$corse[$chiave]['distance'];
      if($time_to_start<=$corse[$chiave]['s']){
          $hashcode_score[$input_file]+=$bonus;
      }
      $auto_x[$v]=$corse[$chiave]['x'];
			$auto_y[$v]=$corse[$chiave]['y'];
			$auto_available[$v] = $time_corsa[$chiave];
			array_push($out[$v], $corse[$chiave]['id']);
			unset($corse[$chiave]);
      $score[$chiave] = -$best_attesa;
    }
  }else{
    print(">>>>>>>ERROR: ".$score[$chiave]." ".$best_attesa."\n");
  }
}

$fo=fopen("$input_file.out","w");
for($v=0; $v < $vehicles; $v++){
	fputs($fo, count($out[$v])." ");
	fputs($fo, implode(" ",$out[$v]));
	fputs($fo, "\n");
}
echo "printed file \n";
fclose($fo);

}
print_r($hashcode_score);

?>
