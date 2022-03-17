<?php
abstract class Personnage {
 
    protected   $id,
                $nom,
                $degats,
                $niveau,
                $timeToBeAsleep,
                $type,
                $atout;
    
    const DETECT_ME     = 1; // renvoyée par la méthode frapperUnPersonnage - détecte si on se frappe soi-même
    const PERSO_DEAD    = 2; // renvoyée par la méthode frapperUnPersonnage - détecte si un personnage est tué en le frappant
    const PERSO_COUP    = 3; // renvoyée par la méthode frapperUnPersonnage - détecte si un coup est bien porté à un personnage
    const PERSO_ENVOUTE = 4; // renvoyée par la méthode lancerUnSort - détecte si le sort est bien lancé
    const NO_MAGIE      = 5; // renvoyée par la méthode lancerUnSort - détecte si magie du magicien à 0
    const PERSO_ASLEEP  = 6; // renvoyé par la méthode frapperUnPersonnage - détecte si le personnage qui veut frapper est endormi


    public function __construct(array $datas) {
        $this->hydrate($datas);
        $this->type = strtolower(static::class);
    }
    
    public function hydrate(array $datas) {
        foreach ($datas as $key => $value) {
            $method = 'set'.ucfirst($key);
            
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
    
    public function frapperUnPersonnage(Personnage $persoAFrapper) {
        if ($persoAFrapper->getId() == $this->id) {
            return self::DETECT_ME;
        }
        
        if ($this->toBeAsleep()) {
            return self::PERSO_ASLEEP;
        }
        
        return $persoAFrapper->recevoirUnCoup();
    }
    
    public function recevoirUnCoup() {
        $this->degats += 5;
        
        if ($this->degats >= 100) {
            return self::PERSO_DEAD;
        }
        
        // Le personnage reçoit un coup
        return self::PERSO_COUP;
    }
    
    public function validName() {
        return !empty($this->nom);
    }

    public function toBeAsleep() {
        return $this->timeToBeAsleep > time();
    }
    
    public function reveil() {
        $secondes = $this->timeToBeAsleep;
        $secondes -= time();
        
        $heures = floor($secondes / 3600);
        $secondes -= $heures * 3600;
        $minutes = floor($secondes / 60);
        $secondes -= $minutes * 60;
        
        $heures .= $heures <= 1 ? ' heure' : ' heures';
        $minutes .= $minutes <= 1 ? ' minute' : ' minutes';
        $secondes .= $secondes <= 1 ? ' seconde' : ' secondes';
        
        return $heures . ', ' . $minutes . ' et ' . $secondes;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getNom() {
        return $this->nom;
    }

    public function getNiveau()
    {
        return $this->niveau;
    }
    
    public function getDegats() {
        return $this->degats;
    }
    
    public function getTimeToBeAsleep() {
        return $this->timeToBeAsleep;
    }
    
    public function getType() {
        return $this->type;
    }
    
    public function getAtout() {
        return $this->atout;
    }
    
     public function setId($id) {
         $this->id = (int)$id;
     }
     
     public function setNom($nom) {
         if (is_string($nom)) {
             $this->nom = $nom;
         }
     }

     public function setNiveau($niveau)
     {
         $niveau = (int) $niveau;

         if($niveau >= 1)
         {
             $this->niveau = $niveau;
         }
     }
     
     public function setDegats($degats) {
         $degats = (int)$degats;
         if ($degats >= 0 && $degats <= 100) {
             $this->degats = $degats;
         }
     }
     
     public function setTimeToBeAsleep($time) {
         $this->timeToBeAsleep = (int) $time;
     }
     
     public function setAtout($atout) {
         $atout = (int) $atout;
         
         if ($atout >= 0 && $atout <= 100) {
             $this->atout = $atout;
         }
     }
    
}