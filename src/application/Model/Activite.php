<?php
namespace Djs\Application\Model;

class Activite{

    protected $idAc;
    protected $nom;
    protected $description;
    protected $distance;
    protected $date;
    protected $elapsed_time;
    protected $idU;
    protected $time;

    /**
     * Activite constructor.
     * @param $idAc
     * @param $nom
     * @param $description
     * @param $distance
     * @param $date
     */
    public function __construct($idAc, $nom, $description, $distance, $date,$elapsed_time,$idU,$time)
    {
        $this->idAc = $idAc;
        $this->nom = $nom;
        $this->description = $description;
        $this->distance = $distance;
        $this->date = $date;
        $this->elapsed_time=$elapsed_time;
        $this->idU=$idU;
        $this->time=$time;
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

    /**
     * @return mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param mixed $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param mixed $distance
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
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
    public function getElapsedTime()
    {
        return $this->elapsed_time;
    }

    /**
     * @param mixed $elapsed_time
     */
    public function setElapsedTime($elapsed_time)
    {
        $this->elapsed_time = $elapsed_time;
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
     * @return false|string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param false|string $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }










}




?>