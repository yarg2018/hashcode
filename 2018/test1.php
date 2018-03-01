<?php
#include  "utility";
$file=file("test.in");
$n=0;
$tmp=explode( " ",$file[$n++]);

$rows=$tmp[0];
$columns=$tmp[1];
$vehicles=$tmp[2];
$rides=$tmp[3];
$bonus =$tmp[4];
$steps=$tmp[5];

for ($i=0; $i< $rides; $i++){
	$ride = array("a"=>0,"b"=>0,"x"=>0,"y"=>0,"s"=>0,"f"=>0);
	list($ride['a'],$ride['b'],$ride['x'],$ride['y'],$ride['s'],$ride['f']) = explode(" ",$file[$n++]);
}










?>