<?php

$filename[0]="a_example.txt";
$filename[1]="b_lovely_landscapes.txt";
$filename[2]="c_memorable_moments.txt";
$filename[3]="d_pet_pictures.txt";
$filename[4]="e_shiny_selfies.txt";

function search_mult_id($id, $slides)
{
	foreach($slides as $k => $v)
	{
		$array_ricerca = explode(" ",$v);
		if(in_array($id,$array_ricerca))
			return true;
	}
	return false;
}
	
foreach($filename as $fname){
	$test=file($fname);
	$num_pictures= $test[0];
	$num_slides=0;
	$slides=array();
	$tag=array();
	$tags=array();
	for ($i=0;$i<$num_pictures;$i++){
		$picture_elements[$i]=explode(" ",$i." ".$test[$i+1]);
		
		$tag[$i]=count($picture_elements[$i]);
		for ($t=1;$t<count($picture_elements[$i]);$t++)
		{
			if(!in_array($picture_elements[$i][$t],array_keys($tags)))
				$tags[$picture_elements[$i][$t]]=0;
			$tags[$picture_elements[$i][$t]]++;
		}
	}
		
	for($i=0;$i<$num_pictures;$i++){
		for($i1=0;$i1<count($picture_elements[$i]);$i1++){
			echo $picture_elements[$i][$i1]."\n";
		}
	}
	
	rsort($tags);
	array_multisort(
			$tag,SORT_NUMERIC, SORT_DESC,
			$picture_elements
	);
	echo"out----------------------------\n";
	$num_slides=0;
	
	foreach($tags as $k=>$v){ 
		for($i=0;$i<$num_pictures;$i++){
			if (in_array($k,$picture_elements[$i])&&
				!in_array($picture_elements[$i][0],$slides)){
				if ($picture_elements[$i][1]=="H"){
					$num_slides++;
					$slides[$num_slides]=$picture_elements[$i][0];	
					echo $num_slides."-".$slides[$num_slides]."\n";			
				}
			}
		}
	}	
	$num_slides++;
	foreach($tags as $k=>$v){ 
		$count_slide=0;
		for($i=0;$i<$num_pictures;$i++){
			if (in_array($k,$picture_elements[$i])&&
				!search_mult_id($picture_elements[$i][0],$slides)){
				if ($picture_elements[$i][1]=="V"){
					$count_slide++;
					if ($count_slide==1){			
						$slides[$num_slides]=$picture_elements[$i][0];	
						echo $num_slides."-".$slides[$num_slides]."\n";	
					}
					else{
						$slides[$num_slides].=" ".$picture_elements[$i][0];
						echo $num_slides."-".$slides[$num_slides]."\n";
						$num_slides++;
						$count_slide=0;	
					}
				}
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
