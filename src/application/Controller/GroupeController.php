<?php

namespace Djs\Application\Controller;

use Djs\Framework\Request;
use Djs\Framework\Response;
use Djs\Framework\View;
use Djs\Application\AutenticationManager;
use Djs\Application\Storage;
use Djs\Application\Outils;
use Djs\Application\Model\Groupe;

class GroupeController
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

    public function execute($action)
    {
        if($this->autenticationManager->isConnected()){
            if (method_exists($this, $action)) {
                $this->$action();
            } else {
                $this->view->setPart('title','Forbidden page');
                $this->view->setPart('content',$this->outils->forbiddenPage());
            }
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }

    }

    /**
     * Formulaire d'inscription d'un nouveau groupe
     */
    public function nouveauGroupe()
    {
        if ($this->storage->isCoach($_SESSION['user']['athlete']['id'])) {
            $content = '<div class="container"> <h2 class="text-center">Nouveau groupe</h2>';
            $content .= '<form method="post" action="?o=groupe&a=sauverGroupe">';
            $content .= '<div class="form-group"> <label for="inputName">Nom</label>';
            $content .= '<input type="text" class="form-control" id="inputName" placeholder="Nom du groupe" name="nom" required> </div>';
            $content .= '<div class="form-group"> <label for="inputName">Description</label>';
            $content .= '<textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="description" required></textarea> </div>';
            $content .= '<button type="submit" class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;">Ajouter</button>';
            $this->view->setPart('title', 'Inscription');
            $this->view->setPart('content', $content);
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }

    }

    /**
     * Formulaire modification groupe
     */
    public function modifier(){
        if ($this->storage->isCoachOfGroupe($idG = $this->request->getGetParam('id'))) {
            $groupe=$this->storage->getGroupe($_GET['id']);
            $content = '<div class="container"> <h2 class="text-center">Modifier groupe</h2>';
            $content .= '<form method="post" action="?o=groupe&a=confModification&id='.$_GET['id'].'">';
            $content .= '<div class="form-group"> <label for="inputName">Nom</label>';
            $content .= '<input type="text" class="form-control" id="inputName" placeholder="Nom du groupe" name="nom" value="'.$groupe->getNom().'" required> </div>';
            $content .= '<div class="form-group"> <label for="inputName">Description</label>';
            $content .= '<textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="description" required>'.$groupe->getDescription().'</textarea> </div>';
            $content .= '<button type="submit" class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;">Modifier</button>';
            $this->view->setPart('title', 'Modification groupe');
            $this->view->setPart('content', $content);

        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }

    }
    public function confModification(){
        if ($this->storage->isCoachOfGroupe($idG = $this->request->getGetParam('id'))) {
            $this->storage->modifierGroupe($_GET['id'],htmlspecialchars($_POST['nom']),htmlspecialchars($_POST['description']));
            $this->outils->POSTredirect('?o=groupe&a=mesGroupes', 'Groupe modifié');
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Enregistrement d'un nouveau groupe avec redirection vers (Mes groupes)
     */
    public function sauverGroupe()
    {
        if ($this->storage->isCoach($_SESSION['user']['athlete']['id'])) {
            $groupe = new Groupe(htmlspecialchars($_POST['nom']), htmlspecialchars($_POST['description']), $_SESSION['user']['athlete']['id']);
            $this->storage->createGroupe($groupe);
            $this->outils->POSTredirect('?o=groupe&a=mesGroupes', 'Groupe crée');
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }

    }

    /**
     * Affichage des groupe d'un coach
     */
    public function mesGroupes()
    {
        if ($this->storage->isCoach($_SESSION['user']['athlete']['id'])) {
            $res = $this->storage->getMyGroupes($_SESSION['user']['athlete']['id']);
            $title = "Mes groupes";
            $content = '<div class="container"> <h2 class="text-center">Mes groupes</h2> <div class="col">';
            foreach ($res as $key => $value) {
                $number = count($this->storage->getGroupeMembres($key));
                $content .= '<div class="col-sm-12"> <div class="card"> <div class="card-body">';
                $content .= '<a href="?o=groupe&a=show&id=' . $key . '" ><h5 class="card-title">' . $value->getNom() . '</h5> </a>';
                $content .= '<p class="card-text">' . $value->getDescription() . '</p>';
                $content .= '<a href="?o=groupe&a=modifier&id=' . $key . '" class="btn btn-success">Modifier</a>';
                $content .= ' <a href="?o=groupe&a=supprimer&id=' . $key . '" class="btn btn-danger">Supprimer</a>';
                $content .= '<small class="float-right">' . $number . ' membre</small>';
                $content .= ' </div></div> </div> <br>';
            }
            $content .= '</div></div>';
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Formulaire suppresion groupe
     */
    public function supprimer()
    {
        if ($this->storage->isCoachOfGroupe($idG = $this->request->getGetParam('id'))) {
            $title = 'Suppresion';
            $idG = $this->request->getGetParam('id');
            $content = '<form class="container" method="post" action="?o=groupe&a=confirSuppr&id=' . $idG . '"> <h5>Voulez vous supprimer ce groupe ?</h5>';
            $content .= "Oui<input type='radio' name='ouiNon' value='oui' required>";
            $content .= "<br>Non<input type='radio' name='ouiNon' value='non'>";
            $content .= '<div class="form-group row"><div class="col-sm-10">';
            $content .= '<button type="submit" class="btn btn-primary" style="background-color:#fc5200; border-color:#fc5200; ">Confirmer</button>';
            $content .= '</div></div></form>';
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Suppresion d'un groupe avec redirection vers (Mes groupes)
     */
    public function confirSuppr()
    {
        if ($this->storage->isCoachOfGroupe($idG = $this->request->getGetParam('id'))) {
            if ($_POST['ouiNon'] == 'oui') {
                $this->storage->supprimerGroupe($this->request->getGetParam('id'));
                $this->outils->POSTredirect('?o=groupe&a=mesGroupes', 'Groupe supprimé');
            }else{
                $this->outils->POSTredirect('?o=groupe&a=mesGroupes', 'Groupe non supprimé');
            }
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Formulaire pour qu'un athlète quitte un groupe
     */
    public function quitter()
    {
        if ($this->storage->isInGroupe($this->request->getGetParam('id'))) {
            $title = 'Quitter groupe';
            $idG = $this->request->getGetParam('id');
            $content = '<form class="container" method="post" action="?o=groupe&a=confirQuitter&id=' . $idG . '"> <h5>Voulez vous quitter ce groupe ?</h5>';
            $content .= "Oui<input type='radio' name='ouiNon' value='oui' required>";
            $content .= "<br>Non<input type='radio' name='ouiNon' value='non'>";
            $content .= '<div class="form-group row"><div class="col-sm-10">';
            $content .= '<button type="submit" class="btn btn-primary" style="background-color:#fc5200; border-color:#fc5200; ">Confirmer</button>';
            $content .= '</div></div></form>';
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Confirmer de quitter le groupe
     */
    public function confirQuitter()
    {
        if ($this->storage->isInGroupe($this->request->getGetParam('id'))) {
            if ($_POST['ouiNon'] == 'oui') {
                $this->storage->quitterGroupe($this->request->getGetParam('id'));
                $this->outils->POSTredirect('?o=groupe&a=groupes', 'Vous avez quitté un groupe');
            }else{
                $this->outils->POSTredirect('?o=groupe&a=groupes', 'Action annulée');
            }
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Affichage d'un groupe selon l'utilisateur
     */
    public function show()
    {
        $id = $this->request->getGetParam('id');
        if ($this->storage->isSportif($_SESSION['user']['athlete']['id'])) {
            if ($this->storage->isInGroupe($id)) {
                $this->showGroupe();
            } else {
                $this->showUnknownGroupe();
            }
        } else {
            $this->showMyGroupe();
        }
    }

    /**
     * Affichage d'un groupe (Pour un athlète déjà inscrit dans ce groupe)
     */
    public function showGroupe()
    {
        $id = $this->request->getGetParam('id');
        $groupe = $this->storage->getGroupe($id);
        $coach = $this->storage->getUser($groupe->getIdU());
        $title = "Groupe de " . $coach->getNom();
        $content = '<div class="row">';
        $content .= '<div class="col-sm-8">';
        $content .= '<h2 class="text-center">Groupe : ' . $groupe->getNom() . '</h2>';
        $content .= '<div class="col">';
        $content .= '<div class="card-body">';
        $content .= '<p>Description du groupe : ' . $groupe->getDescription() . '</p>';
        $content .= '<a href="?o=groupe&a=quitter&id=' . $id . '" class="btn btn-link" style="color: #fc5200;">Quitter le groupe</a>';
        $content .= '</div> </div>';
        //activites
        $content .= '<div class="col">';
        $activites = $this->storage->getActivitesByGroupe($id);
        foreach ($activites as $key => $value) {
            $time = ($value->getElapsedTime()) / 60;
            $allure = ($time / ($value->getDistance())) * 60;
            $nbComments = count($this->storage->getCommentaires($value->getIdAc()));
            $athlete = $this->storage->getUser($value->getIdu());
            $content .= '<div class="card">';
            $content .= '<div class="card-body">';
            $content .= ' <a href="?o=athlete&a=show&id=' . $value->getIdU() . '" class="float-right text-dark" style="text-decoration: none;">' . $athlete->getPrenom()
                . ' <img src="' . $athlete->getImageUrl() . '" class="rounded" width="50" height="50" alt="image athlete"></a>';
            $content .= '<h5 class="card-title">' . $value->getNom() . '</h5>';
            $content .= '<p class="card-text">Description : ' . $value->getDescription() . '</p>';
            $content .= '<p class="card-text">Distance : ' . $value->getDistance() . ' Km</p>';
            $content .= '<p class="card-text">Durée :' . date('H:i:s', $value->getElapsedTime()) . '</p>';
            $content .= '<p class="card-text">Allure :' . date('i:s', $allure) . '/Km</p>';
            $content .= '<p class="card-text">Date : ' . date('Y-m-d H:i', strtotime($value->getDate())) . '</p>';
            $content .= '<a href="?o=commentaire&a=show&idAc=' . $value->getIdAc() . '" class="btn btn-link" style="color: #fc5200;">Commenter</a>';
            $content .= '<small class="float-right">' . $nbComments . ' commentaire(s)</small>';
            $content .= '</div> </div>';
            $content .= '<br>';
        }
        $content .= '</div>';
        $content .= '</div>';
        $content .= '<div class="col-sm-4">';
        $content .= '<p class="text-center">Membres</p>';
        $content .= '<div class="card">';
        $content .= '<ul class="list-group list-group-flush">';
        $content .= '<li class="list-group-item" style="background-color: gold;"> <img src="' . $coach->getImageUrl() . '" class="rounded" alt="athlete img" width="30" height="30"> ' . $coach->getNom() . ' <small style="color: #fc5200;">Coach</small> </li>';
        $ids = $this->storage->getGroupeMembres($id);
        foreach ($ids as $key => $value) {
            $user = $this->storage->getUser($value);
            $content .= '<li class="list-group-item"> <img src="' . $user->getImageUrl() . '" class="rounded" alt="image" width="30" height="30">' . $user->getPrenom() . '</li>';

        }
        $content .= '</ul>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    /**
     * Affichage d'un groupe (Pour un athlète NON inscrit dans ce groupe)
     */
    public function showUnknownGroupe()
    {
        $id = $this->request->getGetParam('id');
        $groupe = $this->storage->getGroupe($id);
        $coach = $this->storage->getUser($groupe->getIdU());
        $title = "Groupe de " . $coach->getNom();
        $content = '<div class="row">';
        $content .= '<div class="col-sm-8">';
        $content .= '<h2 class="text-center">Groupe : ' . $groupe->getNom() . '</h2>';
        $content .= '<div class="col">';
        $content .= '<div class="card-body">';
        $content .= '<p>Description du groupe : ' . $groupe->getDescription() . '</p>';
        $content .= '<a href="?o=groupe&a=rejoindre&id=' . $id . '" class="btn btn-link" style="color: #fc5200;">Rejoindre le groupe</a>';
        $content .= '</div> </div> </div>';
        $content .= '<div class="col-sm-4">';
        $content .= '<p class="text-center">Membres</p>';
        $content .= '<div class="card">';
        $content .= '<ul class="list-group list-group-flush">';
        $content .= '<li class="list-group-item" style="background-color: gold;"> <img src="' . $coach->getImageUrl() . '" class="rounded" alt="image" width="30" height="30"> ' . $coach->getNom() . ' <small style="color: #fc5200;">Coach</small> </li>';
        $ids = $this->storage->getGroupeMembres($id);
        foreach ($ids as $key => $value) {
            $user = $this->storage->getUser($value);
            $content .= '<li class="list-group-item"> <img src="' . $user->getImageUrl() . '" class="rounded" alt="image" width="30" height="30">' . $user->getPrenom() . '</li>';

        }
        $content .= '</ul>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    /**
     * Affichage d'un groupe (Pour un coach qui possède ce groupe)
     */
    public function showMyGroupe()
    {
        if ($this->storage->isCoachOfGroupe( $this->request->getGetParam('id'))) {
            $res = $this->storage->getActivitesByGroupe($this->request->getGetParam('id'));
            $id = $this->request->getGetParam('id');
            $groupe = $this->storage->getGroupe($id);
            $title = "Activités du groupe: " . $groupe->getNom();
            $content = "";
            $content .= '<div class="row"> <div class="col-sm-8">';
            $content .= '<h2 class="text-center">Plan d\'entrainement :' . $groupe->getNom() . '</h2> <div class="col">';
            foreach ($res as $key => $value) {
                $athlete = $this->storage->getUser($value->getIdU());
                $img = $athlete->getImageUrl();
                $nom = $athlete->getPrenom();
                $time = ($value->getElapsedTime()) / 60;
                $allure = ($time / ($value->getDistance())) * 60;
                $nbComments = count($this->storage->getCommentaires($value->getIdAc()));
                $content .= '<div class="col-sm-12"> <div class="card"> <div class="card-body">';
                $content .= '<a href="?o=athlete&a=show&id=' . $value->getIdU() . '" class="float-right text-dark" style="text-decoration: none;">' . $nom . '
            <img src="' . $img . '" class="rounded" alt="image" width="50" height="50"></a>';
                $content .= '<h5 class="card-title">' . $value->getNom() . '</h5>';
                $content .= '<p class="card-text">Description : ' . $value->getDescription() . '</p>';
                $content .= '<p class="card-text">Distance : ' . $value->getDistance() . ' Km</p>';
                $content .= '<p class="card-text">Durée :' . date('H:i:s', $value->getElapsedTime()) . '</p>';
                $content .= '<p class="card-text">Allure :' . date('i:s', $allure) . '/Km</p>';
                $content .= '<p class="card-text">Date : ' . date('Y-m-d H:i', strtotime($value->getDate())) . '</p>';
                $content .= '<a href="?o=commentaire&a=show&idAc=' . $value->getIdAc() . '" class="btn btn-link" style="color: #fc5200;">Commenter</a>';
                $content .= '<small class="float-right">' . $nbComments . ' commentaire(s)</small>';
                $content .= ' </div> </div> </div> <br>';
            }
            $content .= '</div> </div>';
            $content .= '<div class="col-sm-4"> <p class="text-center">Membres</p> <div class="card">  <ul class="list-group list-group-flush">';
            //groupes membres
            $ids = $this->storage->getGroupeMembres($id);
            foreach ($ids as $key => $value) {
                $user = $this->storage->getUser($value);
                $content .= '<li class="list-group-item"> <img src="' . $user->getImageUrl() . '" class="rounded" alt="image" width="30" height="30">' . $user->getPrenom() . '</li>';
            }
            $content .= '</ul> <a href="?o=groupe&a=inviter&id=' . $id . '" style="background-color: #fc5200; border-color: #fc5200;" class="btn btn-primary">Inviter/Supprimer un athlète</a> </div> </div> </div>';
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Une fonction qui permet a un athlète de rejoindre un Groupe
     */
    public function rejoindre()
    {
        if ($this->storage->isSportif($_SESSION['user']['athlete']['id'])) {
            $id = $this->request->getGetParam('id');
            $this->storage->adherer($id);
            $this->outils->POSTredirect('?o=groupe&a=show&id=' . $id, 'vous venez de rejoindre un nouveau groupe');
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Formulaire qui permet a un Coach de chercher un athlète afin de l'integrer/supprimer de son groupe
     */
    public function inviter()
    {
        if ($this->storage->isCoachOfGroupe( $this->request->getGetParam('id'))) {
            $title = "inviter";
            $content = '<form class="container" method="get"> <div class="form-group"> <input type="hidden" name="id" value="' . $_GET['id'] . '"> <input type="hidden" name="o" value="groupe"> <input type="hidden" name="a" value="confInvit"> <input type="text" name="nomU" class="form-control" placeholder="Nom/Prénom Athlète">';
            $content .= '</div> <button type="submit" class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;">Chercher</button> <a class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;" href="?id=' . $_GET['id'] . '&o=groupe&a=confInvit&nomU=">Tous les athlètes</a></form>';
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Résultat de recherche des athlètes
     */
    public function confInvit()
    {
        if ($this->storage->isCoachOfGroupe( $this->request->getGetParam('id'))) {
            $title = "inviter";
            $content = '';
            $mot = $this->request->getGetParam('nomU');
            $id = $this->request->getGetParam('id');
            $res = $this->storage->chercherAthlete($mot);
            $content .= '<div class="container">';
            foreach ($res as $key => $value) {
                $content .= '<div class="col-sm-12">';
                $content .= '<div class="card">';
                $content .= '<div class="card-body">';
                $content .= '<a href="?o=athlete&a=show&id=' . $value->getIdU() . '"><img src="' . $value->getImageUrl() . '" class="float-right" alt="image" width="50" height="50"> </a>';
                $content .= '<h5 class="card-title">' . $value->getPrenom() . ' ' . $value->getNom() . '</h5>';
                if ($this->storage->athleteisInGroupe($value->getIdU(), $id)) {
                    $content .= '<a href="?o=groupe&a=supprAt&idG=' . $id . '&idU=' . $value->getIdU() . '" class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;">Supprimer</a>';
                } else {
                    $content .= '<a href="?o=groupe&a=ajout&idG=' . $id . '&idU=' . $value->getIdU() . '" class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;">Inviter</a>';
                }
                $content .= '</div></div>';
                $content .= '</div>';
            }
            $content .= '</div>';
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Suppresion d'un athète d'un groupe (par un Coach)
     */
    public function supprAt()
    {
        if ($this->storage->isCoachOfGroupe( $this->request->getGetParam('idG'))) {
            $idG = $this->request->getGetParam('idG');
            $idU = $this->request->getGetParam('idU');
            if ($this->storage->getCoachGroupe($idG) == $_SESSION['user']['athlete']['id']) {
                $this->storage->supprimerAthlete($idU, $idG);
                $this->outils->POSTredirect('?o=groupe&a=show&id=' . $idG, 'Athlète supprimé');
            } else {
                $this->outils->POSTredirect('.', 'Erreur');
            }
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * L'ajout d'un athlète  dans un groupe (par un Coach)
     */
    public function ajout()
    {
        if ($this->storage->isCoachOfGroupe( $this->request->getGetParam('idG'))) {
            $idg = $this->request->getGetParam('idG');
            $idu = $this->request->getGetParam('idU');
            $this->storage->ajouterAthleteGrp($idg, $idu);
            $this->outils->POSTredirect('?o=groupe&a=show&id=' . $idg, 'Athlète ajouté');
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }

    /**
     * Rèsulat de recherche d'un groupe (BARRE DE NAVIGATION)
     */
    public function recherche()
    {
        $title = "Résulat de la recherche";
        $res = $this->storage->rechercheGroupe($this->request->getGetParam('mot'));
        $content = '<div class="container"> <h2 class="text-center">Résulat de la recherche</h2> <div class="col">';
        foreach ($res as $key => $value) {
            $number = count($this->storage->getGroupeMembres($key));
            $coach = $this->storage->getUser($value->getIdU());
            $content .= '<div class="col-sm-12"> <div class="card"> <div class="card-body">';
            $content .= '<a href="" class="float-right text-dark" style="text-decoration: none;">' . $coach->getPrenom() . '
            <img src="' . $coach->getImageUrl() . '" class="rounded" alt="image" width="50" height="50"></a>';
            $content .= '<h5 class="card-title">' . $value->getNom() . '</h5>';
            $content .= '<p class="card-text">' . $value->getDescription() . '</p>';
            $content .= '<a href="?o=groupe&a=show&id=' . $key . '" class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;">Voir</a>';
            $content .= '<small class="float-right">' . $number . ' membre</small>';
            $content .= ' </div></div> </div> <br>';
        }
        $content .= '</div></div>';
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);

    }

    /**
     * liste des groupes d'un athlète
     */
    public function groupes(){
        if ($this->storage->isSportif($_SESSION['user']['athlete']['id'])) {
            $title = "Mes groupes";
            $res = $this->storage->getAthleteGroupes();
            $content = '<div class="container"> <h2 class="text-center">Mes groupes</h2> <div class="col">';
            foreach ($res as $key => $value) {
                $number = count($this->storage->getGroupeMembres($key));
                $coach = $this->storage->getUser($value->getIdU());
                $content .= '<div class="col-sm-12"> <div class="card"> <div class="card-body">';
                $content .= '<a href="" class="float-right text-dark" style="text-decoration: none;">' . $coach->getPrenom() . '
            <img src="' . $coach->getImageUrl() . '" class="rounded" alt="image" width="50" height="50"></a>';
                $content .= '<h5 class="card-title">' . $value->getNom() . '</h5>';
                $content .= '<p class="card-text">' . $value->getDescription() . '</p>';
                $content .= '<a href="?o=groupe&a=show&id=' . $key . '" class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;">Voir</a>';
                $content .= '<small class="float-right">' . $number . ' membre</small>';
                $content .= ' </div></div> </div> <br>';
            }
            $content .= '</div></div>';
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        }else{
            $this->view->setPart('title','Forbidden page');
            $this->view->setPart('content',$this->outils->forbiddenPage());
        }
    }


    public function defaultAction()
    {
    }


}


?>