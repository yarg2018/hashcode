<?php

ini_set('memory_limit', '256M'); //era servito per caricare un file molto grosso

function print_out_results($file, $vettore_pizze){
	/* funzione per stampare:
	 *  - i risultati su file .out
	 *  - summary sul terminale
	 */
	$numero_pizze = count($vettore_pizze); // conto le pizze scelte
	$stringa_pizze = ""; // inizializzo la stringa dei risultati
	foreach($vettore_pizze as $key => $value){ // listo sull'array dei risultati
		$stringa_pizze = $key." ".$stringa_pizze; // e aggiungo alla stringa
	};
	file_put_contents($file."_A.out",$numero_pizze."\n".$stringa_pizze); // butto tutto sul file
	echo "Score:".number_format(array_sum($vettore_pizze))."\n"; // e riporto il risultato a terminale
		//echo $miglior_pizze_scelte."\n";
	echo "------------------------------------------------------------------\n";
};

// lista dei file di input
$fname[0]="a_example";
$fname[1]="b_small";
$fname[2]="c_medium";
$fname[3]="d_quite_big";
$fname[4]="e_also_big";
//echo __DIR__;
// eseguo su ogni file
foreach($fname as $fn){
	// importo il file
	$f=str_replace(array("\n","\r"),"",file(__DIR__."/".$fn.".in")); //mi serve str_replace per evitare rotture con i return del file di input
	$r=explode(" ",$f[0]); //esplodo la prima riga del file di input letto
	$target =$r[0]; // il primo valore numerico è l'obiettivo
	$numero_pizze=$r[1]; // il secondo valore di input è il numero di pizze (elementi della seconda riga)
	echo $numero_pizze;
	$pizze=explode(" ",$f[1]); // metto le pizze in array
	echo "file=".$fn.".in"."\n";
	echo "Target: ".number_format($target)."\n";
	echo "numero_pizze: ".$numero_pizze.";\n";
	//print_r($pizze)."\n";
	//----------------- fine import

	// risolvo subito il caso facile in cui posso prendere tutte le $pizze - UPDATE: inutile, non serve!!!
	if (array_sum($pizze) <= $target){
		print_out_results($fn, $pizze);
		echo "countinue";
		continue;
	};

	/*
	 * Voglio ciclare sulle pizze (dal valore più alto)
	 * e tenerle tutte fino a che riesco
	 * Le salvo in un array "usate";
	 * Butto le altre in un array "non usate" --> vedo dopo cosa farne
	 */
	$usate = array(); // definisco vettore pizze usate
	$non_usate = array(); // definisco vettore pizze non usate
	echo "\n";
	$current_sum = 0; // inizializzo somma fette (mi servirà per controllare di non superare il target)
	for($i = $numero_pizze-1; $i >= 0; $i--){ // ciclo dalla più grande per avvicinarmi il più possibile all'obiettivo
		$valore_i = $pizze[$i]; //salvo il valore perché lo uso spesso
		if($current_sum + $valore_i < $target){
			//se sono sotto target tengo la pizza in "usate" e aggiorno la somma
			$current_sum += $valore_i;
			$usate[$i] = $valore_i;
			unset($pizze[$i]); // tolgo anche la fetta da quelle da analizzare (forse inutile)
		}elseif (($current_sum + $pizze[$i] ) == $target) {
			// se ho raggiunto il target tengo la pizza in "usate",
			// aggiorno la somma ed esco: meglio di così non posso fare!
			$current_sum += $valore_i;
			$usate[$i] = $valore_i;
			unset($pizze[$i]);
			break;
		}else {
			// se sono sopra target metto la pizza in "non_usate"
			$non_usate[$i] = $valore_i;
			unset($pizze[$i]);
		};
	};

	$goal = $target - $current_sum; // vedo quanto manca al target
	// Stampo un po' di roba
	echo "Score after cycle: ".number_format($current_sum)."\n";
	echo "Missing: ".number_format($goal)."\n";
	echo "Numero di non_usate: ".count($non_usate)."\n";
	echo "Valore di non_usate: ".array_sum($non_usate)."\n";
	echo "Max di non_usate: ".max($non_usate)."\n";
	echo "Min di usate: ".min($usate)."\n";

	/*
	 * se ho raggiunto il target vado diretto in stampa dei risultati
	 * altrimenti provo a togliere una fetta usata e a sostituirla
	 * con una o più di quelle più piccole
	 */
	if(array_sum($usate) != $target){
		// ciclo sulle "usate"
		foreach ($usate as $key_usate => $value_usate) {
			// aggiorno la variabile goal con il valore di quanto mi mancherebbe
			// se togliessi questa fetta
			$goal = $target - ($current_sum - $value_usate);
			$temp = array(); // definisco array per archiviare le fette da usare in sostituzione
			/*
			 * ciclo sulle "non usate" per cercare con cosa sostiture quella tolta
			 * riempo con la prima e poi provo a colmare... qui forse potrei fare meglio (cercare l'ottimo)
			 */
			foreach($non_usate as $key => $value){
				if($value <= $goal){
					// se ne trovo una che potrebbe starci la archivio e ricalcolo l'obiettivo
					$temp[$key] = $value;
	//				echo "goal: ".$goal."; temp value:".$value."\n";
					$goal = $goal - $value;
				};
			};//continuo su quelle non usate per vedere se posso fare meglio di prima

			// ho una proposta di sostituzione: controllo se è effettivamente meglio
			if(array_sum($temp)>$value_usate){
				// se è ok, faccio la sostituzione:
				// toglo quella che c'era da usate e la metto in "non usate"
				// togliendo anche il suo valore
				unset($usate[$key_usate]);
				$non_usate[$key_usate] = $value_usate;
				$current_sum -= $value_usate;
				// aggiungo quelle che ho trovato ad usate e le tolgo da "non usate"
				// aggiornando anche il valore della somma
				foreach($temp as $key_temp => $value_temp){
					unset($non_usate[$key_temp]);
					$usate[$key_temp] = $value_temp;
					$current_sum += $value_temp;
				}
			};
		};
	};
	print_out_results($fn, $usate);

	};

//echo __DIR__."\\".$fn.".in";
?>
