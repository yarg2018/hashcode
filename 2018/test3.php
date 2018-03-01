<?php
require_once("2018/utility.php");
$file=file("2018/test.in");
$n=0;
$tmp=explode( " ",$file[$n++]);

$rows=$tmp[0];
$columns=$tmp[1];
$vehicles=$tmp[2];
$rides=$tmp[3];
$bonus =$tmp[4];
$steps=$tmp[5];

$corse = array();

for ($i=0; $i< $rides; $i++){
	$ride = array('a'=>0,'b'=>0,'x'=>0,'y'=>0,'s'=>0,'f'=>0,'distance'=>0);
	list($ride['a'],$ride['b'],$ride['x'],$ride['y'],$ride['s'],$ride['f']) = explode(" ",$file[$n++]);
	$ride['distance'] = distanza($ride['a'],$ride['b'],$ride['x'],$ride['y']);
	array_push($corse, $ride);
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


while(array_search(min($auto_available), $auto_available)<$steps && count($corse>0)){
	$time_to_start=0;
	$time_corsa=0;

	$delta=floor($steps*0.1);
	$best_attesa=$steps+$rows+$columns;
	//foreach($corse as $c_key => $c){
	$v=array_search(min($auto_available), $auto_available);
	foreach($corse as $c_key => $c){
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
				break;
			}
		}

		$time_to_start=$auto_available[$v]+distanza($auto_x[$v], $auto_y[$v],$corse[$chiave]['a'],$corse[$chiave]['b']);
		$time_corsa = max($time_to_start, $corse[$chiave]['s'])+distanza($corse[$chiave]['a'],$corse[$chiave]['b'],$corse[$chiave]['x'],$corse[$chiave]['y']);
		if($time_corsa<$steps){
			$auto_x=$corse[$chiave]['x'];
			$auto_y=$corse[$chiave]['y'];
			$auto_available[$v] += $time_corsa;
			array_push($out[$v], $corse[$chiave]['id']);
			unset($corse, $chiave);
		}else{
			$auto_available[$v]=$steps+1;
		}
}
//}


$fo=fopen("2018/test.out","w");
for($v=0; $v < $vehicles; $v++){
	fputs($fo, count($out[$v])." ");
	foreach($out[$v] as $key => $value){
		fputs($fo, $value);
	}
	fputs($fo, "\n");
}
echo "printed file \n";
fclose($fo);







?>
