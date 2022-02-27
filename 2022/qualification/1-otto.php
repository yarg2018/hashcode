<?php
/**
  * ripartito da otto1.php
  * aggiunta modalità random di selezione degli ingredienti
  * otto 5 aggiungo iter aggiuntivo
  */
chdir("/home/andreo/Documenti/Hashcode/hashcode/2022/qualification/");
echo "folder=".getcwd()."\n";
$fname[0]="a_an_example.in";
$fname[1]="b_better_start_small.in";
$fname[2]="c_collaboration.in";
$fname[3]="d_dense_schedule.in";
$fname[4]="e_exceptional_skills.in";
$fname[5]="f_find_great_mentors.in";

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
  $f=str_replace(array("\n","\r"),'',file("input_data/".$file_name.".txt"));
  $d=explode(" ",$f[0]);
  $num_contributors = $d[0];
  $num_projects = $d[1];
  $contributors = array();
  $projects = array();
  $skills = array();
  $i = 1;
  $contatore = 0;
  while($contatore<$num_contributors){
    $d=explode(" ",$f[$i]);
    //$contributors[$d[0]]=$d[0];
    $i++;
    $contatore++;
    for($l=0; $l<$d[1]; $l++){
      $e=explode(" ",$f[$i+$l]);
      $contributors[$d[0]][$e[0]]=$e[1];
      if(!in_array($e[0], array_keys($skills))){
        $skills[$e[0]]=array();
      }
      array_push($skills[$e[0]],$d[0]);
    }
    $i+=$l;
  }
  print($i);
  $contatore = 0;
  while($contatore<$num_projects){
    $g=explode(" ",$f[$i]);
    $projects[$g[0]]['durata']=$g[1];
    $projects[$g[0]]['score']=$g[2];
    $projects[$g[0]]['best_before']=$g[3];
    $projects[$g[0]]['numero_ruoli'] = $g[4];
    $i++;
    $contatore++;
    for($l=0; $l<$g[4]; $l++){
      $h=explode(" ",$f[$i+$l]);
      $projects[$g[0]][$h[0]]=$h[1];
    }
    $i = $i+$l;
  }

  echo "num_contributors=".$num_contributors."\n";
  echo "num_projects=".$num_projects."\n";
  if($file_name == 'a_an_example.in'){
    print_r($projects);
    print_r($contributors);
    print_r($skills);
  }
  echo "fine lettura file".$file_name."\n";
  return array($contributors, $projects, $skills);
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
  file_put_contents("output/".$file.".out", count($output)."\n");
  foreach($output as $p_k => $p_v){
    file_put_contents("output/".$file.".out", $p_k."\n".join(" ", $p_v)."\n", FILE_APPEND);
  }
  echo "stampato output con tempo impiegato:".(time()-$t_zero).";\n";
  echo"------------\n";
}

foreach($fname as $file_k => $file){
  $zero = time();
  list($contributors, $projects, $skills) = read_file($file);

  $offset = 0;
  if($file_k == 4){
    array_multisort(array_column($projects,'best_before'), SORT_ASC, array_column($projects,'score'), SORT_DESC, $projects);
  }elseif($file_k == 5){
    array_multisort(array_column($projects,'score'), SORT_DESC, $projects);
    $offset = -1;
  }else{
    array_multisort(array_column($projects,'durata'), SORT_DESC, array_column($projects,'score'), SORT_DESC, $projects);
  }


  if($file == 'a_an_example.in'){
    print_r($projects);
  }
  $output = array();
  foreach($projects as $p_k => $p_v){
    $ruoli = array_slice($p_v, 4);
    $lista_esclusi = array();

    foreach($ruoli as $r_k => $r_v){
      foreach($skills[$r_k] as $nome_contributor){
        if(in_array($nome_contributor, $lista_esclusi)){
          continue;
        }
        $esperienza_contr = $contributors[$nome_contributor][$r_k];
        if($esperienza_contr >= $p_v[$r_k]+$offset){
          if(!in_array($p_k, array_keys($output))){
            $output[$p_k]=array();
          }
          array_push($output[$p_k], $nome_contributor);
          array_push($lista_esclusi, $nome_contributor);

          break;
        }
      }
//      if($file == 'b_better_start_small.in'){
//        echo "nome_contributor:".$nome_contributor."\n";
//        print($p_k);
//        print_r($output[$p_k]);
//      }
    }
    if(in_array($p_k, array_keys($output))){
      if(count($output[$p_k]) < $p_v['numero_ruoli']){
        unset($output[$p_k]);
      }else{

        foreach($ruoli as $r_k => $r_v){
          $check_esp = false;
          foreach($output[$p_k] as $nome_contr){
            if(in_array($r_k, array_keys($contributors[$nome_contr]))){
              if($contributors[$nome_contr][$r_k] < $p_v[$r_k]){
                continue(2);
              }
            }
          }
          if($check_esp == false){
            unset($output[$p_k]);
            break;
          }
        }
      }

    }
  }
  stampa($file, $output, $zero);
  unset($a);

}

?>
