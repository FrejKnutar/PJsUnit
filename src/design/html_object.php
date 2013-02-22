<?php





$string .= "<ol>";
foreach($methods as $m) {
	$string .= "<li>$m</li>";
}
$string .= "</ol>";
$string .= "<p><label>Methods:</label>&nbsp".count($methods)."</p>";
$string .= "<p><label>Passed:</label>&nbsp$passed_count (" . (count($methods)>0 ? ($passed_count/count($methods))*100 .'%' : 'NA').")</p>";
$string .= "<p><label>Failed:</label>&nbsp$failed_count (" . (count($methods)>0 ? ($failed_count/count($methods))*100 .'%' : 'NA').")</p>";
$string .= "</ol>";
return $string;
?>