<?php
namespace Djs\Framework;
use \Exception;
use Djs\Application\AthleteController;


class Router
{
    protected $controllerClassName;
    protected $controllerAction;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->parseRequest();
    }

    public function getControllerClassName()
    {
        return $this->controllerClassName;
    }

    public function getControllerAction()
    {
        return $this->controllerAction;
    }

    protected function parseRequest()
    {
      // un nom de package est-il spécifié dans l'URL ?
        $package = $this->request->getGetParam('o');

        // Regarder quel contrôleur instancier
        switch ($package) {
            case 'poem':
                $this->controllerClassName = 'Djs\Application\AthleteController';
                break;
            /** exemple pour plus tard
            case 'image':
                $this->controllerClassName = 'ImageController';
                break;
             */

            default:
                // idem ici, on peut imaginer un package à utiliser par défaut
                // j'utilise ArticleController pour l'instant car c'est le seul existant
                $this->controllerClassName = 'Djs\Application\AthleteController';
        }

        // tester si la classe à instancier existe bien. Si non lancer une Exception.
        if (!class_exists($this->controllerClassName)) {
            throw new Exception("Classe {$this->controllerClassName} non existante");
        }

        // regarder si une action est demandée dans l'URL
        // si le paramètre 'a' n'existe pas alors l'action sera 'defaultAction'

        $action=$this->request->getGetParam('code', 'defaultAction');
        if($action!='defaultAction'){
            $this->controllerAction='Oauth';
        }else{
            $this->controllerAction = $this->request->getGetParam('a', 'defaultAction');
        }
	}
}

?>