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
        $req = $this->bdd->prepare('INSERT INTO Personnages_v2
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
        $req = $this->bdd->prepare('UPDATE Personnages_v2
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
        $this->bdd->exec('DELETE FROM Personnages_v2
                                 WHERE id = ' . $perso->getId());
    }
    
    public function getPersonnage($info) {
        if (is_int($info)) {
            $req = $this->bdd->query('SELECT id, nom, degats, timeToBeAsleep, type, atout
                                         FROM Personnages_v2
                                        WHERE id = ' . $info);
            $datasOfPerso = $req->fetch(PDO::FETCH_ASSOC);
        }
        else {
            $req = $this->bdd->prepare('SELECT id, nom, degats, timeToBeAsleep, type, atout
                                           FROM Personnages_v2
                                          WHERE nom = :nom');
            $req->execute([':nom' => $info]);
            
            $datasOfPerso = $req->fetch(PDO::FETCH_ASSOC);
        }
        
        switch ($datasOfPerso['type']) {
            case 'guerrier' : return new Guerrier($datasOfPerso);
            case 'magicien' : return new Magicien($datasOfPerso);
            default : return null;
        }
        
        $req->closeCursor();
    }
    
    public function getListPersonnages($nom) {
        $persos = [];
        
        $req = $this->bdd->prepare('SELECT id, nom, degats, timeToBeAsleep, type, atout
                                      FROM Personnages_v2
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
                                     FROM Personnages_v2')->fetchColumn();
    }
    
    public function ifPersonnageExist($info) {
        if (is_int($info)) {
            return (bool) $this->bdd->query('SELECT COUNT(*)
                                                FROM Personnages_v2
                                               WHERE id = ' . $info)->fetchColumn();
        }
        $req = $this->bdd->prepare('SELECT COUNT(*)
                                       FROM Personnages_v2
                                      WHERE nom = :nom');
        $req->execute([':nom' => $info]);
        return (bool) $req->fetchColumn();
        
        $req->closeCursor();
    }
}