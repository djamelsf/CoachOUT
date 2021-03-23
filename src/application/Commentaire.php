<?php

namespace Djs\Application;

class Commentaire{

    private $texte;
    private $date;
    private $idU;
    private $idAc;

    /**
     * Commentaire constructor.
     * @param $texte
     * @param $date
     * @param $idU
     * @param $idAc
     */
    public function __construct($texte, $date, $idU, $idAc)
    {
        $this->texte = $texte;
        $this->date = $date;
        $this->idU = $idU;
        $this->idAc = $idAc;
    }



    /**
     * @return mixed
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * @param mixed $texte
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
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
    public function getIdAc()
    {
        return $this->idAc;
    }

    /**
     * @param mixed $idAc
     */
    public function setIdAc($idAc)
    {
        $this->idAc = $idAc;
    }




}


?>