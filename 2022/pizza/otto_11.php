<?php
/**
  * ripartito da otto1.php
  * aggiunta modalità random di selezione degli ingredienti
  * otto 5 aggiungo iter aggiuntivo
  */
chdir("/home/andreo/Documenti/Hashcode/hashcode/2022/qualification/");
echo "folder=".getcwd()."\n";
$fname[0]="a_an_example";

$fname[1]="b_basic";
$fname[2]="c_coarse";
$fname[3]="d_difficult";
$fname[4]="e_elaborate";

/*
*/

function filter($x,$value=-1){
  if($x>$value)
    return true;
  else
    return false;
}

function read_file($file_name){
  echo str_repeat("-",10)."\n";
  echo "file=".$file_name."\n";
  $f=str_replace(array("\n","\r"),'',file("input_data/".$file_name.".in.txt"));
  $d=explode(" ",$f[0]);
  $num_clients = $d[0];
  $gusti = array();
  $gusti_split = array();
  $clienti = array();

  $count_dislike = 0;

  for($i=0; $i<2*$num_clients; $i=$i+2){
    $d=explode(" ",$f[$i+1]);
    $clienti[$i]['id']=$i;
    $clienti[$i]['like'] = array();
    $clienti[$i]['sorting'] = $d[0];
    for($j=0; $j<$d[0]; $j++){
      array_push($clienti[$i]['like'],$d[$j+1]);
      if(!array_key_exists($d[$j+1], $gusti)){
        $gusti[$d[$j+1]]=1;
      }else {
          $gusti[$d[$j+1]]++;
      }
      if(!array_key_exists($d[$j+1], $gusti_split)){
        $gusti_split[$d[$j+1]]['id']=$d[$j+1];
        $gusti_split[$d[$j+1]]['like']=1;
        $gusti_split[$d[$j+1]]['dislike']=0;
      }else{
        $gusti_split[$d[$j+1]]['like']++;
      }

    }
    $d=explode(" ",$f[$i+2]);
    $clienti[$i]['count_dislike'] = $d[0];
    $clienti[$i]['dislike'] = array();
    $clienti[$i]['sorting'] = $clienti[$i]['sorting']+2*$d[0];
    if($d[0] == 0){
      $count_dislike++;
    }
    for($j=0; $j<$d[0]; $j++){
      array_push($clienti[$i]['dislike'],$d[$j+1]);
      if(!array_key_exists($d[$j+1], $gusti)){
        $gusti[$d[$j+1]]=-1;
      }else {
          $gusti[$d[$j+1]]--;
      }

      if(!array_key_exists($d[$j+1], $gusti_split)){
        $gusti_split[$d[$j+1]]['id']=$d[$j+1];
        $gusti_split[$d[$j+1]]['dislike']=1;
        $gusti_split[$d[$j+1]]['like']=0;
      }else{
        $gusti_split[$d[$j+1]]['dislike']++;
      }
    }
  }
  echo "num_clients=".$num_clients."\n";
  echo "num_gusti=".count($gusti)."\n";
  echo "fine lettura file".$file_name."\n";
  return array($clienti, $gusti, $count_dislike, $gusti_split);
}

function compute_score($output, $clienti){
  $score = 0;
  foreach($clienti as $c){
    $like = count($c['like']);
    if($like > count($output))
      continue;
    foreach($output as $ingr){
      if(in_array($ingr, $c['dislike']))
        continue(2); // passo al cliente successivo
      if(in_array($ingr, $c['like'])){
        $like--;
      }
    }
    if($like==0){
      $score++;
    }
  }
  //echo "result:".$score."\n";
  return $score;
}

function stampa($file, $output, $t_zero){
  file_put_contents("output/".$file.".out", count($output)." ".join(" ", $output));
  echo "stampato output con ".count($output)." gusti; tempo impiegato:".(time()-$t_zero).";\n";
  echo"------------\n";
}

foreach($fname as $file){
  $zero = time();
  $a = read_file($file);
  $clienti = $a[0];
  $gusti = $a[1];
  //print_r($clienti);

  $output = array_filter($gusti, "filter");
  //print_r($output);
  //$output = array_diff_assoc($gusti, array_column($clienti,'dislike'));
  $score = compute_score(array_keys($output), $clienti);
  echo "result:".$score."\n";

  //print_r($output);
  $gusti_like = $a[3];
  $gusti_dislike = $a[3];
  unset($a);

  $like_column_gusti = array_column($gusti_like, 'like');
  array_multisort($gusti_like, SORT_DESC, $like_column_gusti);
  $like_column_gusti = array_keys($gusti_like);
  //print_r($like_column_gusti);
  $dislike_column_gusti = array_column($gusti_dislike, 'dislike');
  array_multisort($gusti_dislike, SORT_ASC, $dislike_column_gusti);
  $dislike_column_gusti = array_keys($gusti_dislike);

  $best_output = $output;
  $best_score = $score;
  $max_time = 5*60;
  $num_rimozioni_default = 3;
  $num_aggiunte_default = 3;
  $gusti_rimanenti = array_diff(array_keys($gusti), array_keys($best_output));
  print_r($best_output);
  print_r(array_rand(array_keys($output), $num_rimozioni_default));
  while(time()-$zero < $max_time){
    //break;
    $output = $best_output;
    $copertura = ceil(100*count($gusti_rimanenti)/count($gusti));
    echo "-".count($gusti_rimanenti)."-";
    echo $copertura." ";
    if(rand(0,100) < intval($copertura)){
      echo "<;   ";
      // ho pochi gusti rimanenti, provo a toglierne qualcuno dalla soluzione
      // faccio in modo di averne abbastanza
      $num_rimozioni = min($num_rimozioni_default, count($best_output)-1);
      if($num_rimozioni < 2){
        echo "Non posso più rimuovere\n";
        break;
      }
      foreach(array_rand(array_keys($output), $num_rimozioni) as $k){
          unset($output[$k]);
      }
      $score = compute_score(array_values($output), $clienti);
      if($score > $best_score){
        $best_score = $score;
        $best_output = $output;
        echo "Ho rimosso - result:".$best_score."\n";
      }
    }else{
      echo "<<<  ";
      //ho tanti gusti rimanenti, provo ad aggiungerne uno alla soluzione
      $num_aggiunte = min($num_aggiunte_default, count($gusti_rimanenti));
      if($num_aggiunte < 2){
        echo "Non posso più aggiungere\n";
        break;
      }
      $keys = array_keys($gusti_rimanenti);
      $check = array_rand($keys, $num_aggiunte);
      foreach($check as $k => $value){
          $output[$gusti_rimanenti[$keys[$value]]]=0;
          unset($gusti_rimanenti[$keys[$value]]);
      }
      $score = compute_score(array_values($output), $clienti);
      if($score > $best_score){
        $best_score = $score;
        $best_output = $output;
        echo "Ho aggiunto - result:".$best_score."\n";
      }
    }
  }

  echo "fine; result:".$best_score."; numero gusti: ".count($best_output)."\n";
  stampa($file, array_values($best_output), $zero);
  unset($output, $best_output, $clienti, $gusti, $score);
  //array_rand($gusti, rand(1, count($gusti)));

}

?>
