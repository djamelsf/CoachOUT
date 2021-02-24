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