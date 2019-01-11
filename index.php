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
 use \mywishlist\controleurs\ControleurAccueil;
 use \mywishlist\controleurs\ControleurUser;
 use \mywishlist\controleurs\ControleurItem;
 use \mywishlist\controleurs\Flash;
 use \mywishlist\controleurs\Authentification;

 use Illuminate\Database\Capsule\Manager as DBManager;
 use Psr7Middlewares\Middleware;

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
$container["rootDir"] = __DIR__;
$app = new Slim\App($container);

$appMiddlewares = [
    Middleware::TrailingSlash(false) //(optional) set true to add the trailing slash instead remove
        ->redirect(301),
    Flash::class."::flashMiddleware",
    Flash::class."::savePostMiddleware"
];

$requireLogged = Authentification::class."::requireLoggedMiddleware";
$requireAnon = Authentification::class."::requireAnonMiddleware";

foreach($appMiddlewares as $middleware)
{
    $app->add($middleware);
}


//Accueil
$app->get('/', ControleurAccueil::class.":afficherAccueil")->setName('accueil');

//listeCreateur
$app->group("/liste", function() use ($app){
    $app->get('/creer', ControleurListeCreateur::class.":afficherFormulaireCreation")->setName("formulaireCreerListe");
    $app->post('/creer', ControleurListeCreateur::class.":creerListe")->setName("creerListe");

    //Routes necessitant une auth
    $app->group("/c{id}", function() use($app){
        $app->get("", ControleurListeCreateur::class.":afficherListe")->setName('listeCreateur');
        $app->get('/details', ControleurListeCreateur::class.":afficherListeAvecDetails")->setName('listeCreateurDetails');

        $app->get('/creerItem', ControleurListeCreateur::class.":afficherFormulaireAjoutItem")->setName("formulaireAjouterItem");
        $app->post('/creerItem', ControleurListeCreateur::class.":ajouterItem")->setName("ajouterItem");

    $app->delete('/c{id}', ControleurListeCreateur::class.":supprimerListe")->setName("supprimerListe");
        $app->get('/editer', ControleurListeCreateur::class.":afficherFormulaireModification")->setName("formulaireModifListe");
        $app->put('/editer', ControleurListeCreateur::class.":modifierListe")->setName("modifierListe");



        $app->get('/item{num}/editer', ControleurListeCreateur::class.":afficherModifItemListe")->setName("formulaireModifItem");
        //methode put
        $app->post('/item{num}/editer', ControleurListeCreateur::class.":modifierItem")->setName("modifierItem");
        $app->delete('/item{num}', ControleurListeCreateur::class.":supprimerItem")->setName('supprimerItem');
    })->add(ControleurListeCreateur::class."::checkCreateurMiddleware");

    $app->get('/meslistes', ControleurListeCreateur::class.":afficherMesListes")->setName("afficherMesListes");

});

//Liste participant
$app->group("/liste", function() use ($app){
    $app->get('/publiques[/page{numPage:[0-9]+}]', ControleurAccueil::class.":afficherListesPubliques")->setName('listesPubliques');

    $app->group("/p{token}", function() use ($app){
        $app->get("", ControleurListeParticipant::class.":afficherListe")->setName('listeParticipant');
        $app->get('/details', ControleurListeParticipant::class.":afficherListeAvecDetails")->setName('listeParticipantDetails');
        $app->get('/details/item/{idItem}', ControleurItem::class.":afficherItem")->setName('afficherItem');
        $app->get('/details/item/{idItem}/reserver', ControleurItem::class.":afficherFormulaireReservation")->setName('formulaireReserverItem');
        $app->post('/details/item/{idItem}/reserver', ControleurItem::class.":reserverItem")->setName('reserverItem');
    })->add(ControleurListeParticipant::class."::checkNonCreateurMiddleware");
});

//compte
$app->group("/compte", function() use ($app, $requireAnon, $requireLogged){

    $app->get("", ControleurUser::class . ":afficherCompte")->setName("compte")->add($requireLogged);
    $app->get("/login", ControleurUser::class . ":afficherLogin")->setName("afficherLogin")->add($requireAnon);
    $app->get("/deconnexion", ControleurUser::class . ":deconnecter")->setName("deconnexion")->add($requireLogged);

    $app->post("/login", ControleurUser::class . ":login")->setName("login")->add($requireAnon);
    $app->post("/nouveau", ControleurUser::class . ":creer")->setName("creerCompte")->add($requireAnon);

    $app->put("", ControleurUser::class . ":modifier")->setName("modifierCompte")->add($requireLogged);
    $app->delete("", ControleurUser::class . ":supprimer")->setName("supprimerCompte")->add($requireLogged);
    $app->put("/modiferMdp", ControleurUser::class . ":modifierMdp")->setName("modifierMdp")->add($requireLogged);

});

$app->run();
