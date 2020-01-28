<?php

$filename[0]="a_example.txt";
$filename[1]="b_lovely_landscapes.txt";
$filename[2]="c_memorable_moments.txt";
$filename[3]="d_pet_pictures.txt";
$filename[4]="e_shiny_selfies.txt";

	//$fname=$filename[0];

foreach($filename as $fname){
	$test=file($fname);
	$num_pictures= $test[0];
	$num_slides=0;
	$slides=array();
	for ($i=0;$i<$num_pictures;$i++){
		$picture_elements[$i]=explode(" ",$test[$i+1]); 
	}
		
	for($i=0;$i<$num_pictures;$i++){
		for($i1=0;$i1<count($picture_elements[$i]);$i1++){
			echo $picture_elements[$i][$i1]."\n";
		}
	}
	
	echo"out----------------------------\n";
	$num_slides=0;
	for($i=0;$i<$num_pictures;$i++){
		if ($picture_elements[$i][0]=="H"){
			$num_slides++;
			$slides[$num_slides]=$i;	
			echo $num_slides."-".$slides[$num_slides]."\n";			
		}
	}
	$num_slides++;
	$count_slide=0;
	for($i=0;$i<$num_pictures;$i++){
		if ($picture_elements[$i][0]=="V"){
			$count_slide++;
			if ($count_slide==1){			
				$slides[$num_slides]=$i;	
				echo $num_slides."-".$slides[$num_slides]."\n";	
			}
			else{
				$slides[$num_slides].=" ".$i;
				echo $num_slides."-".$slides[$num_slides]."\n";
				$num_slides++;
				$count_slide=0;	
			}
		}
	}		
	$num_slides--;
	echo"file----------------------------\n";
	unlink($fname.".out");
	file_put_contents($fname.".out",$num_slides."\n",FILE_APPEND);
	echo $num_slides."\n";
	foreach($slides as $slide){
		file_put_contents($fname.".out",$slide."\n",FILE_APPEND);
		echo $slide."\n";	
	}
	
	
	
	echo "------------------------------------------\n";

}
?>
