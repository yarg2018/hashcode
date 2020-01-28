#!/usr/bin/php
<?php
$nome[0]="example";
$nome[1]="small";
$nome[2]="medium";
$nome[3]="big";
for ($v=0;$v<4;$v++){
	$f=file($nome[$v].".in");
	$pizza = array();
	$dati=explode(" ",$f[0]);
	$r=$dati[0];
	$c=$dati[1];
	$l=$dati[2];
	$h=trim($dati[3]);
	$m=0;
	$t=0;

	for ($y=0;$y<$r+1;$y++){
		for ($x=0;$x<$c;$x++){
			$pizza[$x][$y]=$f[$y][$x];
		}
	}

	for($y=0;$y<$r;$y++){
		for($x=0;$x<$c;$x++){
			echo trim($pizza[$x][$y]);
			if(trim($pizza[$x][$y])=='T')$t++;
			if(trim($pizza[$x][$y])=='M')$m++;
		}
		echo "\n";
	}

	echo "r:".$r."\n";
	echo "c:".$c."\n";
	echo "l:".$l."\n";
	echo "h:".$h."\n";
	echo "t:".$t."\n";
	echo "m:".$m."\n";

	$s=0;
	$m=0;
	$t=0;
	$fette=0;
	$fetta=array();

	for($y=0;$y<$r;$y++){
		while(($s+$h)<=$c){
			$m=0;
			$t=0;
			for($x=$s;$x<($s+$h-1);$x++){	
				if ($pizza[$x][$y]=="M")$m++;
				if ($pizza[$x][$y]=="T")$t++;
			}
			echo $y."m:".$m."\n";
			echo $y."t:".$t."\n";
			if ($m>=$l && $t>=$l){	
				$fetta[$fette]=$y." ".$s." ".$y." ".($s+$h-1)."\n";
				$fette++;
				$s=$s+$h+1;
			}
			else {
				echo".\n";
				$s++;
			}
		}
		$s=0;
	}
	$fo=fopen($nome[$v].".out","w");
	fwrite($fo,$fette."\n");
	for ($i=0;$i<$fette;$i++)
		fwrite($fo,$fetta[$i]);
	fclose($fo);
}


?>
