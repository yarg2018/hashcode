<?php
require_once("2018/utility.php");

$input_files = array('2018/a_example','2018/b_should_be_easy','2018/c_no_hurry','2018/d_metropolis','2018/e_high_bonus');


foreach($input_files as $input_file)
{
    print("== lavoro $input_file\n");
	$file=file($input_file . ".in");
	$n=0;
	$tmp=explode( " ",$file[$n++]);

    $rows=$tmp[0];
    $columns=$tmp[1];
    $vehicles=$tmp[2];
    $rides=$tmp[3];
    $bonus =$tmp[4];
    $steps=$tmp[5];

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

for($v=0; $v<$vehicles; $v++){
	$auto_available[$v]=0;
	$out[$v]=array();
	$auto_x[$v]=0;
	$auto_y[$v]=0;
}


while(array_search(min($auto_available), $auto_available)<$steps && count($corse)>0){
	$time_to_start=0;
	$time_corsa=0;

	$delta=floor($steps*0.01);
	$best_attesa=$steps+$rows+$columns;
	//foreach($corse as $c_key => $c){
	$v=array_search(min($auto_available), $auto_available);
	#print_r($corse);
	#print_r($auto_x);
	foreach(array_keys($corse) as $c_key){
			#print($v." ".$c_key."\n");
			$c = $corse[$c_key];
			#print_r($c);
			$time_to_start=$auto_available[$v]+distanza($auto_x[$v], $auto_y[$v],$c['a'],$c['b']);
			$turni_attesa = $c['s']-$time_to_start;
			if($turni_attesa==0){
				$best_id=$c['id'];
				$chiave=$c_key;
				break;
			}elseif($turni_attesa<$delta){
				$best_id=$c['id'];
				$chiave=$c_key;
				break;
			}elseif(abs($turni_attesa)<$best_attesa){
				$best_attesa=$turni_attesa;
				$best_id=$c['id'];
				$chiave=$c_key;
			}

		}

		$time_to_start=($auto_available[$v] + distanza($auto_x[$v], $auto_y[$v],$corse[$chiave]['a'],$corse[$chiave]['b']));
		$time_corsa = max($time_to_start, $corse[$chiave]['s'])+distanza($corse[$chiave]['a'],$corse[$chiave]['b'],$corse[$chiave]['x'],$corse[$chiave]['y']);
		if($time_corsa<$steps){
			$auto_x=$corse[$chiave]['x'];
			$auto_y=$corse[$chiave]['y'];
			$auto_available[$v] = $time_corsa;
			array_push($out[$v], $corse[$chiave]['id']);

			unset($corse[$chiave]);
		}
}
//}


$fo=fopen("$input_file.out","w");
for($v=0; $v < $vehicles; $v++){
	fputs($fo, count($out[$v])." ");
	fputs($fo, implode(" ",$out[$v]));
	fputs($fo, "\n");
}
echo "printed file \n";
fclose($fo);

}





?>
