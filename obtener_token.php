<?php 
$token = tokenAuth();

$jsonIterator = new RecursiveIteratorIterator(
    new RecursiveArrayIterator($token),
    RecursiveIteratorIterator::SELF_FIRST);

echo "<p><p>";

foreach ($jsonIterator as $key => $val) {
    if(is_array($val)) {
        echo "$key:\n" . "<p>";
    } else {
       // echo "$key => $val\n" . "<p>";
      if ($key == 'access_token')
        $access_token = $val;
      if ($key == 'refresh_token')
        $refresh_token = $val;
      if ($key == 'expires_in')
        $tiempo_token = $val;
      if ($key == 'refresh_expires_in')
        $tiempo_refresh = $val;
    }
}

echo "<p>El token de acceso es : " . $access_token . " y vence en " . $tiempo_token . " segundos.<p>";
echo "<p>El token de refresh es : " . $refresh_token . " y vence en " . $tiempo_refresh . " segundos." ;


$erased_token = token_delete($refresh_token);

$jsonIterator = new RecursiveIteratorIterator(
    new RecursiveArrayIterator($erased_token),
    RecursiveIteratorIterator::SELF_FIRST);

echo "<p><p>";

foreach ($jsonIterator as $key => $val) {
    if(is_array($val)) {
        echo "$key:\n" . "<p>";
    } else {
        echo "$key => $val\n" . "<p>";
    }
}

echo "<p>El token fue borrado";


echo "<p>OK";
exit;

function tokenAuth()
{
    $url = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token';//access token url
    $data = array('client_id' => 'api-stag',//Test: 'api-stag' Production: 'api-prod'
                  'client_secret' => '',//always empty
                  'grant_type' => 'password', //always 'password'
                  //go to https://www.hacienda.go.cr/ATV/login.aspx to generate a username and password credentials
                  'username' => 'TuUsuarioATV', 
                  'password' => 'TuClaveATV', 
                  'scope' =>'');//always empty
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE)
      { echo "Resultado: " . $result; }
    $token = json_decode($result); //get a token object
    return $token; //return a json object whith token and refresh token
}

function token_delete($token)
{
$url = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/logout';
$data = array('client_id' => 'api-stag');
// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => 'refresh_token=' . $token
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
if ($result === FALSE)
  { echo "<p><p>Resultado al borrar OK"; }
$token = json_decode($result); //get a token object
return $token; //return a json object whith token and refresh token

}
