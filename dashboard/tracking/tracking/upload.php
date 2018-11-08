<?php
$tempFile = $_FILES['file']['tmp_name'];
chmod($tempFile, 0777);
$workbookEdited = $_POST ['fileLastEdited'];
$safe_mode = $_POST ['safe_mode'];
$targetName = $_FILES['file']['name'];
$url = $_POST ['url'];
$vendor_id = $_POST ['vendor_id'];


$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $url,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => "fileLastEdited=$workbookEdited&safe_mode=$safe_mode&vendor_id=$vendor_id&tempFile=$tempFile&targetName=$targetName"
));
$api_res = curl_exec($curl);    
curl_close($curl);

echo $api_res;