<?php
/*$body = array(
'key' => 'zT908g2j9njBdXcJpcq4BJqnWtIaiiF08Nr%2FDHMVS1QAcxuiP4PcBQ%3D%3DzT908g2j9njBdXcJpcq4BJqnWtIaiiF08Nr%2FDHMVS1QAcxuiP4PcBQ%3D%3D',
    'terid' => array("0060036CCE"),
    'type' => array(18),
    'starttime' => "2020-02-19",
    'endtime' => "2020-02-20",
 );

$content = "Content-type: application/json; charset=utf-8 ";
$data_url = json_encode($body);
$data_len = strlen($data_url);
$options = array('http' => array(
'method'  => 'POST',
'header' => $content . "Content-Length: $data_len",
'content' => $data_url
));
echo $data_url;
$context  = stream_context_create($options);

$resultado = file_get_contents('http://62.151.178.53:12056/api/v1/basic/alarm/detail', false, $context);
echo $resultado;*/

$url = 'http://62.151.178.53:12056/api/v1/basic/alarm/detail';

//create a new cURL resource
$ch = curl_init($url);

//setup request to send json via POST
$body = array(
'key' => 'zT908g2j9njBdXcJpcq4BJqnWtIaiiF08Nr%2FDHMVS1QAcxuiP4PcBQ%3D%3DzT908g2j9njBdXcJpcq4BJqnWtIaiiF08Nr%2FDHMVS1QAcxuiP4PcBQ%3D%3D',
    'terid' => array("0060036CCE"),
    'type' => array(18),
    'starttime' => "2020-02-19",
    'endtime' => "2020-02-20",
 );
$payload = json_encode($body);

//attach encoded JSON string to the POST fields
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

//set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

//return response instead of outputting
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//execute the POST request
$result = curl_exec($ch);

//close cURL resource
curl_close($ch);
echo $result;
?>

