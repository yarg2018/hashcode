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
  $drone_avaiable[$d]=0;
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
      $d=array_search(min($drone_avaiable), $drone_avaiable);
      // vado alla warehous w_stop a prendere i prodotti dell'ordine che ci sono e che ci stanno nel drone come peso
      // tolgo oggetti dalla warehose
      // mando drone a consegnare l'ordine controllando che drone torni available al massimo al turno t
      // scalo prodotto dall'ordine e controllo se tutto l'ordine è evaso
    }
  }
}

for($d=0; $d<$drones; $d++){
  $min_distanza=$max_distanza;
  for ($w=0;$w<$warehouses;$w++){
  	for($o=0; $o<$n_orders; $o++){
      foreach($order_items[$o] as $key ){
        if($wa_product_qt[$w][$key] != 0){
          $cur_distanza=distanza($wa[$w][0],$wa[$w][1],$order_delivery[$o][0],$order_delivery[$o][1]);
      		if ($cur_distanza<$min_distanza){
      			$min_distanza=$cur_distanza;
      			$min_warehouse=$w;
      			$min_orders=$o;
      		}
        }
      }
    }
  }
  $out[0]++;
  $out[$out[0]]=$d." L ".$min_warehouse." ".$order_items[$min_orders][0]." 1\n";
  echo $out[1];
}


echo "mindistanza $min_distanza $min_warehouse $min_orders " ."\n";




echo "row ".$row."\n";
echo "columns ".$columns."\n";
echo "drones ".$drones."\n";
echo "max turns ".$max_turns."\n";
echo "max payload  ".$max_payload."\n";

echo "warehoses ".$warehouses."\n";



?>
