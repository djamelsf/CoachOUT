<?php
namespace Djs\Application\Controller;

use Djs\Framework\Request;
use Djs\Framework\Response;
use Djs\Framework\View;
use Djs\Application\AutenticationManager;
use Djs\Application\Storage;
use Djs\Application\Outils;
use Djs\Application\Model\Athlete;

class AthleteController
{
    protected $request;
    protected $response;
    protected $view;
    protected $autenticationManager;
    protected $storage;
    protected $outils;

    public function __construct(Request $request, Response $response, View $view, AutenticationManager $autenticationManager, Storage $storage, Outils $outils)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->autenticationManager = $autenticationManager;
        $this->storage = $storage;
        $this->outils = $outils;


        $this->view->setPart('menu', $this->outils->getMenu());
    }

    /**
     * exécuter le contrôleur de classe pour effectuer l'action $action
     *
     * @param $action
     */
    public function execute($action)
    {
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }

    }

    /**
     * Dèconnexion d'un Athlète/Coach
     */
    public function deconnexion()
    {
        if ($this->autenticationManager->isConnected()) {
            $this->autenticationManager->deconnexion();
            $this->outils->POSTredirect(".", "Déconnecté");
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Authenfication STRAVA
     */
    public function Oauth()
    {
        if (!$this->autenticationManager->isConnected()) {
            $data_array = array(
                "client_id" => 58487,
                "client_secret" => "94de0c61163e535a4343e5f517bc1cdc073d06f7",
                "code" => $this->request->getGetParam('code'),
                "grant_type" => "authorization_code",
            );
            $make_call = $this->outils->callAPI('POST', 'https://www.strava.com/oauth/token', json_encode($data_array));
            $response = json_decode($make_call, true);
            $_SESSION['user'] = $response;
            $this->outils->POSTredirect(".", "bonjour");
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * @return mixed Recuperation d'un utilisateur depuis STRAVA
     */
    public function getAthlete()
    {
        $data_array = array(
            "access_token" => $_SESSION['user']['access_token'],
        );

        $make_call = $this->outils->callAPI('GET', 'https://www.strava.com/api/v3/athlete', $data_array);

        $response = json_decode($make_call, true);
        return $response;
    }

    /**
     * L'execution de l'action par default
     */
    public function defaultAction()
    {
        if ($this->autenticationManager->isConnected()) {
            if ($this->storage->isCoach($_SESSION['user']['athlete']['id'])) {
                return $this->makeCoachHomepage();
            } else {
                if ($this->storage->isSportif($_SESSION['user']['athlete']['id'])) {
                    return $this->makeSportifHomepage();
                } else {
                    $this->outils->POSTredirect('?a=inscriptionAthlete', 'inscription requise');
                }
            }
        } else {
            return $this->makeHomePage();
        }
    }

    /**
     * Page d'accueil pour un utilisateur NON connecté
     */
    public function makeHomePage()
    {
        $title = "Bienvenue !";
        $content = '<img src="wallpaper.jpg" class="img-fluid" alt="Responsive image">';
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    /**
     * Page d'accueil pour un Coach
     */
    public function makeCoachHomepage()
    {
        $title = "Bienvenue !";
        $content = "Bienvenue Coach! dans Strava!.";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    /**
     * Page d'accueil pour un Athlète
     */
    public function makeSportifHomepage()
    {
        $title = "Bienvenue a toi!";
        $content = "Bienvenue Sportif! dans Strava!.";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    /**
     * Fomulaire d'inscription d'un COACH/ATHLETE pour la première fois.
     */
    public function inscriptionAthlete()
    {
        if ($this->autenticationManager->isConnected()){
            if (!$this->storage->isCoach($_SESSION['user']['athlete']['id']) && !$this->storage->isSportif($_SESSION['user']['athlete']['id'])){
                $content = '<br>';
                $content .= '<p class="text-center">Inscription</p>';
                $content .= '<h2 class="text-center">Choisissez votre type</h2><br>';
                $content .= '<div class="container"><div class="row">';
                $content .= '<a class="col-sm-6" href="?a=sauverInscription&type=coach"><p class="text-center">Entraîneur</p>';
                $content .= '<img src="coach.png"></a>';
                $content .= '<a class="col-sm-6" href="?a=sauverInscription&type=sportif"><p class="text-center">athlète</p>';
                $content .= '<img src="sportsman.png"></a>';
                $content .= '</div></div>';
                $this->view->setPart('title', 'Inscription');
                $this->view->setPart('content', $content);
            }
        }

    }

    /**
     * Enregister l'inscription et redirection vers l'accueil
     */
    public function sauverInscription()
    {
        if ($this->autenticationManager->isConnected()) {
            if (!$this->storage->isCoach($_SESSION['user']['athlete']['id']) && !$this->storage->isSportif($_SESSION['user']['athlete']['id'])) {
                $a = $this->getAthlete();
                if(isset($_GET['type'])) {
                    $athlete = new Athlete($a['id'], $a['lastname'], $a['firstname'], $a['weight'], $_GET['type'], $a['profile_medium']);
                    $this->storage->createAthlete($athlete);
                    $this->outils->POSTredirect('.', 'Inscription faite');
                }
            }
        }

    }

    /**
     * Affichage du profil d'un athlète avec un Graphe JS CHART et ses stats
     */
    public function show()
    {
        if ($this->autenticationManager->isConnected()) {
            $id = $this->request->getGetParam('id');
            $athlete = $this->storage->getUser($id);
            $tab = $this->storage->getActivitesOdered($id);
            $labels = $tab[0];
            $data = $tab[1];
            $title = "Page de " . $athlete->getPrenom();
            $content = '<div class="container"><div class="row"> <div class="col-sm-12">';
            $content .= '<img src="' . $athlete->getImageUrl() . '" width="200" height="200" class="float-left">';
            $content .= '<div class="card-body float-left">';
            $content .= '<h5 class="card-title">' . $athlete->getPrenom() . '</h5>';
            $content .= '<p class="card-text">Totale distance parcourue : ' . $this->storage->getDistanceTotal($id)[0] . ' Km</p>';
            $time = ($this->storage->getDistanceTotal($id)[1]) / 60;
            if ($this->storage->getDistanceTotal($id)[0] == 0) {
                $allure = 0;
            } else {
                $allure = ($time / ($this->storage->getDistanceTotal($id)[0])) * 60;
            }
            $content .= '<p class="card-text">Allure moyenne : ' . date('i:s', $allure) . '/Km</p>';
            $content .= '</div> </div> <div class="col-sm-12"><br>';
            $content .= '<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Distance par jour</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Activités</a>
  </li>
</ul>
<div class="tab-content" id="myTabContent">

  <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab"> <canvas id="myChart"></canvas> </div>
  <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
';
            //listes des activites
            $activites = $this->storage->getMyActivites($id);
            foreach ($activites as $key => $value) {
                $time = ($value->getElapsedTime()) / 60;
                $allure = ($time / ($value->getDistance())) * 60;
                $nbComments = count($this->storage->getCommentaires($value->getIdAc()));
                $content .= '<div class="card"> <div class="card-body">';
                $content .= '<h5 class="card-title">' . $value->getNom() . '</h5>';
                $content .= '<p class="card-text">Description : ' . $value->getDescription() . '</p>';
                $content .= '<p class="card-text">Distance : ' . $value->getDistance() . ' Km</p>';
                $content .= '<p class="card-text">Durée :' . date('H:i:s', $value->getElapsedTime()) . '</p>';
                $content .= '<p class="card-text">Allure :' . date('i:s', $allure) . '/Km</p>';
                $content .= '<p class="card-text">Date : ' . date('Y-m-d H:i', strtotime($value->getDate())) . '</p>';
                $content .= '<a href="?o=commentaire&a=show&idAc=' . $value->getIdAc() . '"  class="btn btn-link" style="color: #fc5200;">Commenter</a>';
                $content .= '<small class="float-right">' . $nbComments . ' commentaire(s)</small>';
                $content .= '</div> </div>';
            }
            $content .= ' </div> </div>';
            $content .= "<script src='https://cdn.jsdelivr.net/npm/chart.js@2.8.0'></script>";
            $content .= "<script type='text/javascript'>";
            $content .= "var ctx = document.getElementById('myChart').getContext('2d');";
            $content .= "var chart = new Chart(ctx, {
        type: 'line',";
            $content .= "data: {
        labels: " . json_encode($labels) . ",
        datasets: [{ 
            data: " . json_encode($data) . ",
            label: 'Distance Km',
            borderColor: '#3e95cd',
            fill: false
        }
        ]},
        options: {}
        });";
            $content .= "</script>";
            $content .= '</div></div></div>';
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

}

?>