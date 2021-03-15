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

    public function createActivite(Activite $activite)
    {
        $rq = "INSERT INTO activite (idAc,nom,description,distance,date,elapsed_time,idU) VALUES (:idAc,:nom,:description,:distance,:date,:elapsed_time,:idU)";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idAc' => $activite->getIdAc(),
            ':nom' => $activite->getNom(),
            ':description' => $activite->getDescription(),
            ':distance' => $activite->getDistance(),
            ':date' => $activite->getDate(),
            ':elapsed_time' => $activite->getElapsedTime(),
            ':idU' => $_SESSION['user']['athlete']['id'],
        );
        $t=$stmt->execute($data);
        if ($t) {
            return true;
        }else{
            return false;
        }
    }

    public function createGroupe(Groupe $groupe)
    {
        $rq = "INSERT INTO groupe (nom,description,idU) VALUES (:nom,:description,:idU)";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':nom' => $groupe->getNom(),
            ':description' => $groupe->getDescription(),
            ':idU' => $_SESSION['user']['athlete']['id'],
        );
        $t=$stmt->execute($data);
        if ($t) {
            return $this->connexion->lastInsertId();
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

	public function getMyActivites($id)
    {
        $rq = "SELECT * FROM activite WHERE idU= :idU";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idU' => $id,
        );
        $stmt->execute($data);
        $tab=[];
        while ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
            array_push($tab, new Activite($setup['idAc'],$setup['nom'],$setup['description'],$setup['distance'],$setup['date'],$setup['elapsed_time']));
        }

        print_r($tab);
        return $tab;
    }

    public function getMyGroupes($id)
    {
        $rq = "SELECT * FROM groupe WHERE idU= :idU";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idU' => $id,
        );
        $stmt->execute($data);
        $tab=[];
        while ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $tab[$setup['idG']]=new Groupe($setup['nom'],$setup['description']);
        }

        return $tab;
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