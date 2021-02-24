<?php

namespace Djs\Framework;
use Djs\Application\AutenticationManager;
use Djs\Application\AthleteController;

class FrontController
{
    /**
     * request et response
     */
    protected $request;
    protected $response;
    protected $post;
    protected $autenticationManager;
    protected $storage;


    /**
     * constructeur de la classe.
     */
    public function __construct($request, $response,$storage)
    {
        $this->request = $request;
        $this->response = $response;
        $this->post=$_POST;
        $this->autenticationManager=new AutenticationManager();
        $this->storage=$storage;
    }

    /**
     * méthode pour lancer le contrôleur et exécuter l'action à faire
     */
    public function execute()
    {
    	$view = new View('application/templates/template.php');
   	
        // demander au Router la classe et l'action à exécuter
        $router = new Router($this->request);
        $className = $router->getControllerClassName();
        $action = $router->getControllerAction();

        // instancier le controleur de classe et exécuter l'action
        $controller = new $className($this->request, $this->response, $view,$this->autenticationManager,$this->storage);
        $controller->execute($action);
        
        if ($this->request->isAjaxRequest()) {
        	$content = $view->getPart('content');
        } else {
        	$content = $view->render();
        }
        
       $this->response->send($content);
    }
}

?>