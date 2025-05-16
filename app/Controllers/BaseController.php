<?php
namespace App\Controllers;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
abstract class BaseController extends Controller
{
  protected $request;
  protected $helpers = [];

  /** Constructor.*/
  public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
  {
    parent::initController($request, $response, $logger);
    $session = \Config\Services::session();
    $language = \Config\Services::language();
    $language->setLocale($session->lang);        
  }

  //recuperation des segments
  public function urichk()
  {
    $data['menu']= $this->request->uri->getSegment(1); 
    $data['sousmenu']= $this->request->uri->getSegment(2);
    $data['sousmenu2']= $this->request->uri->getSegment(3);  
    return $data;
  }
}
?>
