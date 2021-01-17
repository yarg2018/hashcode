<?php

function cmp(array $a, array $b){
    return -$a['conta'] <=> -$b['conta'];
}

function cmp2(array $a, array $b){
    return -$a['n_gusti'] <=> -$b['n_gusti'];
}

//ini_set('memory_limit', '2G');
echo "folder=".getcwd()."\n";
chdir("/home/andreo/Documenti/Hashcode/hashcode/2021/test");
echo "folder=".getcwd()."\n";

$fname[0]="a_example";
$fname[1]="b_little_bit_of_everything";
///*
$fname[2]="c_many_ingredients";
$fname[3]="d_many_pizzas";
$fname[4]="e_many_teams";
/*
*/

foreach($fname as $filename){
  echo str_repeat("-",10)."\n";
  echo "file=".$filename."\n";
	//$f=file($filename.".in");
	$f=str_replace(array("\n","\r"),'',file($filename.".in"));
  $d=explode(" ",$f[0]);

	$npizze=$d[0];
	$squadre2=$d[1];
	$squadre3=$d[2];
	$squadre4=$d[3];

	echo "npizze=".$npizze."\n";

	echo "squadre2=".$squadre2."\n";
	echo "squadre3=".$squadre3."\n";
	echo "squadre4=".$squadre4."\n";
  echo "pizze_necessarie=".(4*$squadre4+3*$squadre3+2*$squadre2)."\n";

  $pizza=array();
  $gusti=array();
  $ngusti=0;

  for($i=0;$i<$npizze;$i++){
		$d=explode(" ",$f[$i+1]);
    $pizza[$i]['identificativo']=$i;
    $pizza[$i]['n_gusti']=$d[0];
    for($j=0; $j<$d[0]; $j++){
      //$pizza[$i]['gusti'][$j]=$d[$j+1];
      $pizza[$i][$d[$j+1]]=1;
      if(array_key_exists($d[$j+1], $gusti)){
        $gusti[$d[$j+1]]['identificativo']=$d[$j+1];
        $gusti[$d[$j+1]]['conta'] = $gusti[$d[$j+1]]['conta'] + 1;
        array_push($gusti[$d[$j+1]]['pizze'], $i);
      }else{
        $gusti[$d[$j+1]]['identificativo']=$d[$j+1];
        $gusti[$d[$j+1]]['conta'] = 1;
        $gusti[$d[$j+1]]['pizze'] = array();
        array_push($gusti[$d[$j+1]]['pizze'], $i);
      }
    }
	}
  if($filename=="a_example"){
    print_r($pizza);
    //print_r($gusti);
    //echo "numero gusti=".count($gusti)."\n";
  }

  $counta_id=0;
  foreach($pizza as $k => $pz){
    if($pz['identificativo']==1){
      $counta_id=$counta_id+1;
    }
  }
  echo "conta_id=".$counta_id."\n";

  echo "sorting...";
  //uasort($gusti, 'cmp');
  //uasort($pizza, 'cmp2');
  ///*
  $n_gusti = array();
  foreach($pizza as $key => $pz){
    $n_gusti[$key] = $pz['n_gusti'];
  }
  array_multisort($n_gusti, SORT_DESC, $pizza);
  //*/
  echo "OK;\n";
  //if(!in_array($d[$j+1], $gusti)){
  //  array_push($gusti, $d[$j+1]);
  //}
  if($filename=="a_example"){
    print_r($pizza);
    //print_r($gusti);
    //echo "numero gusti=".count($gusti)."\n";
  }

  $counta_id=0;
  foreach($pizza as $k => $pz){
    if($pz['identificativo']==1){
      $counta_id=$counta_id+1;
    }
  }
  echo "conta_id=".$counta_id."\n";

  //print(array_sum(array_column($gusti, 'conta')));
  //print_r(array_keys($gusti));
  //$lista_gusti = array_keys($gusti);

  echo "inizio calcoli...\n";
  $result = array();
    //$result_gusti = array();
  $max_ordini = $squadre4+$squadre3+$squadre2;
  foreach($pizza as $k => $pz){
    //echo $pz['identificativo']." ";
    for($i=0; $i<$max_ordini; $i++){
      if(!isset($result[$i]['pizze'])){
        $result[$i]['pizze']=array();
        //$result[$i]['gusti_mancanti']=$lista_gusti;
      };
      $elementi_ordine = 0;
      if($i < $squadre4){
        $elementi_ordine = 4;
      }elseif($i < ($squadre4+$squadre3)){
        $elementi_ordine = 3;
      }else{
        $elementi_ordine = 2;
      };
      if(count($result[$i]['pizze']) < $elementi_ordine ){
        // aggiungi la pizza alla consegna
        // elimino la pizza
        //if(count($result[$i]['gusti_mancanti']) > 0 ){
            //si potrebbe prendere l'ultima pizza per non sprecare gusti
        //}
        //if($filename=="c_many_ingredients")
          //echo "k=".$k." i=".$i." pz[id]=".$pz['identificativo']."\n";
        array_push($result[$i]['pizze'], $pz['identificativo']);
        //$result[$i]['gusti_mancanti'] = array_diff($result[$i]['gusti_mancanti'], array_keys($pz));
        //array_push($result[$i]['gusti_mancanti'], $temp);

        unset($pizza[$k]);
        break;
      } // altrimenti passo alla consegna successiva
    }

  }
  echo "result...".count($result)."\n";

  echo "stampo...";
  //print_r($result);
  $stringa_output[0] = count($result);
  foreach($result as $line){
    if(count($line['pizze'])<2){
      $stringa_output[0] = $stringa_output[0] - 1;
    }else{
      array_push($stringa_output, count($line['pizze'])." ".join(" ",$line['pizze']));
    }
  }
  file_put_contents($filename.".out", join("\n", $stringa_output));
  unset($stringa_output);
  unset($result);
  unset($pizza);
	echo "ok\n----------------";
}

?>
