<?php
namespace Djs\Application;
/**
 * 
 */
class StorageMySQL implements Storage {

	public $connexion;
	
	function __construct($connexion){
		$this->connexion=$connexion;		
	}

    public function createAthlete(Athlete $athlete)
    {
        $rq = "INSERT INTO user (idU,nom,prenom,weight,type) VALUES (:id,:nom,:prenom,:weight,:type)";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':id' => $athlete->getIdU(),
            ':nom' => $athlete->getNom(),
            ':prenom' => $athlete->getPrenom(),
            ':weight' => $athlete->getWeight().'',
            ':type' => $athlete->getType(),
        );
        $t=$stmt->execute($data);
        if ($t) {
            return true;
        }else{
            return false;
        }
    }


    public function isCoach($id){
        $rq = "SELECT * FROM user WHERE idU= :id AND type= 'coach'";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':id' => $id,
        );
        $stmt->execute($data);
        $result = $stmt->fetchAll();
        if (empty($result)) {
            return 0;
        }else{
            return 1;
        }
    }

    public function isSportif($id){
        $rq = "SELECT * FROM user WHERE idU= :id AND type= 'sportif'";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':id' => $id,
        );
        $stmt->execute($data);
        $result = $stmt->fetchAll();
        if (empty($result)) {
            return 0;
        }else{
            return 1;
        }
    }


	public function existsAthlete($id){
        $rq = "SELECT * FROM user WHERE idU= :idU";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idU' => $id,
        );
        $stmt->execute($data);
        $result = $stmt->fetchAll();
        if (empty($result)) {
            return 0;
        }else{
            return 1;
        }

	}




	public function hydrate($stmt){
		$tab=[];
		while ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
			$tab[$setup['idU']]=new Athlete($setup['idU'],$setup['nom'],$setup['prenom'],$setup['weight'],$setup['type']);
		}
		return $tab;
	}



}

 ?>