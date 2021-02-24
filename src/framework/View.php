<?php
namespace Djs\Framework;
use Djs\Application;
class View 
{
    /**
     * @var array $parts le tableau des parties de HTML qui pourront être utilisés
     */
    protected $parts;
    /**
     * @var string $template le nom du fichier servant de squelette HTML à la page
     */
    protected $template;

    public function __construct($template, $parts = array())
    {
    	$this->template = $template;
        $this->parts = $parts;
    }

    public function setPart($key, $content)
    {
        $this->parts[$key] = $content;
    }

    public function getPart($key)
    {
        if (isset($this->parts[$key])) {
            return $this->parts[$key];
        } else {
            return null;
        }
    }

    /**
     * @return string
     * génère la vue (i.e. la page web) avec les contenus en remplissant les zones définies
     */
    public function render()
    {
        $le_titre = $this->getPart('title');
        $le_contenu = $this->getPart('content');
        $le_menu = $this->getPart('menu');

        ob_start();
        include($this->template);
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }
}

?>