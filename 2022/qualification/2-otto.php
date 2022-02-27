<?php
/**
  * File sottomesso durante il qualification round 2022
  * che ha totalizzato maggior numero di punti
  * manca la parte di schedulazione
  * manca ricerca del mentor
  **/
chdir("/home/andreo/Documenti/Hashcode/hashcode/2022/qualification/");
echo "folder=".getcwd()."\n";
$fname[0]="a_an_example.in";
$fname[1]="b_better_start_small.in";
$fname[2]="c_collaboration.in";
$fname[3]="d_dense_schedule.in";
$fname[4]="e_exceptional_skills.in";
$fname[5]="f_find_great_mentors.in";

// filter: funzione non usata
function filter($x,$value=-1){
  if($x>$value)
    return true;
  else
    return false;
}

function read_file($file_name){
  /**
    * funzione di lettura file
    * ritorna:
    * - $contributors[NomeContributore][Skill]=livello_skill
    * - $projects un array associativa che contiene i progetti
    *   $projects[NomeProgetto]['durata'] = durata del progetto
    *   $projects[NomeProgetto]['score'] = score per completamento
    *   $projects[NomeProgetto]['best_before'] = turno per ricevere bonus
    *   $projects[NomeProgetto]['numero_ruoli'] = numero ruoli necessari
    *   $projects[NomeProgetto][...il ruolo...]=esperienza richiesta per il singolo ruolo
    * - $skills['skill'] = [Contributor1, Contributor2, ...] un array che contiene i contributori che hanno la skill
    *
    **/
  echo str_repeat("-",10)."\n";
  echo "file=".$file_name."\n";
  $f=str_replace(array("\n","\r"),'',file("input_data/".$file_name.".txt"));
  $d=explode(" ",$f[0]);
  $num_contributors = $d[0];
  $num_projects = $d[1];
  $contributors = array();
  $projects = array();
  $skills = array();
  $i = 1; // mi serve per contare la riga a cui sono arrivato a leggere
  $contatore = 0; // mi serve per capire quando ho finito di leggere le righe
                  // dei contributors, e passare a leggere le righe che
                  // contengono i progetti
  while($contatore<$num_contributors){
    $d=explode(" ",$f[$i]); // $d[0] nome contributor, $d[1] numero di skills
    $i++;
    $contatore++;
    for($l=0; $l<$d[1]; $l++){
      // per ogni contributor devo leggere $d[1] righe (numero di skills che ha)
      // le conto con $l
      $e=explode(" ",$f[$i+$l]); // $e[0] la singola skill,
                                 // $e[1] l'esperienza nella skill
      $contributors[$d[0]][$e[0]]=$e[1];
      // un vettore per recuperare i contributors che hanno una specifica skill
      // è stata un'aggiunta successiva
      if(!in_array($e[0], array_keys($skills))){
        $skills[$e[0]]=array();
      }
      array_push($skills[$e[0]],$d[0]);
    }
    $i+=$l; // mi piace complicarmi la vita: avrei potuto scrivere $i++ dentro al for
  }
  //print($i);

  // inizio a leggere i progetti
  $contatore = 0;
  while($contatore<$num_projects){
    $g=explode(" ",$f[$i]);
    $projects[$g[0]]['durata']=$g[1];
    $projects[$g[0]]['score']=$g[2];
    $projects[$g[0]]['best_before']=$g[3];
    $projects[$g[0]]['numero_ruoli'] = $g[4];
    $i++;
    $contatore++;
    // per ogni progetto avrà da leggere $g[4] righe (numero di ruoli)
    // e troverò dentro il ruolo $h[0] con l'esperienza richiesta $h[1]
    for($l=0; $l<$g[4]; $l++){
      $h=explode(" ",$f[$i+$l]);
      $projects[$g[0]][$h[0]]=$h[1];
    }
    $i = $i+$l;
  }

  // stampo un po' di roba per controlli vari
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

// compute_score: funzione non usata
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
  /**
    * funzione che mi stampa l'output
    * prende in input il nome del file, l'array di output, il tempo usato
    * e stampa su nome_file.out
    **/
  // stampo il numero totale di progetti
  file_put_contents("output/".$file.".out", count($output)."\n");
  // per ogni progetto stampo il nome e vado a capo
  // quindi stampo la lista dei "contributors" separati da spazio
  foreach($output as $p_k => $p_v){
    file_put_contents("output/".$file.".out", $p_k."\n".join(" ", $p_v)."\n", FILE_APPEND);
  }
  echo "stampato output con tempo impiegato:".(time()-$t_zero).";\n";
  echo"------------\n";
}

// main
// per ogni file leggo, calcolo output, stampo
foreach($fname as $file_k => $file){
  $zero = time(); // segno quando inizio ad analizzare un file
  list($contributors, $projects, $skills) = read_file($file); // leggo file

  // ordiniamo i progetti in base a criteri che possano (ragionevolmente)
  // massimizzare il punteggio
  // abbiamo poi assegnato una regola in base al file che ha fatto miglior score
  // Tentativi:
  // - prioritizzare progetti in base a durata e score
  // - prioritizzare per best_before e score (per limitare la penalità)
  // - prioritizzare in base al solo score
  // la variabile $offset è un'ulteriore parametro per considerare ruoli anche
  // in assenza di un $offset nel livello di esperienza: da rivedere logica sotto
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

  // popolo un vettore di risultati ciclando sui progetti e,
  // per ogni ruolo richiesto, cerco il contributor con l'esperienza giusta (a meno di $offset livelli)
  $output = array();
  foreach($projects as $p_k => $p_v){
    $ruoli = array_slice($p_v, 4);  // tecnicismo: considero solo i ruoli del
                                    // progetto (buttando via le prime 4 info:
                                    // durata, score, best_before e numero_ruoli
    $lista_esclusi = array();       // creo una lista di contributor non adatti
    foreach($ruoli as $r_k => $r_v){
      foreach($skills[$r_k] as $nome_contributor){
        if(in_array($nome_contributor, $lista_esclusi)){
          continue; //se ho escluso il contributor, passo al successivo
        }
        // valuto esperienza del contributor sul ruolo
        $esperienza_contr = $contributors[$nome_contributor][$r_k];
        if($esperienza_contr >= $p_v[$r_k]+$offset){
          // se l'esperienza è in linea con quella richiesta dal progetto
          // aggiungo il contributor all'output del progetto
          // se è il primo contributor devo aggiungere il progetto nel file output
          if(!in_array($p_k, array_keys($output))){
            $output[$p_k]=array();
          }
          array_push($output[$p_k], $nome_contributor);
          // aggiungo il contributor nella lista degli esclusi del progetto per
          // non contarlo 2 volte
          array_push($lista_esclusi, $nome_contributor);
          break; // passo al successivo ruolo richiesto dal progetto
        }
      }

//    debug del problema del doppio contributor, poi risolto con lista_esclusi
//      if($file == 'b_better_start_small.in'){
//        echo "nome_contributor:".$nome_contributor."\n";
//        print($p_k);
//        print_r($output[$p_k]);
//      }
    }

    // prima di chiudere il progetto
    // conto i ruoli assegnati per vedere se mi è rimasto "incompleto"
    // e nel caso lo butto 
    if(in_array($p_k, array_keys($output))){
      if(count($output[$p_k]) < $p_v['numero_ruoli']){
        unset($output[$p_k]);
      }
    }
  }
  // stampo tutto
  stampa($file, $output, $zero);
  //unset($a);  // errore: avrei dovuto buttare via tutto per liberare
                // un po' di memoria (forse) ma non l'ho fatto e mi è rimasto
                // sulla variabile $a che nemmeno esiste più

} // passo al file successivo

?>
