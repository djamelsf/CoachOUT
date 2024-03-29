<?php
namespace Djs\Application\Model;

class Athlete {

	protected $idU;
	protected $nom;
	protected $prenom;
	protected $weight;
	protected $type;
	protected $imageUrl;

	public function __construct($idU, $nom, $prenom, $weight,$type,$imageUrl) {
		$this->idU=$idU;
		$this->nom=$nom;
		$this->prenom=$prenom;
		$this->weight=$weight;
		$this->type=$type;
		$this->imageUrl=$imageUrl;
	}

	/**
	 * @return mixed
	 */
	public function getIdU()
	{
		return $this->idU;
	}

	/**
	 * @param mixed $idU
	 */
	public function setIdU($idU)
	{
		$this->idU = $idU;
	}

	/**
	 * @return mixed
	 */
	public function getNom()
	{
		return $this->nom;
	}

	/**
	 * @param mixed $nom
	 */
	public function setNom($nom)
	{
		$this->nom = $nom;
	}

	/**
	 * @return mixed
	 */
	public function getPrenom()
	{
		return $this->prenom;
	}

	/**
	 * @param mixed $prenom
	 */
	public function setPrenom($prenom)
	{
		$this->prenom = $prenom;
	}

	/**
	 * @return mixed
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * @param mixed $weight
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param mixed $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function getImageUrl()
	{
		return $this->imageUrl;
	}

	/**
	 * @param mixed $imageUrl
	 */
	public function setImageUrl($imageUrl)
	{
		$this->imageUrl = $imageUrl;
	}







}

?>
