<?php
/**
  * Tentativo di algoritmo generico che non converge
  *
  **/
chdir("/home/andreo/Documenti/Hashcode/hashcode/2022/qualification/");
echo "folder=".getcwd()."\n";
$fname[0]="a_an_example";
$fname[1]="b_basic";
$fname[2]="c_coarse";
$fname[3]="d_difficult";
$fname[4]="e_elaborate";


function filter($x){
  if($x>0)
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
  return $score;
}

function stampa($file, $output, $t_zero){
  file_put_contents("output/".$file.".out", count($output)." ".join(" ", $output));
  echo "OK;".(time()-$t_zero).";\n";
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

  $score = compute_score(array_keys($output), $clienti);

  $n_individui = 50;
  $n_epoche = 15;
  $n_crossover = 0.8;
  $popolazione = array();
  $prob_array = array();
  $popolazione[0]['output'] = $output;
  $popolazione[0]['score'] = $score;
  $min_val = min(array_values($gusti));
  foreach($gusti as $gusto => $likes)
    $prob_array= array_pad($prob_array, count($prob_array)+$likes-$min_val, $gusto);
  for($i=1; $i<$n_individui; $i++){
    $selection = array_rand($prob_array,rand(2,count($prob_array)));
    $popolazione[$i]['output']=array();
    foreach($selection as $s){
      //print($prob_array[$s]);
      //print("\n");
      array_push($popolazione[$i]['output'], $prob_array[$s]);
    }
    $popolazione[$i]['output'] = array_unique($popolazione[$i]['output']);
    print_r($popolazione[$i]['output']);
    $popolazione[$i]['score'] = compute_score($popolazione[$i]['output'], $clienti);
    $prob_array = array_pad($prob_array, count($prob_array)+$popolazione[$i]['score'], $i);
  }
  $best_score = max(array_column($popolazione,'score'));

  for($e=0; $e<$n_epoche; $e++){
    $new_prob_array = array();
    $nuova_popolazione = array();
    for($i=0;$i<$n_individui; $i=$i+2){
      $individuo_1 = $prob_array[array_rand($prob_array,1)];
      $individuo_2 = $prob_array[array_rand($prob_array,1)];
      $crossover = ceil(count($popolazione[$individuo_1]['output'])*$n_crossover);
      $nuova_popolazione[$i]['output'] = array_splice($popolazione[$individuo_1]['output'],0,$crossover,array_slice($popolazione[$individuo_2]['output'],-$crossover,$crossover));
      $crossover = ceil(count($popolazione[$individuo_2]['output'])*$n_crossover);
      $nuova_popolazione[$i+1]['output'] = array_splice($popolazione[$individuo_2]['output'],0,$crossover,array_slice($popolazione[$individuo_1]['output'],-$crossover,$crossover));
      $nuova_popolazione[$i]['score'] = compute_score($nuova_popolazione[$i]['output'], $clienti);
      $pad_lenght = count($new_prob_array)+$nuova_popolazione[$i]['score'];
      $new_prob_array = array_pad($new_prob_array, $pad_lenght, $i);
      $nuova_popolazione[$i+1]['score'] = compute_score($nuova_popolazione[$i+1]['output'], $clienti);
      $pad_lenght = count($new_prob_array)+$nuova_popolazione[$i+1]['score'];
      $new_prob_array = array_pad($new_prob_array, $pad_lenght, $i+1);
    }
    unset($popolazione);
    unset($prob_array);
    $popolazione = $nuova_popolazione;
    $prob_array = $new_prob_array;
    unset($nuova_popolazione);
    unset($new_prob_array);
    //print_r($popolazione);
    echo "fine epoca ".$e."; score:".max(array_column($popolazione,'score'))." tempo:".(time()-$zero).";\n";
    if($best_score > max(array_column($popolazione,'score')))
      break;
    $best_score = max(array_column($popolazione,'score'));
  }

  stampa($file, array_keys($output), $zero);
  unset($output, $clienti, $gusti, $score);
  //array_rand($gusti, rand(1, count($gusti)));

}

?>
