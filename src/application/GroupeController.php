<?php

namespace Djs\Application;

use Djs\Framework\Request;
use Djs\Framework\Response;
use Djs\Framework\View;

class GroupeController{
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

    public function nouveauGroupe(){
        $content = '';
        $content .= '<form method="post" action="?o=groupe&a=sauverGroupe">';
        $content .= 'Nom Groupe : <input name="nom" type="text" required>';
        $content .= 'Description : <input name="description" type="text" required>';
        $content .= '<input type="submit">';
        $content .= '</form>';

        $this->view->setPart('title', 'Inscription');
        $this->view->setPart('content', $content);
    }

    public function sauverGroupe(){
        $groupe=new Groupe($_POST['nom'],$_POST['description']);
        $p=$this->storage->createGroupe($groupe);
        echo $p;
        //$this->POSTredirect('.','Groupe crée');

    }

    public function defaultAction(){}

    public function POSTredirect($url, $feedback)
    {
        $_SESSION['feedback'] = $feedback;
        header("Location: " . htmlspecialchars_decode($url), true, 303);
        die;
    }








}


?>