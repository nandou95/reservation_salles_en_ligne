<?php

/* @author HABIMANA Nandou */ 
namespace App\Controllers;
use App\Models\ModelPs;
use App\Models\ModelS;

class Home extends BaseController
{
	protected $session;
  protected $ModelPs;

  public function __construct()
  {	
    $this->session = \Config\Services::session();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
  }

  public function index()
  {
    $sql='Titre';
    $data['Titre']=$sql;
    return view('Home_View',$data);
  }

  public function about_us()
  {
    return view('About_us_View');
  }

  public function service()
  {
    return view('Service_View');
  }

  public function salle($i=1)
  {
    $commune_id=0;
    $province_id=0;
    $date_evenement=0;
    $evenement="Mariage";
    if($i==2)
      $evenement="Conférence";
    if($i==3)
      $evenement="Autres événements";

    $data['i']=$i;
    $data['evenement']=$evenement;
    return view('Salle_View',$data);
  }

  public function contact_nous()
  {
    return view('Contact_nous_View');
  }

  public function reservation($id)
  {
    $titre='Titre';
    $data['titre']=$titre;
    $data['id']=$id;
    return view('Reservation_View',$data);
  }
}
?>