#!/usr/bin/php
<?php

ini_set('memory_limit', '256M'); 	/* *
									 * Risolve il problema di memoria per kittens 
									 *(credo il problema fosse nell'import 
									 * della riga dei pesi perché troppo lunga)
									 * */

$files = array('me_at_the_zoo', 'trending_today', 'videos_worth_spreading', 'kittens');

foreach($files as $fname){
	//data import
	$inputfile=$fname.".in";
	$riga=0;
	$f=file($inputfile);
	$tmp=str_replace(array("\n","\r"), "",explode(" ",$f[$riga++]));	/* dovendo usare il pc del lavoro ho dovuto cambiare
																		 * tutti gli explode usando la funzione str_replace
																		 * come avevo fatto per il problema del taglio della pizza 
																		 * */
	$V= $tmp[0];
	$E=$tmp[1];
	$R=$tmp[2];
	$C=$tmp[3];
	$X=$tmp[4];
	echo "$V $E $R $C $X\n";

	$S=str_replace(array("\n","\r"), "",explode(" ",$f[$riga++]));

	$endpoint_latency=array();
	$endpoint_ncache=array();
	$endpoint_cache=array();
	$endpoint = array();

	for($i=0; $i<$E;$i++){
		$tmp=str_replace(array("\n","\r"), "",explode(" ",$f[$riga++]));
		$endpoint_latency[$i]=$tmp[0];
		$endpoint_ncache[$i]=$tmp[1];
		$endpoint_cache[$i]=array();	
		for($i1=0;$i1<$endpoint_ncache[$i];$i1++){
			$tmp1=str_replace(array("\n","\r"), "",explode(" ",$f[$riga++]));
			$endpoint[$i][$tmp1[0]]=$tmp1[1];
		}
		if(isset($endpoint[$i]))
			foreach($endpoint[$i] as $key => $val)
				($i." ".$key." ".$val);
	}

	$Rv = array();
	$Re = array();
	$Rn = array();
	
	for($i=0;$i<$R;$i++){
		$tmp=str_replace(array("\n","\r"), "",explode(" ",$f[$riga++]));
		$Rv[$i]=$tmp[0];
		$Re[$i]=$tmp[1];
		$Rn[$i]=$tmp[2];
		
	}	

	/* Prima di fare il sort costruisco uno score 
	 * che tenga conto sia del numero di richieste che del
	 * guadagno di latency (per il miglior cache server
	 * */
	$Rsort=array();
	for($r=0;$r<$R;$r++){
		$endpoint_id=$Re[$r];
		$Rsort[$r]=0;
		if(!isset($endpoint[$endpoint_id]))
			continue;
		foreach($endpoint[$endpoint_id] as $epKey => $epVal){
			$temp_score=$Rn[$r]*($endpoint_latency[$endpoint_id]-$epVal);
			if($temp_score>$Rsort[$r])
				$Rsort[$r]=$temp_score;
		}
	}
	// faccio il sort rispetto allo score costruito
	array_multisort($Rsort, SORT_DESC, $Rn, SORT_DESC, $Rv, $Re);	

	$output=array();

	for($c=0; $c<$C; $c++){
		$cacheServerSize[$c]=$X;
	}

	for($r=0; $r<$R; $r++){ //inizio ciclo richieste
		$size_v=$S[$Rv[$r]];
		$endpoint_id=$Re[$r];
		$temp_latenza=$endpoint_latency[$endpoint_id];
		$best_cache=-1;
		// ciclo su cache server
		if(!isset($endpoint[$endpoint_id]))
			continue;
		foreach($endpoint[$endpoint_id] as $epKey => $epVal)
		{
			if($epVal<$temp_latenza){ // ho inverito le condizioni (controllo prima se c'è un guadagno in latenza prima di verificare che ci stia nel cacheserver
				if($size_v < $cacheServerSize[$epKey] || in_array($Rv[$r],$output[$epKey])){ // oltre a verificare lo spazio controllo che il video in questione non ci sia già
					$best_cache=$epKey;
					$temp_latenza=$epVal;
				}
			}
		}
		if($best_cache!=-1){
			if(!isset($output[$best_cache]))
				$output[$best_cache]=array();
			if(!in_array($Rv[$r],$output[$best_cache])){
				array_push($output[$best_cache], $Rv[$r]);
				$cacheServerSize[$best_cache]-=$size_v; //ho spostato dentro all'if l'aggiornamento della size per evitare di scalare il peso dello stesso video 2 volte
			}
		}
	}//fine ciclo richieste

	$fo=fopen($fname.".out","w");

	fputs($fo, count($output)."\n");
	foreach($output as $key => $valArray)
	{
		fputs($fo, $key . " " . implode(" ", $valArray)."\n");
	}
	echo "printed file ". $fname."\n";
	fclose($fo);

}
?>
