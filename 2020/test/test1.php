<?php
//test0
$fname[0]="a_example";
$fname[1]="b_small";
$fname[2]="c_medium";
$fname[3]="d_quite_big";
$fname[4]="e_also_big";

foreach($fname as $fn){
	$f=file($fn.".in");
	$r=explode(" ",$f[0]);
	$numero_fette=$r[0];
	$numero_pizze=$r[1];
	$pizze=explode(" ",$f[1]);
	echo "file=".$fn."\n";
	echo "numero fette ".$numero_fette."\n";
	echo "numero_pizze ".$numero_pizze ."\n";
	//print_r($pizze)."\n";
	
	$miglior_numero_pizze_scelte=0;
	$miglior_pizze_scelte='';
	$numero_fette_scoperte=$numero_fette;
	for ($a=0;$a<100; $a++ ){
		$numero_fette=$r[0];
		$numero_pizze=$r[1];
		$numero_pizze_scelte=0;
		$pizze_scelte="";
		for($i= strval($numero_pizze) - 1 - $a; $i>=0;$i--){
			if (($numero_fette - $pizze[$i]) >=0){
				$pizze_scelte= $i." ".$pizze_scelte;
				$numero_pizze_scelte++;
				$numero_fette-=$pizze[$i];
				//echo "$i ";
			}
		}
		//echo "\n result $a $numero_fette\n";
		
		if ($numero_fette<=$numero_fette_scoperte){
			$numero_fette_scoperte=$numero_fette;
			$miglior_numero_pizze_scelte=$numero_pizze_scelte;
			$miglior_pizze_scelte=$pizze_scelte;
		}
	}

	file_put_contents($fn.".out",$miglior_numero_pizze_scelte."\n".$miglior_pizze_scelte."\n");
	echo "\n\n $numero_fette_scoperte\n";
	echo "\n\n".$miglior_numero_pizze_scelte."\n";
	//echo $miglior_pizze_scelte."\n";
	echo "------------------------------------------------------------------\n";
}
?>
