<?php
namespace Djs\Application;
use Djs\Application\AutenticationManager;
use Djs\Application\Storage;
use Djs\Framework\Request;
use Djs\Framework\Response;
use Djs\Framework\View;

class ActiviteController{
    protected $request;
    protected $response;
    protected $view;
    protected $autenticationManager;
    protected $storage;

    /**
     * ActiviteController constructor.
     * @param $request
     * @param $response
     * @param $view
     * @param $autenticationManager
     * @param $storage
     */
    public function __construct(Request $request, Response $response, View $view, AutenticationManager $autenticationManager, Storage $storage)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->autenticationManager = $autenticationManager;
        $this->storage = $storage;

        if ($this->autenticationManager->isConnected()) {
            if($this->storage->isCoach($_SESSION['user']['athlete']['id'])){
                $menu = array(
                    "Accueil" => '.',
                    "Créer un groupe" =>'?o=groupe&a=nouveauGroupe',
                    "Mes groupes" => '?a=mesGroupes',
                    "Déconnexion" => '?a=deconnexion',
                );
            }else{
                if($this->storage->isSportif($_SESSION['user']['athlete']['id'])){
                    $menu = array(
                        "Accueil" => '.',
                        "Trouver un groupe" =>'?a=trouverGroupe',
                        "Créer un activite" => '?o=activite&a=nouvelleActivite',
                        "Mes activites" => '?o=activite&a=mesActivites',
                        "Déconnexion" => '?a=deconnexion',
                    );
                }else{
                    $menu = array(
                        "Accueil" => '.',
                        "Déconnexion" => '?a=deconnexion',
                    );
                }
            }
        } else {
            $menu = array(
                "Accueil" => '.',
                "Connexion" => 'http://www.strava.com/oauth/authorize?client_id=58487&response_type=code&redirect_uri=http://localhost:8888/STRAVA&approval_prompt=force&scope=activity:read_all,profile:read_all,activity:write"',
            );
        }

        $this->view->setPart('menu', $menu);
    }

    public function execute($action)
    {
        if(method_exists($this,$action)){
            $this->$action();
        }else{
            echo "wrong function";
        }

    }

    public function defaultAction(){}

    public function nouvelleActivite(){
        $content = '';
        $content .= '<form method="post" action="?o=activite&a=sauverActivite">';
        $content .= 'Nom <input name="nom" type="text" required>';
        $content .= 'Description <input name="description" type="text">';
        $content .= 'Distance <input name="distance" type="number" step="any" required>';
        $content .= 'Date début de lactivité <input name="date" type="date" required>';
        $content .= 'Heure début de lactivité <input name="heureD" type="time" required>';
        $content .= 'Heure fin de lactivité <input name="heureF" type="time" required>';
        $content .= '<input type="submit">';
        $content .= '</form>';

        $this->view->setPart('title', 'Nouvelle activité');
        $this->view->setPart('content', $content);
    }

    public function sauverActivite(){
        $heureD=$_POST['heureD'];
        $heureF=$_POST['heureF'];
        $debut = strtotime($_POST['date']." $heureD UTC");
        $fin=strtotime($_POST['date']." $heureF UTC");
        $elapsed_time=$fin-$debut;

        $data_array = array(
            "access_token" => $_SESSION['user']['access_token'],
            "name" => $_POST['nom'],
            "type" => "run",
            "start_date_local" => $_POST['date']."T".$_POST['heureD'],
            "elapsed_time" => $elapsed_time,
            "description" => $_POST['description'],
            "distance" => $_POST['distance'],
        );

        $make_call = $this->callAPI('POST', 'https://www.strava.com/api/v3/activities', json_encode($data_array));

        $response = json_decode($make_call, true);


        $activite=new Activite($response['id'],$response['name'],$response['description'],$response['distance'],$response['start_date_local'],$response['elapsed_time'],$_SESSION['user']['athlete']['id'],date('Y-m-d H:i:s'));
        $this->storage->createActivite($activite);


        $this->POSTredirect('.','Activité crée');


    }

    public function mesActivites(){
        $this->storage->getMyActivites($_SESSION['user']['athlete']['id']);
    }

    public function callAPI($method, $url, $data = false)
    {
        $curl = curl_init();
        switch ($method) {
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
        if (!$result) {
            die("Connection Failure");
        }
        curl_close($curl);
        return $result;
    }

    public function POSTredirect($url, $feedback)
    {
        $_SESSION['feedback'] = $feedback;
        header("Location: " . htmlspecialchars_decode($url), true, 303);
        die;
    }



}



?>