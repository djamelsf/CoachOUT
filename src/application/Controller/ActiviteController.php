<?php

namespace Djs\Application\Controller;

use Djs\Application\AutenticationManager;
use Djs\Application\Storage;
use Djs\Framework\Request;
use Djs\Framework\Response;
use Djs\Framework\View;
use Djs\Application\Outils;
use Djs\Application\Model\Activite;

class ActiviteController
{
    protected $request;
    protected $response;
    protected $view;
    protected $autenticationManager;
    protected $storage;
    protected $outils;

    /**
     * ActiviteController constructor.
     * @param $request
     * @param $response
     * @param $view
     * @param $autenticationManager
     * @param $storage
     */
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
     * execution d'une action
     * @param $action le nom de la fonction
     */
    public function execute($action)
    {
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            echo "wrong function";
        }

    }

    public function defaultAction()
    {
    }


    /**
     * Formulaire "Nouvelle activité"
     */
    public function nouvelleActivite()
    {
        if(!$this->autenticationManager->isConnected()){
            $this->view->setPart('title', 'Nouvelle activité');
            $this->view->setPart('content', $this->outils->forbiddenPage());
        }else{
            $content = '';
            $content .= '<div class="container"> <h2 class="text-center">Nouvelle activité</h2> <form method="post" action="?o=activite&a=sauverActivite">';
            $content .= '<div class="form-group"><label>Nom*</label>';
            $content .= '<input type="text" class="form-control" placeholder="Titre de l\'activité" name="nom" required></div>';
            $content .= '<div class="form-group"> <label>Description*</label>';
            $content .= '<textarea class="form-control" rows="3" name="description" required></textarea></div>';
            $content .= '<div class="form-group"><label>Distance*</label>';
            $content .= '<input type="number" step="any" class="form-control" placeholder="Distance en kilomètre" name="distance" required></div>';
            $content .= '<div class="form-row">';
            $content .= '<div class="form-group col-md-6"><label>Date début de lactivité*</label>';
            $content .= '<input type="date" class="form-control" name="date" required></div>';
            $content .= '<div class="form-group col-md-6"><label>Heure début de lactivité*</label>';
            $content .= '<input type="time" class="form-control" name="heureD" required></div>';
            $content .= '</div>';
            $content .= '<div class="form-group">';
            $content .= '<label>Durée*</label>';
            $content .= '<input type="time" step="1" class="form-control" name="duree" required>';
            $content .= '</div>';
            $content .= '<button type="submit" class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;">Ajouter</button> </form></div>';
            $this->view->setPart('title', 'Nouvelle activité');
            $this->view->setPart('content', $content);
        }
    }

    /**
     * Sauver l'activité inscrite sur le formulaire
     */
    public function sauverActivite()
    {
        $elapsed_time = strtotime($_POST['duree']) - strtotime('TODAY');
        $data_array = array(
            "access_token" => $_SESSION['user']['access_token'],
            "name" => $_POST['nom'],
            "type" => "run",
            "start_date_local" => $_POST['date'] . "T" . $_POST['heureD'],
            "elapsed_time" => $elapsed_time,
            "description" => $_POST['description'],
            "distance" => $_POST['distance'] * 1000,
        );
        $make_call = $this->outils->callAPI('POST', 'https://www.strava.com/api/v3/activities', json_encode($data_array));
        $response = json_decode($make_call, true);
        $activite = new Activite($response['id'], $response['name'], $response['description'], $response['distance'] / 1000, $response['start_date_local'], $response['elapsed_time'], $_SESSION['user']['athlete']['id'], date('Y-m-d H:i:s'));
        $this->storage->createActivite($activite);
        $this->outils->POSTredirect('.', 'Activité crée');
    }

    /**
     * Afficher les activités de l'athlete authentifié
     */
    public function mesActivites()
    {
        $res = $this->storage->getMyActivites($_SESSION['user']['athlete']['id']);
        $title = "Mes activités";
        $content = '<div class="container"> <h2 class="text-center">Mes activités</h2> <div class="col">';
        foreach ($res as $key => $value) {
            $time = ($value->getElapsedTime()) / 60;
            $allure = ($time / ($value->getDistance())) * 60;
            $nbComments = count($this->storage->getCommentaires($value->getIdAc()));
            $content .= '<div class="col-sm-12"> <div class="card"> <div class="card-body">';
            $content .= '<h5 class="card-title">' . $value->getNom() . '</h5>';
            $content .= '<p class="card-text">Description : ' . $value->getDescription() . '</p>';
            $content .= '<p class="card-text">Distance : ' . $value->getDistance() . ' Km</p>';
            $content .= '<p class="card-text">Durée :' . date('H:i:s', $value->getElapsedTime()) . '</p>';
            $content .= '<p class="card-text">Allure :' . date('i:s', $allure) . '/Km</p>';
            $content .= '<p class="card-text">Date : ' . date('Y-m-d H:i', strtotime($value->getDate())) . '</p>';
            $content .= '<a href="?o=commentaire&a=show&idAc=' . $value->getIdAc() . '" class="btn btn-link" style="color: #fc5200;">Commenter</a>';
            $content .= '<a href="?o=activite&a=supprimer&id=' . $value->getIdAC() . '" class="btn btn-danger">Supprimer</a>';
            $content .= '<small class="float-right">' . $nbComments . ' commentaire(s)</small>';
            $content .= ' </div></div> </div> <br>';
        }
        $content .= '</div></div>';
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    /**
     * Formulaire de pour confirmer la suppresion d'une activité
     */
    public function supprimer()
    {
        $title = 'Suppresion';
        $id = $this->request->getGetParam('id');
        $content = '<form class="container" method="post" action="?o=activite&a=confirSuppr&id=' . $id . '"> <h5>Voulez vous supprimer cette activité ?</h5>';
        $content .= "Oui<input type='radio' name='ouiNon' value='oui' required>";
        $content .= "<br>Non<input type='radio' name='ouiNon' value='non'>";
        $content .= '<div class="form-group row"><div class="col-sm-10">';
        $content .= '<button type="submit" class="btn btn-primary" style="background-color:#fc5200; border-color:#fc5200; ">Confirmer</button>';
        $content .= '</div></div></form>';
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    /**
     * Suppresion d'une activité avec redirection vers (Mes activités)
     */
    public function confirSuppr()
    {
        if ($_POST['ouiNon'] == 'oui') {
            $this->storage->supprimerActivite($this->request->getGetParam('id'));
            $this->outils->POSTredirect('.', 'Activité supprimée');
        }
    }


}


?>