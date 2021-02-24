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

	public function read($id){
		$rq = "SELECT * FROM Game WHERE idG= :id";
		$stmt = $this->connexion->prepare($rq);
		$data = array(
         ':id' => $id,
        );
		$stmt->execute($data);
		return $this->hydrate($stmt);
	}

	public function readByUser($user){
		$rq = "SELECT * FROM Game WHERE user= :user";
		$stmt = $this->connexion->prepare($rq);
		$data = array(
         ':user' => $user,
        );
		$stmt->execute($data);
		return $this->hydrate($stmt);
	}

	public function isAthlete($id){
	    //
    }

	public function existsAthlete($id){
		$rq = "SELECT * FROM user WHERE idU= :id";
		$stmt = $this->connexion->prepare($rq);
		$data = array(
         ':id' => $id,
        );
		$stmt->execute($data);
		$rs=$this->hydrate($stmt);
		if (empty($rs)) {
			return false;
		}else{
			return true;
		}
	}


	public function readAll(){
		$rq = "SELECT * FROM Game";
		$stmt = $this->connexion->prepare($rq);
		$stmt->execute();
        return $this->hydrate($stmt);
	}

	public function create(Game $a,$user){
		$rq = "INSERT INTO Game (nom,type,disc,prix,img,user) VALUES (:nom,:type,:disc,:prix, :img, :user)";
		$stmt = $this->connexion->prepare($rq);
		$data = array(
         ':nom' => $a->getNom(),
         ':type' => $a->getType(),
         ':disc' => $a->getDisc(),
         ':prix' => $a->getPrix(),
         ':img' => $a->getImg(),
         ':user' => $user,
        );
        $t=$stmt->execute($data);
        if ($t) {
        	return $this->connexion->lastInsertId();
        }
	}

	public function hydrate($stmt){
		$tab=[];
		while($setup = $stmt->fetch(\PDO::FETCH_ASSOC)){
			//print_r($setup);
			$tab[$setup['idU']]=new Athlete($setup['idU'],$setup['nom'],$setup['prenom'],$setup['weight'],$setup['type']);
		}
		print_r($tab);
		return $tab;
	}

	public function getUser($id){
		$rq = "SELECT * FROM Game WHERE idG= :id";
		$stmt = $this->connexion->prepare($rq);
		$data = array(
         ':id' => $id,
        );
		$stmt->execute($data);
		//return $this->hydrate($stmt);
		$rs;
		if($setup = $stmt->fetch(PDO::FETCH_ASSOC)){
			//print_r($setup);
			$rs=$setup['user'];
		}
		return $rs;
	}

	public function delete($id){
		$rq = "DELETE FROM Game WHERE idG= :id";
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

	public function update($id, Game $c){
		$rq = "UPDATE Game SET nom= :nom, type= :type, disc= :disc, prix= :prix, img= :img WHERE idG= :id";
		$stmt = $this->connexion->prepare($rq);
		$data = array(
         ':nom' => $c->getNom(),
         ':type' => $c->getType(),
         ':disc' => $c->getDisc(),
         ':prix' => $c->getPrix(),
         ':img' => $c->getImg(),
         ':id' => $id,
        );
        if ($stmt->execute($data)) {
        	return true;
        }else{
        	return false;
        }
	}

}

 ?>