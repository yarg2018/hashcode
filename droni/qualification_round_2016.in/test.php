#!/usr/bin/php
<?php
 $file=str_replace(array("\n","\r"),"",file("test.in"));
 $dati=explode(" ",$file[0]);
$row=$dati[0];
$columns=$dati[1];
$drones=$dati[2];
$max_turns=$dati[3];
$max_payload=$dati[4];
$product_types=$file[1];
$product_weight=explode(" ", File[2]);
$warehouses=$file[3];
$n=4;
for($i=0; $i<$warehouses;$i++){
     $wa[$i]=explode(" ",$file[$n++]);
     $wa_product_qt[$i]=explode(" ",$file[$n++]);
        
}
$n_orders=$file[$n++];
for($i=0; $i<$n_orders;$i++){
	$orders_delivery[$i]=explode(" ",$file[$n++]);
	$orders_nproduct[$i]=$file[$n++];
	$order_items[$i]=explode(" ",$file[$n++]);
}

for ($w=0;$w<$warehouses;$w++){

}





echo "row ".$row."\n";
echo "columns ".$columns."\n";
echo "drones ".$drones."\n";
echo "max turns ".$max_turns."\n";
echo "max payload  ".$max_payload."\n";

echo "warehoses ".$warehouses."\n";



?>
