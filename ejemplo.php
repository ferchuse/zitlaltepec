<?php
$htmlContent = file_get_contents("http://192.99.81.148/test.php");

$trs = array();

$DOM = new DOMDocument;
$DOM->loadHTML($htmlContent);

$items = $DOM->getElementsByTagName('tr');

foreach ($items as $node) {
	$tds = array();

	foreach($node->childNodes as $element){
		$tds[] = $element->nodeValue;
	}
	$trs[] = $tds;
}

echo '<pre>';
print_r($trs);
echo '</pre>';
?>