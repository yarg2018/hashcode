<?php
/*
php plot_test2.php a_example.in > a_example.html
php plot_test2.php b_little_bit_of_everything.in > b_little_bit_of_everything.html
php plot_test2.php c_many_ingredients.in > c_many_ingredients.html
php plot_test2.php d_many_pizzas.in > d_many_pizzas.html
php plot_test2.php e_many_teams.in > e_many_teams.html
php plot_test2.php a_example.in > a_example.html && php plot_test2.php b_little_bit_of_everything.in > b_little_bit_of_everything.html && php plot_test2.php c_many_ingredients.in > c_many_ingredients.html && php plot_test2.php d_many_pizzas.in > d_many_pizzas.html && php plot_test2.php e_many_teams.in > e_many_teams.html
*/
$fname=$argv[1];

/*
*/
$zero=time();
//$f=file($fname.".in");
$f=str_replace(array("\n","\r"),'',file($fname));
$d=explode(" ",$f[0]);

$npizze=$d[0];
$squadre2=$d[1];
$squadre3=$d[2];
$squadre4=$d[3];

$pizza=array();
$gusti=array();
$ngusti=0;

//raccolgo info delle pizze e lista dei gusti
for($i=0;$i<$npizze;$i++){
	$d=explode(" ",$f[$i+1]);
  $pizza[$i]['identificativo']=$i;
  $pizza[$i]['n_gusti']=$d[0];
  for($j=0; $j<$d[0]; $j++){
    //$pizza[$i]['gusti'][$j]=$d[$j+1];
    $pizza[$i][$d[$j+1]]=1;
    if(!array_key_exists($d[$j+1], $gusti)){
      $gusti[$d[$j+1]] = 1;
    }else{
      $gusti[$d[$j+1]] = $gusti[$d[$j+1]] + 1;
    }
  }
}
array_multisort($gusti);
$dataPoints = array();
foreach($gusti as $label => $freq){
  array_push($dataPoints,array("y"=>$freq, "label"=>$label));
}
array_multisort(array_column($pizza, 'n_gusti'), SORT_DESC, $pizza);
$dataPoints_pizza = array();
foreach($pizza as $piz){
  array_push($dataPoints_pizza,array("y"=>$piz['n_gusti'], "label"=>$piz['identificativo']));
}
//  print_r($dataPoints);

?>

<!DOCTYPE HTML>
<html>
<head>
<script>
window.onload = function () {

  var chart = new CanvasJS.Chart("chartContainer", {
  	animationEnabled: true,
  	theme: "light2",
  	title:{
  		text: <?php echo json_encode("Gusti:".$fname); ?>
  	},
  	axisY: {
  		title: "Freq"
  	},
  	data: [{
  		type: "column",
  		yValueFormatString: "#,##0 freq",
  		dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
  	}]
  });
  chart.render();

  var chart2 = new CanvasJS.Chart("chartContainer2", {
    animationEnabled: true,
    theme: "light2",
    title:{
      text: <?php echo json_encode("Pizze:".$fname); ?>
    },
    axisY: {
      title: "Freq"
    },
    data: [{
      type: "column",
      yValueFormatString: "#,##0 freq",
      dataPoints: <?php echo json_encode($dataPoints_pizza, JSON_NUMERIC_CHECK); ?>
    }]
  });
  chart2.render();
}
</script>
</head>
<body>
<div id="chartContainer" style="height: 370px; width: 100%;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div>
<table style="float: left">
  <thead>
    <tr>
      <th>Top 10 Gusti Freq</th><th>Gusto</th>
    </tr>
  </thead>
  <tbody>
<?php foreach (array_slice($dataPoints,0,10) as $row): array_map('htmlentities', $row); ?>
    <tr>
      <td><?php echo implode('</td><td>', $row); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
<table style="float: left">
  <thead>
    <tr>
      <th>Last 10 Gusti Freq</th><th>Gusto</th>
    </tr>
  </thead>
  <tbody>
<?php foreach (array_slice($dataPoints,-10,10) as $row): array_map('htmlentities', $row); ?>
    <tr>
      <td><?php echo implode('</td><td>', $row); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
<table style="float: left">
  <thead>
    <tr>
      <th>Statistics</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>npizze</td><td><?php echo number_format($npizze); ?></td>
    </tr>
    <tr>
      <td>squadre2</td><td><?php echo number_format($squadre2); ?></td>
    </tr>
    <tr>
      <td>squadre3</td><td><?php echo number_format($squadre3); ?></td>
    </tr>
    <tr>
      <td>squadre4</td><td><?php echo number_format($squadre4); ?></td>
    </tr>
    <tr>
      <td>ordini totali</td><td><?php echo number_format($squadre4+$squadre3+$squadre2); ?></td>
    </tr>
    <tr>
      <td>pizze necessarie</td><td><?php echo number_format(4*$squadre4+3*$squadre3+2*$squadre2); ?></td>
    </tr>
    <tr>
      <td>totale gusti</td><td><?php echo number_format(count(array_keys($gusti))); ?></td>
    </tr>
    <tr>
      <td>numero medio gusti</td><td><?php echo number_format(array_sum(array_column($pizza, 'n_gusti'))/$npizze); ?></td>
    </tr>
  </tbody>
</table>
<table style="float: left">
  <thead>
    <tr>
      <th>Top 10 Pizza Freq</th><th>ID</th>
    </tr>
  </thead>
  <tbody>
<?php foreach (array_slice($dataPoints_pizza,0,10) as $row): array_map('htmlentities', $row); ?>
    <tr>
      <td><?php echo implode('</td><td>', $row); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
<table style="float: left">
  <thead>
    <tr>
      <th>Last 10 Pizza Freq</th><th>ID</th>
    </tr>
  </thead>
  <tbody>
<?php foreach (array_slice($dataPoints_pizza,-10,10) as $row): array_map('htmlentities', $row); ?>
    <tr>
      <td><?php echo implode('</td><td>', $row); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<br clear="all" />
<div id="chartContainer2" style="height: 370px; width: 100%;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

</body>
</html>
