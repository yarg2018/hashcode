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
//$fname[3]="d_difficult";
$fname[4]="e_elaborate";

/*
*/

function filter($x,$value=0){
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

  $best_output = array_filter($gusti, "filter");
  //print_r($best_output);
  //$output = array_diff_assoc($gusti, array_column($clienti,'dislike'));
  $best_score = compute_score(array_keys($best_output), $clienti);

  echo "result:".$best_score."\n";
  unset($a);

  for($i=0; $i<count($clienti); $i++){
    $output = $best_output;
    if(!empty($clienti[$i]['dislike'])){
      $temp = array_diff($clienti[$i]['dislike'],array_keys($best_output));
      if (count($temp)==1) {
        unset($output[array_key_first($temp)]);
      } else {
        foreach($temp as $t)
          unset($output[$t]);
      }
    }
    if(!empty($clienti[$i]["like"]))
      $output = array_merge($output, array_flip(array_values($clienti[$i]["like"])));
    else
      continue;
    if($file=="a_an_example"){
      print_r($clienti[$i]["like"]);
      print_r($output);
    }
    $score = compute_score(array_keys($output), $clienti);

    if($score > $best_score){
      $best_score = $score;
      $best_output = $output;
      echo "New best result:".$best_score."; cliente:".$i."\n";
    }
    if($i % 2000 == 0){
      echo "Analizzati ".$i." clienti in ".time()-$zero."\n";
    }
  }
  echo "fine; result:".$best_score."; numero gusti: ".count($best_output)."\n";
  stampa($file, array_keys($best_output), $zero);
  unset($output, $best_output, $clienti, $gusti, $score);
  //array_rand($gusti, rand(1, count($gusti)));

}

?>
