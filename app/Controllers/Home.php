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

  public function reservation($id)
  {
    $titre='Titre';
    $data['titre']=$titre
    $data['id']=$id;
    return view('Reservation_View',$data);
  }

  public function mariage()
  {
    return view('Mariage_View');
  }

  public function conferences()
  {
    return view('Conferences_View');
  }

  public function autres_evenements()
  {
    return view('Autres_evenements_View');
  }

  public function contact_nous()
  {
    return view('Contact_nous_View');
  }
}
?>