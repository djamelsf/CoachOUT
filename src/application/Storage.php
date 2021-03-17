<?php
namespace Djs\Application;

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
}

?>
