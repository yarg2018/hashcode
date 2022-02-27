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
  $clienti = array();

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
    }
    $d=explode(" ",$f[$i+2]);
    $clienti[$i]['dislike'] = array();
    $clienti[$i]['sorting'] = $clienti[$i]['sorting']+2*$d[0];
    for($j=0; $j<$d[0]; $j++){
      array_push($clienti[$i]['dislike'],$d[$j+1]);
      if(!array_key_exists($d[$j+1], $gusti)){
        $gusti[$d[$j+1]]=-1;
      }else {
          $gusti[$d[$j+1]]--;
      }
    }
  }
  echo "num_clients=".$num_clients."\n";
  echo "num_gusti=".count($gusti)."\n";
  echo "fine lettura file".$file_name."\n";
  return array($clienti, $gusti);
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
  unset($a);
  $output = array_filter($gusti, "filter");
  //print_r($output);
  //$output = array_diff_assoc($gusti, array_column($clienti,'dislike'));
  $score = compute_score(array_keys($output), $clienti);
  echo "result:".$score."\n";

  /*
  * Creo score clienti
  * Meglio se ho pochi like e pochi dislike (posso anche solo sommare gusti o aggravare gusti dislike)
  */
  $sorting = array_column($clienti, 'sorting');
  array_multisort($clienti, SORT_ASC, $sorting);
/*
  Ciclo selezionando i primi x
  Prendo i like unici
  Controllo score
  Controllo se meglio sovrascrivo
  Se like unici sono tutti i gusti break
*/
  //print_r($output);
  $max_time = 5*60;
  $first_likes = array_merge(...array_column($clienti, "like"));
  $first_dislikes = array_merge(...array_column($clienti, "dislike"));
  $best_output = array_diff($first_likes, $first_dislikes);
  //print_r($best_output);
  $best_score = compute_score(array_values($best_output), $clienti);

  foreach($clienti as $i => $cliente){
    $output = array_unique(array_merge($best_output, $cliente["like"]));
    if($output == $best_output)
      continue;
    $score = compute_score(array_values($output), $clienti);
    //print_r(array_flip($output));

    if($score > $best_score){
      $best_score = $score;
      $best_output = $output;
      //echo "result:".$best_score."; cliente:".$i."\n";
    }
    if(count($output)==count($gusti)){
      echo "inseriti tutti i gusti; result:".$best_score."; cliente:".$i."; numero gusti: ".count($best_output)."\n";
      break;
    }
    if(time()-$zero > $max_time){
      echo "tempo esaurito; result:".$best_score."; cliente:".$i."; numero gusti: ".count($best_output)."\n";
      break;
    }

  }


  stampa($file, array_values($best_output), $zero);
  unset($output, $best_output, $clienti, $gusti, $score);
  //array_rand($gusti, rand(1, count($gusti)));

}

?>
