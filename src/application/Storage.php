<?php
namespace Djs\Application;
/* Interface représentant un système de stockage des poèmes. */
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
}

?>
