<?php

function get_score(array $res, int $num){
  $sum = 0;
  foreach($res as $r){
    $sum = $sum + pow($num - count($r['gusti_mancanti']), 2);
  }
  unset($r);
  return $sum;
}

function get_score_local(array $r, array $pizza){
  $array_gusti = array();
  foreach($r['pizze'] as $p)
    $array_gusti = array_merge($array_gusti, array_keys($pizza[$p]));
  $array_gusti = array_unique($array_gusti);
  return pow(count($array_gusti)-2, 2);
}

function get_score_full(array $res, array $pizza){
  $sum = 0;
  foreach($res as $r){
    $sum = $sum + get_score_local($r,$pizza);
  }
  return $sum;
}

//ini_set('memory_limit', '2G');
chdir("/home/andreo/Documenti/Hashcode/hashcode/2021/test");
echo "folder=".getcwd()."\n";
/*
$fname[0]="a_example";
$fname[1]="b_little_bit_of_everything";

$fname[2]="c_many_ingredients";

$fname[3]="d_many_pizzas";
*/
$fname[4]="e_many_teams";
/*
*/

foreach($fname as $filename){
  $zero=time();
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
  echo "ordini totale=".($squadre4+$squadre3+$squadre2)."\n";
  echo "pizze_necessarie=".(4*$squadre4+3*$squadre3+2*$squadre2)."\n";

  $pizza=array();
  $gusti=array();
  $ngusti=0;

  //raccolgo info delle pizze e lista dei gusti
  for($i=0;$i<$npizze;$i++){
		$d=explode(" ",$f[$i+1]);
    $pizza[$i]['identificativo']=$i;
    $pizza[$i]['n_gusti']=$d[0];
    for($j=0; $j<$d[0]; $j++){
      //$pizza[$i]['gusti'][$j]=$d[$j+1];
      $pizza[$i][$d[$j+1]]=1;
      if(!array_key_exists($d[$j+1], $gusti)){
        $gusti[$d[$j+1]] = 1;
      }else{
        $gusti[$d[$j+1]] = $gusti[$d[$j+1]] + 1;
      }
    }
	}
  $lista_gusti = array_keys($gusti);
  $ngusti = count($lista_gusti);
  //stampo un po' di statistiche
  echo "totale gusti: ".count($lista_gusti)."\n";
  echo "n_gusti medio: ".array_sum(array_column($pizza, 'n_gusti'))/$npizze."\n";
  echo "n_gusti min: ".min(array_column($pizza, 'n_gusti'))."\n";
  echo "n_gusti max: ".max(array_column($pizza, 'n_gusti'))."\n";

  //ordino le pizze in base al numero di gusti
  echo "sorting...";
  $score_pz = array();
  foreach($pizza as $key => $pz){
    $score_pz[$key] = 0;
    foreach($pz as $p => $g){
      if($p=='identificativo')
        continue;
      if($p=='n_gusti')
        continue;
      $score_pz[$key] += $gusti[$p];
    }
  }
  unset($key);
  unset($pz);
  unset($p);
  unset($a);
//array_multisort(array_column($pizza, 'n_gusti'), SORT_DESC, $pizza);
  array_multisort($score_pz, SORT_DESC, $pizza);
  echo "OK;".(time()-$zero).";\n";

  echo "inizio calcoli...\n";
  $result = array(); //storicizzo le pizze selezionate
  $salta_indice=array(); //e quelle che non devo più considerare

  $max_ordini = $squadre4+$squadre3+$squadre2; //non posso superare questo numero di ordini

  // seleziono una pizza (iniziando da quelle con più gusti)
  foreach($pizza as $k => $pz){
    if(in_array($k, $salta_indice))
      continue;
    // prendo un ordine
    for($i=0; $i<$max_ordini; $i++){
      //se nuovo, inizializzo
      if(!isset($result[$i]['pizze'])){
        $result[$i]['pizze']=array();
        $result[$i]['gusti_mancanti']=$lista_gusti;
        $result[$i]['elementi_ordine'] = 2;
        if($i < $squadre4){
          $result[$i]['elementi_ordine'] = 4;
        }elseif($i < ($squadre4+$squadre3)){
          $result[$i]['elementi_ordine'] = 3;
        }else{
          $result[$i]['elementi_ordine'] = 2;
        };
      };
      // se ho spazio riempio l'ordine
      if(count($result[$i]['pizze']) < $result[$i]['elementi_ordine'] ){
        // se sono già al massimo dei punti prendo una pizza con pochi ingrdienti
        // --> migliorabile (dovrei prendere quelle pizze senza ingredienti "rari")
        if(count($result[$i]['gusti_mancanti']) == 0 ){
          $ultima_pizza = array_key_last(array_diff(array_keys($pizza),$salta_indice));
          // aggiungo la pizza alla consegna
          // segno la pizza come già lavorata
          array_push($result[$i]['pizze'], $pizza[$ultima_pizza]['identificativo']);
          // non mi serve aggiornare lista di ingredienti se già non ne mancavano
          //$result[$i]['gusti_mancanti'] = array_diff($result[$i]['gusti_mancanti'], array_keys($pizza[$ultima_pizza]));
          array_push($salta_indice, $ultima_pizza);
        }else{
          // non ho completato l'ordine e mi mancano dei gusti
          // dovrei cercare la pizza migliore
          // per ora uso quella che ho nel foreach
          //
          // aggiungo la pizza alla consegna
          // aggiorno gusti mancanti
          // segno la pizza come già lavorata
          array_push($result[$i]['pizze'], $pz['identificativo']);
          $result[$i]['gusti_mancanti'] = array_diff($result[$i]['gusti_mancanti'], array_keys($pz));
          array_push($salta_indice, $k);
          break;
        }
      } // altrimenti passo alla consegna successiva
    }
    //  passo alla pizza successiva e cerco l'ordine in cui metterla
  }
  unset($k);
  unset($pz);

  echo "OK;".(time()-$zero).";\n";
  // ci sono due casi:
  // -> ho finito le pizze posso cambiare composizione ordini per vedere se miglioro
  //    (pescando da quelli da 2, completo quelli da 4 con gusti mancanti; poi quelli da 3; poi quelli da due)
  // -> non ho finito le pizze ma ho finito gli ordini
  //    (vedo se cambiando qualche pizza negli ordini posso miglioare) --> a tempo
  echo "ottimizzo...";

  array_multisort(array_column($pizza,'identificativo'), SORT_ASC, $pizza);
  $usati = array();
  foreach($result as $r => $res){
    if(count($res['pizze']) == 1){
      unset($result[$r]);
    }
    foreach($res['pizze'] as $pluto)
    array_push($usati, $pluto);
  }
  unset($r);
  unset($res);
  $riordino = array_diff(array_column($pizza,'identificativo'), $usati);

  $risultati = array(0,0,0,0,0);
  foreach($result as $r => $res2){
    $n_pizze_ordine = count($res2['pizze']);
    $risultati[$n_pizze_ordine] = $risultati[$n_pizze_ordine] + 1;
      //recupero pizze sprecate (che non contribuiscono a punteggio)
  }
  unset($r);
  unset($res2);
  foreach($result as $r => $res2){
    if(count($res2['pizze'])==4)
      continue;
    if(((count($res2['pizze'])==3) && ($risultati[4] < $squadre4)) ||
        ((count($res2['pizze'])==2) && ($risultati[3] < $squadre3))){
      $result_best = null;
      $score_best = get_score_local($res2,$pizza);
      foreach($riordino as $aggiunta){
        array_push($res2['pizze'], $aggiunta);
        $temp_score = get_score_local($res2,$pizza);
        if($temp_score > $score_best){
          $score_best = $temp_score;
          $result_best = $aggiunta;
        }
        array_pop($res2['pizze']);
      }
      if(!is_null($result_best))
        array_push($result[$r]['pizze'], $pizza[$aggiunta]['identificativo']);
    }
  }
  unset($r);
  unset($res2);

  $risultati = array(0,0,0,0,0);
  foreach($result as $r => $res2){
    $n_pizze_ordine = count($res2['pizze']);
    $risultati[$n_pizze_ordine] = $risultati[$n_pizze_ordine] + 1;
      //recupero pizze sprecate (che non contribuiscono a punteggio)
  }
  while($risultati[4] > $squadre4){
    $rand_key = array_rand($result, 1);
    if(count($res2['pizze'])!=4)
      continue;
    $a = array_rand($result[$rand_key]['pizze'], 1);
    unset($result[$rand_key]['pizze'][$a]);
    $risultati[3] = $risultati[3] + 1;
    $risultati[4] = $risultati[4] - 1;
  }
  while($risultati[3] > $squadre3){
    $rand_key = array_rand($result, 1);
    if(count($res2['pizze'])!=3)
      continue;
    $a = array_rand($result[$rand_key]['pizze'], 1);
    unset($result[$rand_key]['pizze'][$a]);
    $risultati[2] = $risultati[2] + 1;
    $risultati[3] = $risultati[3] - 1;
  }
  while($risultati[2] > $squadre2){
    $rand_key = array_rand($result, 1);
    if(count($res2['pizze'])!=2)
      continue;
    unset($result[$rand_key]);
    $risultati[2] = $risultati[2] - 1;
  }

  unset($salta_indice);

  array_multisort(array_column($pizza,'identificativo'), SORT_ASC, $pizza);

  $result_best = $result;
  $score_best = get_score_full($result,$pizza);
  foreach($result as $r => $res2){
    //unset($result[$r]['gusti_mancanti']); //inutile mantenerlo se continuo a cambiare pizze
    $result[$r]['score']=get_score_local($res2,$pizza);
  }
  array_multisort(array_column($result,'score'), SORT_DESC, $result);

  $generation = 0;
  echo "\nGeneration ".$generation." completed: ".get_score_full($result, $pizza);
  $last_cycle = time() + (10 * 60); // gira per 5 minuti
  while(time() < $last_cycle){
    // faccio evolvere la soluzione
    $generation++;
/*    foreach($result as $r => $res2){
      if($res2['score'] >= (0.5 + 1/($generation+1)) * pow($ngusti,2))
        continue;
        */
    $perc_change = ceil(count($result)/5);
    if($perc_change % 2 != 0)
      $perc_change = $perc_change-1;
    if($perc_change < 1)
      break;

    $rand_keys = array_rand($result, $perc_change);
    for($i=0; $i<count($rand_keys); $i=$i+2){

      $local_score = get_score_local($result[$rand_keys[$i]],$pizza)+get_score_local($result[$rand_keys[$i+1]],$pizza);

      $a = array_rand($result[$rand_keys[$i]]['pizze'], 1);
      $b = array_rand($result[$rand_keys[$i+1]]['pizze'], 1);

      $temp_result = $result;
      $temp_result[$rand_keys[$i]]['pizze'][$a] = $result[$rand_keys[$i+1]]['pizze'][$b];
      $temp_result[$rand_keys[$i+1]]['pizze'][$b] = $result[$rand_keys[$i]]['pizze'][$a];

      $temp_score = get_score_local($temp_result[$rand_keys[$i]],$pizza)+get_score_local($temp_result[$rand_keys[$i+1]],$pizza);

      if($temp_score > $local_score){
        $result = $temp_result;
      }
    }
    if(($generation % 500) == 0)
      echo "\nGeneration ".$generation." completed: ".get_score_full($result, $pizza);
  }
  echo "\nGeneration ".$generation." completed: ".get_score_full($result, $pizza);


  echo "\nottimizzo...OK;".(time()-$zero).";\n";
  echo "risultato attuale circa: ".get_score($result,$ngusti).", ".get_score_full($result,$pizza)."\n";
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
  echo "OK;".(time()-$zero).";\n";
  echo "result...".$stringa_output[0]."\n";
  echo"------------\n";
  unset($stringa_output);
  unset($result);
  unset($pizza);
}

?>
