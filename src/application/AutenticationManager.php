<?php
namespace Djs\Application;

class AutenticationManager{
    private $users;

    function __construct(){

    }

    public function isConnected(){
       if(isset($_SESSION['user'])){

          $date = new \DateTime();
          if(($_SESSION['user']['expires_at'])<($date->getTimestamp())){
                $data_array =  array(
                    "client_id"        => 58487,
                    "client_secret"   => "94de0c61163e535a4343e5f517bc1cdc073d06f7",
                    "grant_type" => "refresh_token",
                    "refresh_token" => $_SESSION['user']['refresh_token'],
                );

                $make_call = $this->callAPI('POST', 'https://www.strava.com/oauth/token', json_encode($data_array));

                $response = json_decode($make_call, true);
                $athlete=$_SESSION['user']['athlete'];
                $_SESSION['user']=$response;
                $_SESSION['user']['athlete']=$athlete;
         }
       }
        return isset($_SESSION['user']);
    }

    public function deconnexion(){
        unset($_SESSION['user']);
    }

    public function callAPI($method, $url, $data){
        $curl = curl_init();
        switch ($method){
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);
        return $result;
    }

}
?>