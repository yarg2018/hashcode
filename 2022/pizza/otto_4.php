<?php
/**
  * ripartito da otto1.php
  * aggiunta modalità random di selezione degli ingredienti
  */
chdir("/home/andreo/Documenti/Hashcode/hashcode/2022/qualification/");
echo "folder=".getcwd()."\n";
$fname[0]="a_an_example";

$fname[1]="b_basic";
$fname[2]="c_coarse";
$fname[3]="d_difficult";
$fname[4]="e_elaborate";


function filter($x,$value=0){
  if($x>$value)
    return true;
  else
    return false;
}

function filter2($x,$value=-5){
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
    $clienti[$i]['like'] = array();
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
//  print_r($clienti);
  unset($a);
  $output = array_filter($gusti, "filter");
  //$output = array_diff_assoc($gusti, array_column($clienti,'dislike'));
  $score = compute_score(array_keys($output), $clienti);

  // cerco soluzione miglioare
  // togliendo un elemento
  // aggiungendo un elemento
  // sostituendo un elemento a caso (provo alcune volte)
  // se trovo qualcosa di meglio riprovo altrimenti esco e stampo

  $n_epoche = 50;
  $max_time = 3*60;
  $best_score = $score;
  $best_output = $output;
  for($e=0; $e<$n_epoche; $e++){
    $last_best_score = $best_score;
    if(time()-$zero > $max_time)
      break;
    for($try=0; $try< 3; $try++){
      $missing = array_diff_assoc(array_filter($gusti, "filter2"), $best_output);
      if(!empty($missing)){
        $output_to_check = $best_output;
        array_push($output_to_check, $missing[array_rand($missing, 1)]);
        $score_to_check = compute_score(array_keys($output_to_check), $clienti);
        if ($score_to_check > $best_score){
          $best_output = $output_to_check;
          $best_score = $score_to_check;
        }
      }

      $missing = array_diff_assoc(array_filter($gusti, "filter2"), $best_output);
      if(!empty($missing)){
        $element_to_remove = array_rand($best_output,1);
        $output_to_check = $best_output;
        unset($output_to_check[$element_to_remove]);
        array_push($output_to_check, $missing[array_rand($missing, 1)]);
        $score_to_check = compute_score(array_keys($output_to_check), $clienti);
        if ($score_to_check > $best_score){
          $best_output = $output_to_check;
          $best_score = $score_to_check;
        }
      }
      $element_to_remove = array_rand($best_output,1);
      $output_to_check = $best_output;
      unset($output_to_check[$element_to_remove]);
      $score_to_check = compute_score(array_keys($output_to_check), $clienti);
      if ($score_to_check > $best_score){
        $best_output = $output_to_check;
        $best_score = $score_to_check;
      }
    }
    echo "fine epoca ".$e."; score:".$best_score." tempo:".(time()-$zero).";\n";
    unset($element_to_remove, $output_to_check, $score_to_check);
  }

  stampa($file, array_keys($best_output), $zero);
  unset($output, $clienti, $gusti, $score);
  //array_rand($gusti, rand(1, count($gusti)));

}

?>
