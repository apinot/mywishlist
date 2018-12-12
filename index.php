<?php
/**
 * File:  index.php
 * Creation Date: 04/12/2017
 * description:
 *
 * @author: canals
 */

 use \mywishlist\controleurs\ControleurListeCreateur;
 use \mywishlist\controleurs\ControleurListeParticipant;
 use Illuminate\Database\Capsule\Manager as DBManager;

require_once __DIR__ . '/vendor/autoload.php';

//Init Eloquent
$db = new DBManager();
$db_conf = parse_ini_file(__DIR__ . "/src/conf/conf.ini");
$db->addConnection($db_conf);
$db->setAsGlobal();
$db->bootEloquent();

//Init Slim
$settings = require_once __DIR__ . "/src/conf/settings.php";
$container = new Slim\Container($settings);
$app = new Slim\App($container);


//Accueil
$app->get('/', \mywishlist\controleurs\ControleurAccueil::class.":afficherAccueil")->setName('accueil');

//listeCreateur
$app->group("/liste", function() use ($app){
    $app->get('/creer', \mywishlist\controleurs\ControleurListeCreateur::class.":afficherFormulaireCreation")->setName("formulaireCreerListe");
    $app->post('/creer', \mywishlist\controleurs\ControleurListeCreateur::class.":creerListe")->setName("creerListe");
    $app->get('/c{id}', \mywishlist\controleurs\ControleurListeCreateur::class.":afficherListe")->setName('listeCreateur');
    $app->get('/c{id}/details',\mywishlist\controleurs\ControleurListeCreateur::class.":afficherListeAvecDetails")->setName('listeCreateurDetails');

    $app->get('/c{id}/creerItem', \mywishlist\controleurs\ControleurListeCreateur::class.":afficherFormulaireAjoutItem")->setName("formulaireAjouterItem");
    $app->post('/c{id}/creerItem', \mywishlist\controleurs\ControleurListeCreateur::class.":ajouterItem")->setName("ajouterItem");
});

//Liste participant
$app->group("/liste", function() use ($app){
    $app->get('/p{id}', \mywishlist\controleurs\ControleurListeParticipant::class.":afficherListe")->setName('listeParticipant');
    $app->get('/p{id}/details',\mywishlist\controleurs\ControleurListeParticipant::class.":afficherListeAvecDetails")->setName('listeParticipantDetails');
});

$app->get('/item/{id}', \mywishlist\controleurs\ControleurItem::class.":afficherItem")->setName("voirItem");

$app->run();
