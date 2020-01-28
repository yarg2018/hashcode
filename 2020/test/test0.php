<?php
//test0
$fname[0]="a_examples";
$fname[1]="b_small";
$fname[2]="c_medium";
$fname[3]="d_quite";
$fname[4]="e_also";

foreach($fname as $fn){
	$f=file($fn.".in");
	$r=explode(" ",$f[0]);
	$numero_fette=$r[0];
	$numero_pizze=$r[1];
	$pizze=explode(" ",$f[1]);
	echo "file=".$fn."\n";
	echo "numero fette ".$numero_fette."\n";
	echo "numero_pizze ".$numero_pizze ."\n";
	print_r($pizze)."\n";
	
	
	
	
	$numero_pizze_scelte=0;
	$pizze_scelte="";
	
	for($i=$numero_pizze-1;$i>=0;$i--){
		if (($numero_fette - $pizze[$i]) >0){
			$pizze_scelte= $i." ".$pizze_scelte;
			$numero_pizze_scelte++;
			$numero_fette-=$pizze[$i];
		}
	}

	file_put_contents($fn.".out",$numero_pizze_scelte."\n".$pizze_scelte."\n");
	echo "\n\n".$numero_pizze_scelte."\n";
	echo $pizze_scelte."\n";
	echo "------------------------------------------------------------------\n";
}
?>
