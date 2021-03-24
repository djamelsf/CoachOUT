<?php
namespace Djs\Application;
use Djs\Application\Model\Activite;
use Djs\Application\Model\Athlete;
use Djs\Application\Model\Commentaire;
use Djs\Application\Model\Groupe;

interface Storage {
	/* Renvoie l'instance de Athlete correspondant à l'identifiant donné,
	 * ou null s'il n'y en a pas. */


	public function existsAthlete($id);
	public function isCoach($id);
	public function isSportif($id);
	public function createAthlete(Athlete $athlete);
	public function createActivite(Activite $activite);
	public function getMyActivites($id);
	public function createGroupe(Groupe $groupe);
	public function getMyGroupes($id);
	public function getGroupe($id);
	public function getCoachGroupe($id);
	public function rechercheGroupe($text);
	public function getUser($id);
	public function adherer($idG);
	public function isInGroupe($id);
	public function getActivitesByGroupe($id);
	public function getActivitesOdered($id);
	public function getGroupeMembres($id);
	public function supprimerGroupe($id);
	public function supprimerActivite($id);
	public function quitterGroupe($id);
	public function classementGroupe($id);
	public function chercherAthlete($nom);
	public function ajouterAthleteGrp($idG,$idU);
	public function athleteisInGroupe($id,$idG);
	public function supprimerAthlete($idU,$idG);
	public function getCommentaires($id);
	public function getActivite($id);
	public function createCommentaire(Commentaire $commentaire);
	public function getDistanceTotal($id);
	public function isCoachOfGroupe($id);
	public function isMyActivite($id);
	public function getAthleteGroupes();
}

?>
