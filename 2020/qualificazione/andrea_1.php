<?php


$fname[0]='a_example.txt';
$fname[1]='b_read_on.txt';
$fname[2]='c_incunabula.txt';
$fname[3]='d_tough_choices.txt';
$fname[4]='e_so_many_books.txt';
$fname[5]='f_libraries_of_the_world.txt';

foreach($fname as $fn){
	$p=0;
	$f=str_replace(array("\n","\r"),'',file($fn));
	$l=explode(" ",$f[$p++]);
	$books=$l[0];
	$libraries=$l[1];
	$days=$l[2];

	$scores=explode(" ",$f[$p++]);
	#print_r($scores);
	$library = array();
	$v=0;
	for($i=0;$i<$libraries;$i++){
		$l=explode(" ",$f[$p++]);
		$arraylibri = explode(" ",$f[$p++]);
		$punteggio = (getscore($arraylibri,$scores,$l[2],$l[1])*100000).$v++;
		$library[$punteggio]['idorig'] = $i;
		$library[$punteggio]['books']=$l[0];
		$library[$punteggio]['days']=$l[1];
		$library[$punteggio]['librigiorno']=$l[2];
		$library[$punteggio]['idlibri']=$arraylibri;
		//$library[$punteggio]['libscore']=getscore($library[$i]['idlibri'],$scores,$library[$i]['librigiorno'],$library[$i]['days']);
	}
	$punteggi = array_keys($library);

	rsort($punteggi);
	$stringa_output = array();
	array_push($stringa_output, count($library));
	$previous = array();
	foreach($punteggi as $punt){
		for($ll=0;$ll<count($library[$punt]['idlibri']);$ll++)
		{
			if(in_array($library[$punt]['idlibri'][$ll], $previous))
			{
				unset($library[$punt]['idlibri'][$ll]);
			}
		}
		$library[$p]['idlibri'] = score_libri($library[$punt]['idlibri'],$scores);

		//eliminare i libri che non potremo lavorare
		//$numerolibrilavorabili = count($library[$punt]['idlibri'])-($days - $library[$punt]['days'])*$library[$punt]['librigiorno'];

		$numerolibrinonlavorabili = count($library[$punt]['idlibri'])-($days - $library[$punt]['days'])*$library[$punt]['librigiorno'];
		for($volte=0;$volte<$numerolibrinonlavorabili;$volte++)
			array_pop($library[$punt]['idlibri']);

		//print_r($library[$punt]['idlibri']);
		//array_push($stringa_output, $library[$punt]['idorig']." ".$library[$punt]['books']);
		array_push($stringa_output, $library[$punt]['idorig']." ".count($library[$punt]['idlibri']));
		array_push($stringa_output, join(" ",$library[$punt]['idlibri']));
		array_push($previous, $library[$punt]['idlibri']);
	}
	file_put_contents($fn."_A.out",join("\n",$stringa_output));
}

function score_libri($alibri, $scores){
	$res=array();
	$numlibri = count($alibri);
	$vv=0;
		foreach($alibri as $l){
			$res[$scores[$l]*$numlibri+$vv++] = $l;
		}
		$ordinati = array_keys($res);
		sort($ordinati);
		//array_flip($res);
		$nuovores = array();
		foreach($ordinati as $r)
		{
			array_push($nuovores, $res[$r]);
		}
		//print_r($nuovores);

		return $nuovores;

}

function getscore($alibri,$scores,$librigiorno,$days)
{
	$res = 0;
	foreach($alibri as $l)
	{
	   $res += $scores[$l];
	}
	//$res = $res/$librigiorno-$days;
	$res = max($res*$librigiorno/$days	,0);
	print("[*] SCORE: $res\n");
	return $res;
}


?>
