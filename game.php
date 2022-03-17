<?php

function chargerMesClasses($classes) {
    require 'classes/' . $classes . '.php';
}

spl_autoload_register('chargerMesClasses');

session_start();

if (isset($_GET['deconnexion'])) {
    session_destroy();
    header('Location: .');
    exit();
}

if (isset($_SESSION['perso'])) {
    $perso = $_SESSION['perso'];
}

$db = new PDO('mysql:host=localhost;dbname=jeu2kombat', 'root', 'root');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$manager = new PersonnagesManager($db);

if (isset($_POST['creer']) && isset($_POST['personnageNom']))
{
    switch ($_POST['personnageType']) {
        case 'magicien' :
            $perso = new Magicien(['nom' => $_POST['personnageNom']]);
            break;
        case 'guerrier' :
            $perso = new Guerrier(['nom' => $_POST['personnageNom']]);
            break;
        default :
            $message = 'Le type du personnage n\'est pas valide';
            unset($perso);
            break;
    }
    
    if(isset($perso))
    {
        if (!$perso->validName())
        {
            $message = 'Le nom choisi n\'est pas valide.';
            unset($perso);
        }
        
        elseif ($manager->ifPersonnageExist($perso->getNom()))
        {
            $message = 'Le nom du personnage est déjà utilisé.';
            unset($perso);
        }
        
        else
        {
            $manager->addPersonnage($perso->getNom(), $perso->getType());
            $message = 'Le personnage est créé.';
        }
    }
}

elseif (isset($_POST['utiliser']) && isset($_POST['personnageNom']))
{
    if ($manager->ifPersonnageExist($_POST['personnageNom']))
    {
        $perso = $manager->getPersonnage($_POST['personnageNom']);
    }
    else
    {
        $message = 'Ce personnage n\'existe pas';
    }
}

elseif (isset($_GET['frapperUnPersonnage']))
{
    if (!isset($perso))
    {
        $message = 'Merci de créer un personnage ou de vous identifier';
    }
    
    else
    {
        if (!$manager->ifPersonnageExist((int) $_GET['frapperUnPersonnage']))
        {
            $message = 'Le personnage que vous voulez attaquer n\'existe pas';
        }
        
        else
        {
            $persoAFrapper = $manager->getPersonnage((int) $_GET['frapperUnPersonnage']);
            $retour = $perso->frapperUnPersonnage($persoAFrapper);
            
            switch ($retour)
            {
                case Personnage::DETECT_ME :
                    $message = 'Mais...c\'est moi...Stupid idiot !!!';
                    
                    break;
                
                case Personnage::PERSO_COUP :
                    $message = 'Le personnage a bien été atteint';
                    
                    $manager->updatePersonnage($perso);
                    $manager->updatePersonnage($persoAFrapper);
                    
                    break;
                
                case Personnage::PERSO_DEAD :
                    $message = 'Vous avez tué ce personnage !';
                    
                    $manager->updatePersonnage($perso);
                    $manager->deletePersonnage($persoAFrapper);
                    
                    break;
                
                case Personnage::PERSO_ASLEEP :
                    $message = 'Vous êtes endormi et ne pouvez pas frapper un adversaire';
                    
                    break;
            }
        }
    }
}

elseif (isset($_GET['envouter']))
{
    if (!isset($perso))
    {
        $message = 'Merci de créer une personnage ou de vous identifier';
    }
    
    else
    {
        if ($perso->getType() != 'magicien')
        {
            $message = 'Vous n\êtes pas un magicien...Vous ne pouvez pas envouter un adversaire';
        }
        
        else
        {
            if (!$manager->ifPersonnageExist((int) $_GET['envouter']))
            {
                $message = 'Le personnage que vous voulez envouyter n\existe pas';
            }
            
            else
            {
                $persoAEnvouter = $manager->getPersonnage((int) $_GET['envouter']);
                $retour = $perso->lancerUnSort($persoAEnvouter);
                
                switch ($retour)
                {
                    case Personnage::DETECT_ME :
                        $message = 'Stupid idiot...Je ne peux m\'envouter';
                        
                        break;
                    
                    case Personnage::PERSO_ENVOUTE :
                        $message = 'Votre adversaire est bien envouté';
                        
                        $manager->updatePersonnage($perso);
                        $manager->updatePersonnage($persoAEnvouter);
                        
                        break;
                    
                    case Personnage::NO_MAGIE :
                        $message = 'Vous n\'avez pas assez de magie !';
                        
                        break;
                    
                    case Personnage::PERSO_ASLEEP :
                        $message = 'Vous êtes endormi, vous ne pouvez pas lancer de sort !';
                        
                        break;
                }
            }
        }
    }
}


?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <meta name="description" content="Exemples POO en PHP - basé sur le MOOC POO - PHP OpenClassrooms">
        <meta name="keywords" content="POO, PHP, Bootstrap">
        <meta name="author" content="Christophe Malo">
            
        <title>Mini jeu de combat - POO - PHP</title>

        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css"  href="css/bootstrap_mini_jeu_combat_v01.css" media="all"> 
        
    </head>
    <body>

    <audio src="donjon-naheulbeuk.mp3" id="my_audio" loop="loop"></audio>
  <script type="text/javascript">
    window.onload=function(){
      document.getElementById("my_audio").play();
    }
  </script>

        <div class="container">
        <!-- Header
        ================================================== -->
            <header class="row col-sm-12">
                <h1>Mini jeu de combat - POO - PHP</h1>
            </header>
        
        <!-- Section Contenu
        ================================================== -->
            <section id="infos" class="row col-sm-12">
                <p>Nombre de personnage créés : <?= $manager->countPersonnages() ?></p>
                <p>
                    <?php
                        if (isset($message)) { 
                            echo $message;    
                        }
                    ?>
                </p>
            </section>
        
        <!-- Section Personnage
        ================================================== -->
            <?php
            // Si utilisation d'un personnage
            if (isset($perso)) {
            ?>
                <div class="row col-sm-12"><a class="btn btn-default btn-lg pull-right" href="?deconnexion=1" role="button">Déconnexion</a></div>
                <section class="row col-sm-12">
                    <fieldset>
                        <legend>Mes informations</legend>
                        <p>
                            Nom : <?= htmlspecialchars($perso->getNom()) ?><br>
                            Dégâts : <?= $perso->getDegats() ?><br>
                            Type : <?= ucfirst($perso->getType()) ?><br>
                            <?php
                            
                            switch ($perso->getType()) {
                                case 'guerrier' :
                                    echo 'Protection : ';
                                    break;
                                case 'magicien' :
                                    echo 'Magie : ';
                                    break;
                            }
                            
                            echo $perso->getAtout();
                            ?>
                        </p>
                    </fieldset>
                    <fieldset>
                        <legend>Qui frapper ?</legend>
                        <p>
                        <?php
                       
                            $persos = $manager->getListPersonnages($perso->getNom());
                            
                            if (empty($persos)) {
                                echo 'Il n\'y aucun adversaire';
                            }
                            
                            else {
                                if ($perso->toBeAsleep()) {
                                    echo 'Un magicien vous a endormi ! Vous allez vous réveiller dans ' . $perso->reveil() . '.';
                                }
                                
                                else {
                                    foreach ($persos as $onePerson) {
                                        echo '<a href="?frapperUnPersonnage=' . $onePerson->getId() . '">' . htmlspecialchars($onePerson->getNom()) . '</a> (Dégats : ' . $onePerson->getDegats() . ' - type : ' . $onePerson->getType() . ')';
                                        
                                        if ($perso->getType() == 'magicien') {
                                            echo ' - <a href="?envouter=' . $onePerson->getId() . '">Lancer un sort</a>';
                                        }
                                        
                                        echo '<br>';
                                    }
                                }
                            }
                        ?>
                        </p>
                    </fieldset>
                </section>
            
            <?php
            } else {
            ?>
        <!-- Section Formulaire saisie - choix
        ================================================== -->
                <section class="row col-sm-12">
                    <form class="form-horizontal" method="post">
                        <!-- Champ de saisie texte une ligne -->
                        <div class="form-group form-group-lg">
                            <label for="personnageNom" class="col-xs-12 col-sm-4 col-md-3 control-label">Nom du personnage : </label>
                            <div class="col-xs-12 col-sm-8 col-md-9 focus"> 
                                <input class="form-control input-lg" type="text" name="personnageNom" id="prenom" placeholder="Nom du personnage" autofocus required />
                            </div>
                            
                        </div>
                        <div class="form-group form-group-lg">
                            <label for="personnageType" class="col-xs-12 col-sm-4 col-md-3 control-label">Type du personnage : </label>
                            <div class="col-xs-12 col-sm-8 col-md-9">
                                <select class="form-control input-lg" name="personnageType">
                                    <option value="magicien">Magicien</option>
                                    <option value="guerrier">Guerrier</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-default btn-lg pull-right" value="Créer le personnage" name="creer">Créer le personnage</button>
                        <button type="submit" class="btn btn-default btn-lg pull-right" value="Utiliser le personnage" name="utiliser">Utiliser le personnage</button>
                    </form>
                </section>
        <?php
            }
        ?>
        <!-- Footer
        ================================================== -->
        </div>
    </body>
</html>
<?php
if (isset($perso)) {
    $_SESSION['perso'] = $perso;
}
?>