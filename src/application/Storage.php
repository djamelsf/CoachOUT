<?php
namespace Djs\Application;
/* Interface représentant un système de stockage des poèmes. */
interface Storage {
	/* Renvoie l'instance de Athlete correspondant à l'identifiant donné,
	 * ou null s'il n'y en a pas. */
	public function read($id);

	/* Renvoie un tableau associatif id=>poème avec tous les poèmes de la base. */
	public function readAll();

	public function existsAthlete($id);
}

?>
