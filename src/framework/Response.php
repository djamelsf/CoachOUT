<?php
namespace Djs\Framework;
use Djs\Application;
/**
 * Class Response
 *
 * embryon de classe pour gérer la réponse HTTP
 */
class Response
{
    /**
     * @var array
     * liste des en-têtes HTTP
     */
	private $headers = array();

    /**
     * @param $headerValue
     * ajouter un en-tête à la liste
     * par exemple pour changer le Content-Type
     */
	public function addHeader($headerValue) {
		$this->headers[] = $headerValue;
	}

    /**
     * envoie tous les headers au client
     * @todo utilise la liste dans l'ordre où les en-têtes ont été ajoutés ce qui peut devenir incohérent
     */
	public function sendHeaders() {
		foreach ($this->headers as $header) {
			header($header);
		}
	}

    /**
     * @param $content
     * envoi de la réponse au client
     */
	public function send($content)
	{
		$this->sendHeaders();
		echo $content;
	}  
}

?>