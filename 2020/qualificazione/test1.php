<?php


$fname[0]='a_example.txt';
$fname[1]='b_read_on.txt';	     
$fname[2]='c_incunabula.txt';     
$fname[3]='d_tough_choices.txt';  
$fname[4]='e_so_many_books.txt';
$fname[5]='f_libraries_of_the_world.txt';

foreach($fname as $fn){
	$p=0;
	$f=file($fn);
	$l=explode(" ",$f[$p++]);
	$books=$l[0];
	$libraries=$l[1];
	$days=$l[2];
	
	$scores=explode(" ",$f[$p++]);
	
	for($i=0;$i<$libraries;$i++){	
		$l=explode(" ",$f[$p++]);
		$library[$i]['books']=$l[0];
		$library[$i]['days']=$l[1];
		$library[$i]['librigiorno']=$l[1];
		$library[$i]['idlibri']=explode(" ",$f[$p++]);	
		
		
	}	
	
	
	
	
}




?>
