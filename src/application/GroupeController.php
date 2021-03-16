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
                    "Mes groupes" => '?o=groupe&a=mesGroupes',
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
        $groupe=new Groupe($_POST['nom'],$_POST['description'],$_SESSION['user']['athlete']['id']);
        $p=$this->storage->createGroupe($groupe);
        $this->POSTredirect('.','Groupe crée');

    }

    public function mesGroupes(){
        $res=$this->storage->getMyGroupes($_SESSION['user']['athlete']['id']);
        $title="Mes groupes";
        $content="<ul>";
        foreach ($res as $key => $value){
            $content.="<li> <a href='?o=groupe&a=show&id=$key' > ".$value->getNom()." </a></li>";
        }
        $content.="</ul>";
        $this->view->setPart('title',$title);
        $this->view->setPart('content',$content);
    }

    public function show(){
        if($this->storage->isSportif($_SESSION['user']['athlete']['id'])){
            $id = $this->request->getGetParam('id');
            $groupe = $this->storage->getGroupe($id);
            //$iDcoach = $this->storage->getCoachGroupe($id);
            $coach = $this->storage->getUser($groupe->getIdU());
            $title = "Groupe de " . $coach->getNom();
            $content = "<p> Groupe " . $groupe->getNom() . " </p>";
            $content .= "<p> Description :" . $groupe->getDescription() . " </p>";
            if ($this->storage->isInGroupe($id)) {
                $content .= "<a href='?o=groupe&a=retirer&id=$id'>Se retirer</a>";
            } else {
                $content .= "<a href='?o=groupe&a=adherer&id=$id'>Adherer</a>";
            }
        }else{
            $res=$this->storage->getActivitesByGroupe($this->request->getGetParam('id'));
            $id = $this->request->getGetParam('id');
            $groupe = $this->storage->getGroupe($id);

            $title="Activités du groupe: ".$groupe->getNom();
            $content="<ul>";
            foreach($res as $key => $value){
                $time=($value->getElapsedTime())/60;
                $allure=($time/($value->getDistance()/1000))*60;



                ///
                $athlete=$this->storage->getUser($value->getIdU());
                $content.="<li>";
                $content.="<a href='?o=activite&a=show&id=".$value->getIdAc()."'>";
                $content.="<p>".$value->getNom()."</p> </a>";
                $content.="<p>Description :".$value->getDescription()."</p>";
                $content.="<p>Distance :".($value->getDistance()/1000)." Km</p>";
                $content.="<p>Durée :".date('H:i:s', $value->getElapsedTime())."</p>";
                $content.="<p>Allure :".date('i:s',$allure)."/Km</p>";
                $content.="<a href='?o=athlete&a=show&id=".$athlete->getIdU()."'><p>Athlete :".$athlete->getPrenom()."</p> </a>";
                $content.="<p>Posté le ".$value->getTime()."</p>";
                $content.="";
                $content.="</li>";
            }
            $content.="</ul>";


        }
        $this->view->setPart('title',$title);
        $this->view->setPart('content',$content);
        
    }

    public function adherer(){
        $this->storage->adherer($this->request->getGetParam('id'));
        $this->POSTredirect('.','vous venez de rejoindre un nouveau groupe');
    }

    public function trouverGroupe(){
        $title="Chercher un groupe";
        $content="<form method='get'>";
        $content.="<input type='hidden' name='o' value='groupe'>";
        $content.="<input type='hidden' name='a' value='recherche'>";
        $content.="<input type='text' name='mot'>";
        $content.="<input type='submit'>";
        $content.="</form>";

        $this->view->setPart('title',$title);
        $this->view->setPart('content',$content);
    }

    public function recherche(){
        $title="Résulat de la recherche";
        $res=$this->storage->rechercheGroupe($this->request->getGetParam('mot'));
        $content="<ul>";
        foreach ($res as $key => $value){
            $content.="<li> <a href='?o=groupe&a=show&id=$key' > ".$value->getNom()." </a></li>";
        }
        $content.="</ul>";
        $this->view->setPart('title',$title);
        $this->view->setPart('content',$content);

    }



    public function defaultAction(){
    }



    public function POSTredirect($url, $feedback)
    {
        $_SESSION['feedback'] = $feedback;
        header("Location: " . htmlspecialchars_decode($url), true, 303);
        die;
    }








}


?>