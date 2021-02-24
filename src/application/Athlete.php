<?php
namespace Djs\Application;
/* Représente un poème. */
class Athlete {

	protected $idU;
	protected $nom;
	protected $prenom;
	protected $weight;
	protected $type;

	public function __construct($idU, $nom, $prenom, $weight,$type) {
		$this->idU=$idU;
		$this->nom=$nom;
		$this->prenom=$prenom;
		$this->weight=$weight;
		$this->type=$type;
	}



}

?>
