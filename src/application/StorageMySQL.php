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
        $rq = "INSERT INTO user (idU,nom,prenom,weight,type,imageUrl) VALUES (:id,:nom,:prenom,:weight,:type,:imageUrl)";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':id' => $athlete->getIdU(),
            ':nom' => $athlete->getNom(),
            ':prenom' => $athlete->getPrenom(),
            ':weight' => $athlete->getWeight().'',
            ':type' => $athlete->getType(),
            ':imageUrl' => $athlete->getImageUrl(),
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
        $rq = "INSERT INTO activite (idAc,nom,description,distance,date,elapsed_time,idU,time) VALUES (:idAc,:nom,:description,:distance,:date,:elapsed_time,:idU,:time)";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idAc' => $activite->getIdAc(),
            ':nom' => $activite->getNom(),
            ':description' => $activite->getDescription(),
            ':distance' => $activite->getDistance(),
            ':date' => $activite->getDate(),
            ':elapsed_time' => $activite->getElapsedTime(),
            ':idU' => $activite->getIdU(),
            ':time' => $activite->getTime(),

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

    public function adherer($idG)
    {
        $rq = "INSERT INTO adhere (idU,idG) VALUES (:idU,:idG)";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idU' => $_SESSION['user']['athlete']['id'],
            ':idG' => $idG,
        );
        $t=$stmt->execute($data);
        if ($t) {
            return $this->connexion->lastInsertId();
        }
    }

    public function isInGroupe($id)
    {
        $rq = "SELECT * FROM adhere WHERE idU= :idU AND idG= :idG";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idU' => $_SESSION['user']['athlete']['id'],
            ':idG' => $id,
        );
        $stmt->execute($data);
        $result = $stmt->fetchAll();
        if (empty($result)) {
            return 0;
        }else{
            return 1;
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

	public function getUser($id)
    {
        $rq = "SELECT * FROM user WHERE idU= :idU";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idU' => $id,
        );
        $stmt->execute($data);
        $user=null;
        if ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $user=new Athlete($id,$setup['nom'],$setup['prenom'],$setup['weight'],$setup['type'],$setup['imageUrl']);
        }
        return $user;
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
            array_push($tab, new Activite($setup['idAc'],$setup['nom'],$setup['description'],$setup['distance'],$setup['date'],$setup['elapsed_time'],$setup['idU'],$setup['time']));
        }

        return $tab;
    }

    public function getActivitesByGroupe($id)
    {
        $rq="SELECT * FROM adhere,activite WHERE adhere.idG= :id AND adhere.idU=activite.idU ORDER BY activite.time DESC";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':id' => $id,
        );
        $stmt->execute($data);
        $tab=[];
        while ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
            array_push($tab, new Activite($setup['idAc'],$setup['nom'],$setup['description'],$setup['distance'],$setup['date'],$setup['elapsed_time'],$setup['idU'],$setup['time']));
        }
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
            $tab[$setup['idG']]=new Groupe($setup['nom'],$setup['description'],$setup['idU']);
        }

        return $tab;
    }

    public function getGroupe($id)
    {
        $rq = "SELECT * FROM groupe WHERE idG= :idG";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idG' => $id,
        );
        $stmt->execute($data);
        $groupe=null;
        if ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $groupe=new Groupe($setup['nom'],$setup['description'],$setup['idU']);
        }
        return $groupe;
    }

    public function getCoachGroupe($id)
    {
        $rq = "SELECT idU FROM groupe WHERE idG= :idG";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idG' => $id,
        );
        $stmt->execute($data);
        $c=null;
        if ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $c=$setup['idU'];
        }
        return $c;
    }

    public function rechercheGroupe($text)
    {
        $rq = "SELECT * FROM groupe WHERE nom LIKE :nom";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':nom' => '%'.$text.'%',
        );
        $stmt->execute($data);
        $tab=[];
        while ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $tab[$setup['idG']]=new Groupe($setup['nom'],$setup['description'],$setup['idU']);
        }

        return $tab;


    }

    public function getActivitesOdered($id)
    {
        $rq="SELECT * FROM activite WHERE idU= :idU ORDER by activite.date ASC";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idU' => $id,
        );
        $stmt->execute($data);
        $labels=[];
        $data=[];
        while ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
            array_push($labels,$setup['date']);
            array_push($data,($setup['distance'])/1000);
        }
        return [$labels,$data];
    }

    public function getGroupeMembres($id)
    {
        $rq="SELECT * FROM adhere WHERE idG= :idG";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idG' => $id,
        );
        $stmt->execute($data);
        $res=[];
        while ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
            array_push($res,$setup['idU']);
        }
        return $res;
    }

    public function supprimerGroupe($id)
    {
        $rq = "DELETE FROM adhere WHERE idG= :id; DELETE FROM groupe WHERE idG= :id;";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':id' => $id,
        );
        if ($stmt->execute($data)) {
            return true;
        }else{
            return false;
        }
    }

    public function supprimerActivite($id)
    {
        $rq = "DELETE FROM activite WHERE idAc= :id";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':id' => $id,
        );
        if ($stmt->execute($data)) {
            return true;
        }else{
            return false;
        }
    }

    public function quitterGroupe($id)
    {
        $rq = "DELETE FROM adhere WHERE idG= :idG AND idU= :idU";
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':idG' => $id,
            ':idU' => $_SESSION['user']['athlete']['id'],
        );
        if ($stmt->execute($data)) {
            return true;
        }else{
            return false;
        }
    }

    public function classementGroupe($id)
    {
        $rq='SELECT * FROM (SELECT SUM(activite.distance) AS activiteSUM, adhere.idU FROM activite,adhere,groupe WHERE activite.idU=adhere.idU AND groupe.idG=adhere.idG AND groupe.idG= :id GROUP BY(adhere.idU)) AS TP ORDER BY(TP.activiteSUM) DESC LIMIT 3';
        $stmt = $this->connexion->prepare($rq);
        $data = array(
            ':id' => $id,
        );
        $stmt->execute($data);
        $tab=[];
        while ($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $tab[$setup['idU']]=$setup['activiteSUM'];
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