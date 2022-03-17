<?php
class PersonnagesManager {

    private $bdd;
    
    public function __construct($bdd) {
        $this->setDb($bdd);
    }
    
    public function setDb(PDO $bdd) {
        $this->bdd = $bdd;
    }
    
    public function addPersonnage($nom, $type) {
        $req = $this->bdd->prepare('INSERT INTO personnage
                                             SET nom    = :nom,
                                                 type   = :type
                                   ');          
        $req->execute(array(
                                "nom"=>$nom,
                                "type"=>$type
                            )
                    );                                                    

        $req->closeCursor();
    }

    public function updatePersonnage(Personnage $perso) {
        $req = $this->bdd->prepare('UPDATE personnage
                                        SET degats          = :degats,
                                            timeToBeAsleep  = :timeToBeAsleep,
                                            atout           = :atout
                                      WHERE id = :id
                                    ');
        $perso->getTimeToBeAsleep(0);
        $req->bindValue(':degats',          $perso->getDegats(),            PDO::PARAM_INT);
        $req->bindValue(':timeToBeAsleep',  $perso->getTimeToBeAsleep(),    PDO::PARAM_INT);
        $req->bindValue(':atout',           $perso->getAtout(),             PDO::PARAM_INT);
        $req->bindValue(':id',              $perso->getId(),                PDO::PARAM_INT);
        $req->execute();
        
        $req->closeCursor();
    }

    public function deletePersonnage(Personnage $perso) {
        $this->bdd->exec('DELETE FROM personnage
                                 WHERE id = ' . $perso->getId());
    }
    
    public function getPersonnage($info) {
        if (is_int($info)) {
            $req = $this->bdd->query('SELECT id, nom, degats, timeToBeAsleep, type, atout
                                         FROM personnage
                                        WHERE id = ' . $info);
            $datasOfPerso = $req->fetch(PDO::FETCH_ASSOC);
        }
        else {
            $req = $this->bdd->prepare('SELECT id, nom, degats, timeToBeAsleep, type, atout
                                           FROM personnage
                                          WHERE nom = :nom');
            $req->execute([':nom' => $info]);
            
            $datasOfPerso = $req->fetch(PDO::FETCH_ASSOC);
        }
        
        switch ($datasOfPerso['type']) {
            case 'guerrier' : 
                return new Guerrier($datasOfPerso);
                break;
            case 'magicien' : 
                return new Magicien($datasOfPerso);
                break;
            default : 
            return null;
                break;

        }
        
        $req->closeCursor();
    }
    
    public function getListPersonnages($nom) {
        $persos = [];
        
        $req = $this->bdd->prepare('SELECT id, nom, degats, timeToBeAsleep, type, atout
                                      FROM personnage
                                     WHERE nom <> :nom
                                     ORDER BY nom');
        $req->execute([':nom' => $nom]);
        
        while ($datas = $req->fetch(PDO::FETCH_ASSOC)) {
            switch ($datas['type']) {
                case 'guerrier' : $persos[] = new Guerrier($datas);
                    break;
                case 'magicien' : $persos[] = new Magicien($datas);
                    break;
            }
        }
        
        return $persos;
        
        $req->closeCursor();
    }
    
    public function countPersonnages() {
        return $this->bdd->query('SELECT COUNT(*)
                                     FROM personnage')->fetchColumn();
    }
    
    public function ifPersonnageExist($info) {
        if (is_int($info)) {
            return (bool) $this->bdd->query('SELECT COUNT(*)
                                                FROM personnage
                                               WHERE id = ' . $info)->fetchColumn();
        }
        $req = $this->bdd->prepare('SELECT COUNT(*)
                                       FROM personnage
                                      WHERE nom = :nom');
        $req->execute([':nom' => $info]);
        return (bool) $req->fetchColumn();
        
        $req->closeCursor();
    }
}