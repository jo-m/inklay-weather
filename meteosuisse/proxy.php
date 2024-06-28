<?php

// Usage:  curl -X POST -d "secret_token=...&url=..." http://localhost:8000/proxy.php

// To compute hash: https://bcrypt.online/
// Cost: 13
const SECRET_TOKEN_HASH = '$2y$13$QPX1RTsRMqzpNlFw8b53Uu3MtAnZV8crDhit.SM5RVjDUxVzp3/HK';

function err_out($code, $msg)
{
    http_response_code($code);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["msg" => $msg, "code" => $code]);
    exit();
}

// Auth.
if (!array_key_exists("secret_token", $_POST)) {
    err_out(400, "missing auth");
}
if (!password_verify($_POST["secret_token"], SECRET_TOKEN_HASH)) {
    err_out(401, "unauthorized");
}

// Get URL.
if (!array_key_exists("url", $_POST)) {
    err_out(400, "missing URL query param");
}
$url = $_POST["url"];

// Load URL via curl.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
curl_setopt($ch, CURLOPT_ENCODING, "");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept-Encoding: gzip, deflate, br",
    "Upgrade-Insecure-Requests: 1",
]);
$data = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Forward status code, content type, data.
header("Content-Type: " . curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
http_response_code($code);
echo $data;
?>
