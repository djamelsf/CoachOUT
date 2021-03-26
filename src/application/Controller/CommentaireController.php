<?php

namespace Djs\Application\Controller;

use Djs\Application\AutenticationManager;
use Djs\Application\Model\Commentaire;
use Djs\Application\Outils;
use Djs\Application\Storage;
use Djs\Framework\Request;
use Djs\Framework\Response;
use Djs\Framework\View;

class CommentaireController
{
    protected $request;
    protected $response;
    protected $view;
    protected $autenticationManager;
    protected $storage;
    protected $outils;

    /**
     * CommentaireController constructor.
     * @param $request
     * @param $response
     * @param $view
     * @param $autenticationManager
     * @param $storage
     * @param $outils
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

    public function execute($action)
    {
        if ($this->autenticationManager->isConnected()) {
            if (method_exists($this, $action)) {
                $this->$action();
            } else {
                echo "wrong function";
            }
        } else {
            $this->view->setPart('title', 'Forbidden page');
            $this->view->setPart('content', $this->outils->forbiddenPage());
        }

    }

    public function defaultAction()
    {
    }

    /**
     * Affichage d'une activité avec ses commentaires + zone pour taper un commentaire.
     */
    public function show()
    {
        if ($this->storage->getActivite($this->request->getGetParam('idAc')) != null) {
            $idAc = $this->request->getGetParam('idAc');
            $activite = $this->storage->getActivite($idAc);
            if (!empty($_POST)) {
                $commentaire = new Commentaire(htmlspecialchars($_POST['texte']), date('Y-m-d H:i:s'), $_SESSION['user']['athlete']['id'], $idAc);
                $this->storage->createCommentaire($commentaire);
                $this->outils->POSTredirect('?o=commentaire&a=show&idAc=' . $idAc, 'Message envoyé');
            }
            $time = ($activite->getElapsedTime()) / 60;
            $allure = ($time / ($activite->getDistance())) * 60;
            $title = 'Activité';
            $content = '<div class="card container">';
            $content .= '<div class="card-body">';
            $content .= '<h5 class="card-title">' . $activite->getNom() . '</h5>';
            $content .= '<p class="card-text">Description : ' . $activite->getDescription() . '</p>';
            $content .= '<p class="card-text">Distance : ' . $activite->getDistance() . ' Km</p>';
            $content .= '<p class="card-text">Durée :' . date('H:i:s', $activite->getElapsedTime()) . '</p>';
            $content .= '<p class="card-text">Allure :' . date('i:s', $allure) . '/Km</p>';
            $content .= '<p class="card-text">Date : ' . date('Y-m-d H:i', strtotime($activite->getDate())) . '</p>';
            $content .= '</div></div><br>';
            //formulaire texte
            $content .= '<div class="container"><div class="row"><div class="col-sm-4"><form method="post" action="">';
            $content .= '<div class="form-group"><label>Message</label><textarea name="texte" class="form-control" rows="3" required></textarea>';
            $content .= '</div> <button type="submit" class="btn btn-primary" style="background-color:#fc5200; border-color: #fc5200;">Envoyer</button>';
            $content .= '</form></div>';
            //
            //liste des messages
            $commentaires = $this->storage->getCommentaires($idAc);
            $content .= '<div class="col-sm-8" style="overflow-y: scroll; height:500px; width: auto;">';
            foreach ($commentaires as $key => $value) {
                $user = $this->storage->getUser($value->getIdu());
                $content .= '<div class="card"><div class="card-body">';
                $content .= '<a href="?o=athlete&a=show&id=' . $user->getIdU() . '"><img src="' . $user->getImageUrl() . '"  class="float-left" width="30" height="30" alt="image profil"> </a>';
                $content .= '<h5 class="card-title"> ' . $user->getPrenom() . '</h5>';
                $content .= '<br><p class="card-text">' . $value->getTexte() . '</p>';
                $content .= '<small class="float-right">' . $value->getDate() . '</small>';
                $content .= '</div></div>';
            }
            $content .= '</div>';
            $content .= '</div></div>';
            $this->view->setPart('title', $title);
            $this->view->setPart('content', $content);
        } else {
            $this->view->setPart('title', 'Forbidden page');
            $this->view->setPart('content', $this->outils->forbiddenPage());
        }
    }


}


?>