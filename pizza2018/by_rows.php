<?php
$nome[0]="example";
$nome[1]="small";
$nome[2]="medium";
$nome[3]="big";

for ($v=0;$v<4;$v++){
	$f=file("pizza2018/".$nome[$v].".in");
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
		// cycling on cells 1 by 1
		// then I compute the x,y position of the cell
		$x=(int)($cell%$c);
		$y=(int)($cell/$c);

		// if the cell is not busy, it can be a new slice
		// cycle to increase the slice
		for($k=1;($k<$h+1 && $pizza[$x][$y]!="0");$k++){
			// defining the dimension of the slice to consider the biggest possible:
			// when $k=1 ==> the slice is horizontal
			// whne $k > $h/2 ==> the slice is vertical
			// the min is needed to avoid going over the pizza's border
			$shift_x=min($c-$x,(int)($h/$k));
			$shift_y=min($r-$y,(int)($h/$shift_x));
			//echo "shift_x:".$shift_x."; shift_y:".$shift_y."\n";

			$m=0; // counting the number of mushrooms in the cell
			$t=0; // counting the number of tomatos in the cell
			$busy=0; // counting the number of cells already busy (they need to be 0)
			for($s_x=0;$s_x<$shift_x;$s_x++){
				for($s_y=0;$s_y<$shift_y;$s_y++){
					if ($pizza[$x+$s_x][$y+$s_y]=="M")$m++;
					if ($pizza[$x+$s_x][$y+$s_y]=="T")$t++;
					if ($pizza[$x+$s_x][$y+$s_y]=="0")$busy++;
				}
			}

			// Check if the pizza slice is valid
			if ($m>=$l && $t>=$l && $busy==0){
				//echo $fette."m:".$m."\n";
				//echo $fette."t:".$t."\n";
				$fetta[$fette]=$y." ".$x." ".($y+$shift_y-1)." ".($x+$shift_x-1)."\n";
				$fette++;

				// Mark the cells of the valid pizza slice as busy "0"
				for($s_x=0;$s_x<$shift_x;$s_x++){
					for($s_y=0;$s_y<$shift_y;$s_y++){
						$pizza[$x+$s_x][$y+$s_y]="0";
						$pizza[$x+$s_x][$y+$s_y]="0";
					}
				}
				//$cell=$cell+$shift_x+1;
				//$cell++;
			}
			else {
				//echo"fetta_non_valida\n";
			}
		}
	}


	$fo=fopen("pizza2018/".$nome[$v].".out","w");
	fwrite($fo,$fette."\n");
	for ($i=0;$i<$fette;$i++)
		fwrite($fo,$fetta[$i]);
	fclose($fo);
}
