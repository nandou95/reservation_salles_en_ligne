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
$routes->match(['get', 'post'], 'service', 'Home::service');
$routes->match(['get', 'post'], 'about_us', 'Home::about_us');
$routes->match(['get', 'post'], 'salle/(:any)', 'Home::salle/$1');
$routes->match(['get', 'post'], 'contact_nous', 'Home::contact_nous');
#################### Fin home ###################

if(is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
?>