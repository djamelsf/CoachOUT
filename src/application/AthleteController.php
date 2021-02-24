<?php
namespace Djs\Application;
use Djs\Framework\Request;
use Djs\Framework\Response;
use Djs\Framework\View;

class AthleteController
{
    protected $request;
    protected $response;
    protected $view;
    protected $autenticationManager;
    protected $storage;

    public function __construct(Request $request, Response $response, View $view,AutenticationManager $autenticationManager,Storage $storage)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->autenticationManager=$autenticationManager;
        $this->storage=$storage;


        if($this->autenticationManager->isConnected()){
            echo "//";
            $id=$_SESSION['user']['athlete']['id'];
            echo $storage->existsAthlete($_SESSION['user']['athlete']['id']); //+type
            echo "is Coach?";
            echo $storage->isCoach($id);
            echo "//";
            $menu = array(
                "Accueil" => '.',
                "Déconnexion" => '?a=deconnexion',
            );
        }else{
            $menu = array(
                "Accueil" => '.',
                "Connexion" => 'http://www.strava.com/oauth/authorize?client_id=58487&response_type=code&redirect_uri=http://localhost:8888/STRAVA&approval_prompt=force&scope=activity:read_all"',
            );
        }

        $this->view->setPart('menu', $menu);
    }

    /**
     * exécuter le contrôleur de classe pour effectuer l'action $action
     *
     * @param $action
     */
    public function execute($action)
    {
            $this->$action();
    }

    public function deconnexion(){
        echo "helloo";
        $this->autenticationManager->deconnexion();
        $this->POSTredirect(".","Déconnecté");
    }

    public function Oauth(){
        $data_array =  array(
            "client_id"        => 58487,
            "client_secret"   => "94de0c61163e535a4343e5f517bc1cdc073d06f7",
            "code" => $this->request->getGetParam('code'),
            "grant_type" => "authorization_code",
        );

        $make_call = $this->callAPI('POST', 'https://www.strava.com/oauth/token', json_encode($data_array));

        $response = json_decode($make_call, true);

        echo $response['athlete']['username'];
        $_SESSION['user']=$response;
        $this->POSTredirect(".","HELLO");
    }

    public function defaultAction()
    {
        if($this->autenticationManager->isConnected()){
            return $this->afficher();

        }else{
            return  $this->makeHomePage();
        }

    }

    public function afficher(){
        $data_array =  array(
            "access_token"=> $_SESSION['user']['access_token'],
        );

        $make_call = $this->callAPI('GET', 'https://www.strava.com/api/v3/athlete/activities', $data_array);

        $response = json_decode($make_call, true);

        var_dump($response);


    }


    public function makeHomePage() {
        $title = "Bienvenue !";
        $content= "Bienvenue sur STRAVA API.";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    public function callAPI($method, $url, $data=false){
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

    public function POSTredirect($url, $feedback){
        $_SESSION['feedback'] = $feedback;
        header("Location: ".htmlspecialchars_decode($url), true, 303);
        die;
    }

}

?>