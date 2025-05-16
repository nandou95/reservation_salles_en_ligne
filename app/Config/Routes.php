<?php
/* @autor HABIMANA Nandou 71483905/69301985 */
namespace Config;
use App\Controllers\ShoppingCart;
$routes = Services::routes();

/* Router Setup */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override(function()
{
	return view('Page404'); 
});

//For internationalisation
$routes->get('lang/{locale}', 'Language::index');
#################### Debut home #################
$routes->match(['get', 'post'], '/', 'Home::index');
#################### Fin home #####################

############### module administration ##################
// $routes->group('Administration', ['namespace' => 'App\Modules\Administration\Controllers'], function ($routes)
// {
// 	$routes->get('Menu_Engagement_Juridique/exporter_Excel_deja_fait/(:any)','Menu_Engagement_Juridique::exporter_Excel_deja_fait/$1');
// 	$routes->get('Liquidation_Double_Commande/exporter_Excel_deja_fait/(:any)','Liquidation_Double_Commande::exporter_Excel_deja_fait/$1');
// });
############### module administration ##################

if(is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
?>