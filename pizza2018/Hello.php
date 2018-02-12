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

	for ($y=0;$y<$r;$y++){
		for ($x=0;$x<$c;$x++){
			$pizza[$x][$y]=$f[$y+1][$x];
			if($v==0)echo trim($pizza[$x][$y]);
		}
		if($v==0)echo "\n";
	}

	for($y=0;$y<$r;$y++){
		for($x=0;$x<$c;$x++){
			//echo trim($pizza[$x][$y]);
			if(trim($pizza[$x][$y])=='T')$t++;
			if(trim($pizza[$x][$y])=='M')$m++;
		}
		//echo "\n";
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
	
	for($cell=0;$cell<($r*$c);$cell++){
		$x=(int)($cell%$c);
		$y=(int)($cell/$c);
		//echo "x:".$x."\n";
		//echo "y:".$y."\n";
		//echo $pizza[$x][$y]."\n";

		for($k=1;($k<$h+1 && $pizza[$x][$y]!="0");$k++){
			$shift_x=min($c-$x,(int)($h/$k));
			//$shift_y=min($r-$y,$k);
			$shift_y=min($r-$y,(int)($h/$shift_x));
			$m=0;
			$t=0;
			$busy=0;
			//echo "shift_x:".$shift_x."; shift_y:".$shift_y."\n";
			for($s_x=0;$s_x<$shift_x;$s_x++){
				for($s_y=0;$s_y<$shift_y;$s_y++){
					if ($pizza[$x+$s_x][$y+$s_y]=="M")$m++;
					if ($pizza[$x+$s_x][$y+$s_y]=="T")$t++;
					if ($pizza[$x+$s_x][$y+$s_y]=="0")$busy++;
				}
			}
			if ($m>=$l && $t>=$l && $busy==0){	
				//echo $fette."m:".$m."\n";
				//echo $fette."t:".$t."\n";
				$fetta[$fette]=$y." ".$x." ".($y+$shift_y-1)." ".($x+$shift_x-1)."\n";
				$fette++;
				
				for($s_x=0;$s_x<$shift_x;$s_x++){
					for($s_y=0;$s_y<$shift_y;$s_y++){
						$pizza[$x+$s_x][$y+$s_y]="0";
						$pizza[$x+$s_x][$y+$s_y]="0";
					}
				}
				//$cell=$cell+$shift_x+1;
				$cell++;
			}
			else {
				//echo"fetta_non_valida\n";
			}
		}
	}
	
	
	$fo=fopen($nome[$v].".out","w");
	fwrite($fo,$fette."\n");
	for ($i=0;$i<$fette;$i++)
		fwrite($fo,$fetta[$i]);
	fclose($fo);
}
