#!/usr/bin/php
<?php

function main()
{

    $nome[0]="example.in";
    $nome[1]="small.in";
    $nome[2]="medium.in";
    $nome[3]="big.in";

    $try_cumulate = false;


    foreach($nome as $v)
    {
        $inputlines=array();
        $inputlines=preg_split('/\n/',file_get_contents($v));
        $pizza = array(
            'matrix'		=> array(), 
            'mushrooms'		=> 0, 
            'tomatos' 		=> 0, 
            'min_num_ingredients'	=> 0, 
            'max_cell_per_slice'	=> 0, 
            'rows' 			=> 0, 
            'columns' 		=> 0
        );
        $current_row = 0;
        $tomatos_count = 0;
        $mushrooms_count = 0;
        foreach($inputlines as $il)
        {
            if(preg_match('/(\d+)\s(\d+)\s(\d+)\s(\d+)\s*$/', $il))
            {
                list($pizza['rows'],$pizza['columns'],$pizza['min_num_ingredients'],$pizza['max_cell_per_slice']) = preg_split('/\s/',$il);
                //print("RIGHE: $rows  COLONNE: $columns    MIN_NUM_INGREDIENTS: $min_num_ingredients   MAX_CELL_PER_SLIDE: $max_cell_per_slice\n");
            }
            if(preg_match('/^(T|M){'.$pizza['columns'].'}$/',$il))
            {
                // $pizza['matrix'][$current_row] = preg_split('//',$il,-1,PREG_SPLIT_NO_EMPTY);
                $pizza['matrix'][$current_row] = $il;
                $pizza['mushrooms'] += substr_count($il, 'T');
                $pizza['tomatos'] += substr_count($il, 'M');
                $current_row++;
            }
        }
        print_r($pizza);

        $slices = array();
        $curr_slice = 0;
        $previous_ok = false;
        $c = 0;
        for($r=0;$r<count($pizza['matrix']);$r++)
        {
            //if(substr_count($pizza['matrix'][$r],'T')>=$pizza['min_num_ingredients'] &&
            //substr_count($pizza['matrix'][$r],'M')>=$pizza['min_num_ingredients'])
            if(substr_count(substr($pizza['matrix'][$r],0,$pizza['max_cell_per_slice']),'T')>=$pizza['min_num_ingredients'] &&
            substr_count(substr($pizza['matrix'][$r],0,$pizza['max_cell_per_slice']),'M')>=$pizza['min_num_ingredients'])
            {
                if($try_cumulate)
                {
                    if((isset($slices[$curr_slice-1]) && count($slices[$curr_slice-1])>0) && $previous_ok)
                    {
                        if($slices[$curr_slice -1][3] + strlen(substr($pizza['matrix'][$r],0,$pizza['max_cell_per_slice']))>$pizza['max_cell_per_slice'])
                        {
                        }
                        else
                        {
                            $slices[$curr_slice - 1][2] = $r;
                            $slices[$curr_slice - 1][3] += strlen(substr($pizza['matrix'][$r],0,$pizza['max_cell_per_slice']));
                        }
                    }
                    else
                    {
                        $slices[$curr_slice] = array($r, $c, $r, strlen(substr($pizza['matrix'][$r],0,$pizza['max_cell_per_slice'])));
                    }
                }
                else
                {
                    //$slices[$curr_slice] = array($r, $c, $r, strlen($pizza['matrix'][$r]));
                    $slices[$curr_slice] = array($r, $c, $r, strlen(substr($pizza['matrix'][$r],0,$pizza['max_cell_per_slice'])));
                }
                $curr_slice++;
                $previous_ok = true;
            }
            else
            {
                $previous_ok = false;
            }
        }

        print "SLICES: " . count($slices) . "\n";
        foreach($slices as $s)
        {
            print implode(" ",$s)."\n";
        }

        file_put_contents(substr($v,0,strlen($v)-3) . ".out", "" . count($slices) . "\n");
        foreach($slices as $s)
        {
            file_put_contents(substr($v,0,strlen($v)-3).".out", implode(" ", $s) . "\n",FILE_APPEND);
        }
    }

}


function slice_builder()
{
	
}


main();

?>
