#!/usr/bin/php
<?php
include "utility.php";
$out=array();
$file=str_replace(array("\n","\r"),"",file("droni/qualification_round_2016.in/test.in"));
$dati=explode(" ",$file[0]);
$row=$dati[0];
$columns=$dati[1];
$drones=$dati[2];
$max_turns=$dati[3];
$max_payload=$dati[4];
$product_types=$file[1];
$product_weight=explode(" ", $file[2]);
$warehouses=$file[3];
$n=4;

for($i=0; $i<$warehouses;$i++){
     $wa[$i]=explode(" ",$file[$n++]);
     $wa_product_qt[$i]=explode(" ",$file[$n++]);

}
$n_orders=$file[$n++];
for($i=0; $i<$n_orders;$i++){
	$order_delivery[$i]=explode(" ",$file[$n++]);
	$order_nproduct[$i]=$file[$n++];
	$order_items[$i]=explode(" ",$file[$n++]);
  $order_id[$i]=$i;
}
$max_distanza=distanza(0, 0, $row, $columns)+1;
$ordine_evaso = array();
$order_stops = array();
$order_costs = array();

for($d=0; $d<$drones; $d++){
  $drone_available[$d]=0;
  $drone_payload[$d]=0;
  $drone[$d]=[0, 0];
  $drone_carico[$d]=array();
}

$out[0]=0;

for($o=0; $o<$n_orders; $o++){
  if(in_array($o,$ordine_evaso)){
    $order_cost[$o] = $turns;
  }
  else{
    $order_cost[$o] = 0;
    $order_stops[$o] = array();
    foreach($order_items[$o] as $key ){
      for ($w=0;$w<$warehouses;$w++){
        if($wa_product_qt[$w][$key]!=0){
          if(!in_array($w, $order_stops[$o])){
            $order_cost[$o] += distanza($wa[$w][0],$wa[$w][1],$order_delivery[$o][0],$order_delivery[$o][1]);
            array_push($order_stops[$o], $w);
          }
          break; //esco dal for delle warehouses e passo al prossimo item
        }
      }
    }
  }
}

array_multisort($order_cost, SORT_ASC, $order_stops, SORT_ASC, $order_id, $order_items, $order_nproduct, $order_delivery);


for($o=0; $o<$n_orders; $o++){
  if(!in_array($o,$ordine_evaso)){
    foreach($order_stops[$o] as $key => $w_stop){
      echo "".$o." / ".$order_id[$o]." - ".$w_stop."\n";
      // seleziono il drone
      $d=array_search(min($drone_available), $drone_available);
      $trasporto=distanza($wa[$w_stop][0],$wa[$w_stop][1],$drone[$d][0],$drone[$d][1]);
      // vado alla warehous w_stop a prendere i prodotti dell'ordine che ci sono e che ci stanno nel drone come peso
      foreach($order_items[$o] as $key_item => $item){
        // controllo che il caricamento sia possibile e procedo; altrimenti passo al prossimo item;
        if(($wa_product_qt[$w_stop][$item]>0) &&
           ($product_weight[$item]+$drone_payload[$d]<=$max_payload) &&
           ($drone_available[$d]+$trasporto+1<$max_turns)){
          // aggiungo l'azione di carico
          $out[0]++;
          $out[$out[0]]=$d." L ".$w_stop." ".$item." 1";
          echo $out[$out[0]]."\n";
          // aggiorno il carico del drone contando il peso dell'oggetto caricato
          $drone_payload[$d]+=$product_weight[$item];
          // occupo il drone per TOT turni
          // TOT=1 se il drone è già in warehouse
          if(($drone[$d][0]==$wa[$w_stop][0]) &&
             ($drone[$d][1]==$wa[$w_stop][1])){
                   $drone_available[$d]++;
          }else{
            // altrimenti
            // TOT=tempo di arrivare in warehouse +1 se il drone non è in warehouse
            // e aggiorno la posizione del drone
            $drone[$d][0]=$wa[$w_stop][0];
            $drone[$d][1]=$wa[$w_stop][1];
            $drone_available[$d]+=$trasporto+1;
          }
          // tolgo l'oggetto caricato dalla warehouse per evitare di usarlo 2 volte;
          $wa_product_qt[$w_stop][$item]--;
          //elimino il prodotto dall'ordine per evitare di caricarlo 2 volte;
          unset($order_items[$o][$key_item]);
          array_push($drone_carico[$d], $item);
        }
      }
      // ho caricato sul drone $d tutti gli item possibili(in base al peso)
      // dell'ordine in questione;
      // mando drone a consegnare l'ordine controllando che drone torni available al massimo al turno t

      // comincio a spostare il drone
      if(($drone[$d][0]!=$order_delivery[$o][0]) ||
         ($drone[$d][1]!=$order_delivery[$o][0])){
           $drone_available[$d]+=distanza($drone[$d][0],$drone[$d][1],$order_delivery[$o][0],$order_delivery[$o][1]);
           $drone[$d][0]=$order_delivery[$o][0];
           $drone[$d][1]=$order_delivery[$o][1];
      }
      //per ogni item che il drone ha caricato e che mi serve per l0rdine:
      // - controllo che available +1 <=turns
      // - do il comando di scarico
      // - alleggerisco il drone sia peso che oggetti caricati
      // - e aggiungo 1 turno al drone

      foreach($drone_carico[$d] as $carico_key => $carico_value){
        if($drone_available[$d]<=$max_turns){
          $out[0]++;
          $out[$out[0]]=$d." D ".$order_id[$o]." ".$carico_value." 1";
          echo $out[$out[0]]."\n";
          unset($drone_carico[$d][$carico_key]);
        }//else{echo "supero i turni a disposizione";}
      }
      // controllo se tutto l'ordine è evaso
      if(empty($order_id[$o])){
        array_push($ordine_evaso, $o);
      }

    }
  }
}

$fo=fopen("droni/qualification_round_2016.in/test.out","w");
foreach($out as $key => $value){
  fputs($fo, $value."\n");
}
echo "printed file \n";
fclose($fo);


?>
