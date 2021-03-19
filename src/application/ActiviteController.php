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
        $this->outils=$outils;


        $this->view->setPart('menu', $this->outils->getMenu());
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
        $content.='<div class="container"> <h2 class="text-center">Nouvelle activité</h2> <form method="post" action="?o=activite&a=sauverActivite">';
        $content.='<div class="form-group"><label>Nom*</label>';
        $content.='<input type="text" class="form-control" placeholder="Titre de l\'activité" name="nom" required></div>';
        $content.='<div class="form-group"> <label>Description*</label>';
        $content.='<textarea class="form-control" rows="3" name="description" required></textarea></div>';
        $content.='<div class="form-group"><label>Distance*</label>';
        $content.='<input type="number" step="any" class="form-control" placeholder="Distance en kilomètre" name="distance" required></div>';
        $content.='<div class="form-row">';
        $content.='<div class="form-group col-md-6"><label>Date début de lactivité*</label>';
        $content.='<input type="date" class="form-control" name="date" required></div>';
        $content.='<div class="form-group col-md-6"><label>Heure début de lactivité*</label>';
        $content.='<input type="time" class="form-control" name="heureD" required></div>';
        $content.='</div>';


        $content.='<div class="form-group">';
        $content.='<label>Durée*</label>';
        $content.='<input type="time" step="1" class="form-control" name="duree" required>';
        $content.='</div>';
        $content.='<button type="submit" class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;">Ajouter</button> </form></div>';

        $this->view->setPart('title', 'Nouvelle activité');
        $this->view->setPart('content', $content);
    }

    public function sauverActivite(){
        $elapsed_time=strtotime($_POST['duree']) - strtotime('TODAY');
        $data_array = array(
            "access_token" => $_SESSION['user']['access_token'],
            "name" => $_POST['nom'],
            "type" => "run",
            "start_date_local" => $_POST['date']."T".$_POST['heureD'],
            "elapsed_time" => $elapsed_time,
            "description" => $_POST['description'],
            "distance" => $_POST['distance']*1000,
        );

        $make_call = $this->outils->callAPI('POST', 'https://www.strava.com/api/v3/activities', json_encode($data_array));

        $response = json_decode($make_call, true);

        $activite=new Activite($response['id'],$response['name'],$response['description'],$response['distance']/1000,$response['start_date_local'],$response['elapsed_time'],$_SESSION['user']['athlete']['id'],date('Y-m-d H:i:s'));
        $this->storage->createActivite($activite);


        $this->outils->POSTredirect('.','Activité crée');


    }

    public function mesActivites(){
        $this->storage->getMyActivites($_SESSION['user']['athlete']['id']);
    }



}



?>