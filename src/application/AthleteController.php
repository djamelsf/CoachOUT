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
                    "Créer un groupe" =>'?a=nouveauGroupe',
                    "Mes groupes" => '?a=mesGroupes',
                    "Déconnexion" => '?a=deconnexion',
                );
            }else{
                if($this->storage->isSportif($_SESSION['user']['athlete']['id'])){
                    $menu = array(
                        "Accueil" => '.',
                        "Trouver un groupe" =>'?a=trouverGroupe',
                        "Créer un activite" => '?a=nouvelleActivite',
                        "Mes activites" => '?a=mesActivites',
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

    /**
     * exécuter le contrôleur de classe pour effectuer l'action $action
     *
     * @param $action
     */
    public function execute($action)
    {
        $this->$action();
    }

    public function deconnexion()
    {
        echo "helloo";
        $this->autenticationManager->deconnexion();
        $this->POSTredirect(".", "Déconnecté");
    }

    public function Oauth()
    {
        $data_array = array(
            "client_id" => 58487,
            "client_secret" => "94de0c61163e535a4343e5f517bc1cdc073d06f7",
            "code" => $this->request->getGetParam('code'),
            "grant_type" => "authorization_code",
        );

        $make_call = $this->callAPI('POST', 'https://www.strava.com/oauth/token', json_encode($data_array));

        $response = json_decode($make_call, true);

        echo $response['athlete']['username'];
        $_SESSION['user'] = $response;
        $this->POSTredirect(".", "HELLO");
    }

    public function getAthlete()
    {
        $data_array = array(
            "access_token" => $_SESSION['user']['access_token'],
        );

        $make_call = $this->callAPI('GET', 'https://www.strava.com/api/v3/athlete', $data_array);

        $response = json_decode($make_call, true);
        return $response;
    }

    public function defaultAction()
    {
//        if($this->autenticationManager->isConnected()){
//            return $this->afficher();
//
//        }else{
        return $this->makeHomePage();
//        }

    }

    public function afficher()
    {
        $data_array = array(
            "access_token" => $_SESSION['user']['access_token'],
        );

        $make_call = $this->callAPI('GET', 'https://www.strava.com/api/v3/athlete/activities', $data_array);

        $response = json_decode($make_call, true);

        var_dump($response);


    }


    public function makeHomePage()
    {
        if ($this->autenticationManager->isConnected()) {
            if ($this->storage->existsAthlete($_SESSION['user']['athlete']['id'])) {
                if ($this->storage->isCoach($_SESSION['user']['athlete']['id'])) {
                    $title = "Bienvenue !";
                    $content = "Bienvenue Coach! dans Strava!.";
                    $this->view->setPart('title', $title);
                    $this->view->setPart('content', $content);
                } else {
                    echo 'SPORTIF OK';
                }
            } else {
                $this->POSTredirect('?a=inscriptionAthlete', 'inscription requise');
            }
        } else {
            $title = "Bienvenue !";
            $content = "Bienvenue sur STRAVA API.";
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        }


    }

    public function inscriptionAthlete()
    {
        $content = '';
        $content .= '<form method="post" action="?a=sauverInscription">';
        $content .= '<p>etes vous un coach ou un sportif ?</p>';
        $content .= '<input name="nom" type="text" value="' . $_SESSION['user']['athlete']['firstname'] . '" disabled>';
        $content .= '<input name="prenom" type="text" value="' . $_SESSION['user']['athlete']['lastname'] . '" disabled>';
        $content .= 'Coach ou Sportif ? <select name="type" required>';
        $content .= '<option value="coach">Coach</option>';
        $content .= '<option value="sportif">Sportif</option>';
        $content .= '</select>';
        $content .= '<input type="submit">';
        $content .= '</form>';

        $this->view->setPart('title', 'Inscription');
        $this->view->setPart('content', $content);
    }

    public function sauverInscription()
    {
        $a = $this->getAthlete();
        $athlete = new Athlete($a['id'], $a['lastname'], $a['firstname'], $a['weight'], $_POST['type']);
        $this->storage->createAthlete($athlete);
        $this->POSTredirect('.', 'Inscription faite');

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