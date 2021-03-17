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
    protected $outils;

    public function __construct(Request $request, Response $response, View $view, AutenticationManager $autenticationManager, Storage $storage,Outils $outils)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->autenticationManager = $autenticationManager;
        $this->storage = $storage;
        $this->outils=$outils;




        $this->view->setPart('menu', $this->outils->getMenu());
    }

    /**
     * exécuter le contrôleur de classe pour effectuer l'action $action
     *
     * @param $action
     */
    public function execute($action)
    {
        if(method_exists($this,$action)){
            $this->$action();
        }else{
            echo "wrong function";
        }

    }

    public function deconnexion()
    {
        $this->autenticationManager->deconnexion();
        $this->outils->POSTredirect(".", "Déconnecté");
    }

    public function Oauth()
    {
        $data_array = array(
            "client_id" => 58487,
            "client_secret" => "94de0c61163e535a4343e5f517bc1cdc073d06f7",
            "code" => $this->request->getGetParam('code'),
            "grant_type" => "authorization_code",
        );

        $make_call = $this->outils->callAPI('POST', 'https://www.strava.com/oauth/token', json_encode($data_array));

        $response = json_decode($make_call, true);

        echo $response['athlete']['username'];
        $_SESSION['user'] = $response;
        $this->outils->POSTredirect(".", "HELLO");
    }

    public function getAthlete()
    {
        $data_array = array(
            "access_token" => $_SESSION['user']['access_token'],
        );

        $make_call = $this->outils->callAPI('GET', 'https://www.strava.com/api/v3/athlete', $data_array);

        $response = json_decode($make_call, true);
        return $response;
    }

    public function defaultAction()
    {
        if($this->autenticationManager->isConnected()){
            if($this->storage->isCoach($_SESSION['user']['athlete']['id'])){
                return $this->makeCoachHomepage();
            }else{
                if($this->storage->isSportif($_SESSION['user']['athlete']['id'])){
                    return $this->makeSportifHomepage();
                }else{
                    $this->outils->POSTredirect('?a=inscriptionAthlete', 'inscription requise');
                }
            }

        }else{
            return $this->makeHomePage();
        }

    }

    public function afficher()
    {
        $data_array = array(
            "access_token" => $_SESSION['user']['access_token'],
        );

        $make_call = $this->outils->callAPI('GET', 'https://www.strava.com/api/v3/athlete/activities', $data_array);

        $response = json_decode($make_call, true);

        var_dump($response);


    }


    public function makeHomePage()
    {
            $title = "Bienvenue !";
            $content = "Bienvenue sur STRAVA API.";
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
    }

    public function makeCoachHomepage(){
        $title = "Bienvenue !";
        $content = "Bienvenue Coach! dans Strava!.";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    public function makeSportifHomepage(){
        $title = "Bienvenue a toi!";
        $content = "Bienvenue Sportif! dans Strava!.";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
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
        $athlete = new Athlete($a['id'], $a['lastname'], $a['firstname'], $a['weight'], $_POST['type'],$a['profile_medium']);
        $this->storage->createAthlete($athlete);
        $this->outils->POSTredirect('.', 'Inscription faite');

    }

    public function show(){
        $id=$this->request->getGetParam('id');
        $athlete=$this->storage->getUser($id);
        $tab=$this->storage->getActivitesOdered($id);
        $labels=$tab[0];
        $data=$tab[1];
        $title="Page de ".$athlete->getPrenom();
        $content="<img src='".$athlete->getImageUrl()."'>";
        $content.="<script src='https://cdn.jsdelivr.net/npm/chart.js@2.8.0'></script>";
        $content.="<div style='width: 50%'> <canvas id='myChart'></canvas> </div>";
        $content.="<script type='text/javascript'>";
        $content.="var ctx = document.getElementById('myChart').getContext('2d');";
        $content.="var chart = new Chart(ctx, {
        type: 'line',";
        $content.="data: {
        labels: ".json_encode($labels).",
        datasets: [{ 
            data: ".json_encode($data).",
            label: 'Distance Km',
            borderColor: '#3e95cd',
            fill: false
        }
        ]},
        options: {}
        });";
        $content.="</script>";
        $this->view->setPart('title',$title);
        $this->view->setPart('content',$content);
    }

}

?>