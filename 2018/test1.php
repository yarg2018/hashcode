<?php
require_once("utility.php");

$input_files = array('a_example','b_should_be_easy','c_no_hurry','d_metropolis','e_high_bonus');


foreach($input_files as $input_file)
{
    print("== lavoro $input_file\n");
	$file=file($input_file . ".in");
	$n=0;
	$tmp=explode( " ",$file[$n++]);

    $rows=$tmp[0];
    $columns=$tmp[1];
    $vehicles=$tmp[2];
    $rides=$tmp[3];
    $bonus =$tmp[4];
    $steps=$tmp[5];

    $corse = array();

    $ride = array('id'=>array(),'a'=>array(),'b'=>array(),'x'=>array(),'y'=>array(),'s'=>array(),'f'=>array(),'distance'=>array());

    for ($i=0; $i< $rides; $i++){
        list($ride['a'][$i],$ride['b'][$i],$ride['x'][$i],$ride['y'][$i],$ride['s'][$i],$ride['f'][$i]) = explode(" ",$file[$n++]);
        $ride['distance'][$i] = distanza($ride['a'][$i],$ride['b'][$i],$ride['x'][$i],$ride['y'][$i]);
        $ride['id'][$i] = $i;
    }

    array_multisort(
        $ride['distance'],SORT_DESC, SORT_NUMERIC,
        $ride['s'],SORT_ASC, SORT_NUMERIC,
        $ride['id'],
        $ride['a'],
        $ride['b'],
        $ride['x'],
        $ride['y'],
        $ride['f']
    );
    
    for($j=0;$j<count($ride['a']);$j++)
    {
        $corse[] = array(
            'id'	=>	$ride['id'][$j],
            'a'	=>	$ride['a'][$j],
            'b'	=>	$ride['b'][$j],
            'x'	=>	$ride['x'][$j],
            'y'	=>	$ride['y'][$j],
            's'	=>	$ride['s'][$j],
            'f'	=>	$ride['f'][$j],
            'distance'=>	$ride['distance'][$j]);
    }

    print_r($corse);
    
    
    
    print("== fine $input_file\n");
}

?>
