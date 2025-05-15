<?php

/**NSABIMANA Vincent
  *Numero de Telephone (WhatsApp): (+257) 61970146
  *Email: vincent@mediabox.bi
  *Date: 27 Novembre,2023
  *Titre: Generation des documents
**/

namespace App\Controllers;
use App\Controllers\BaseController;
use Dompdf\Dompdf;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Gerate_pdf extends BaseController
{
  protected $session;
  protected $ModelPs;
    
  public function __construct()
  { 
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
    $this->session = \Config\Services::session();
  }

  // lettere de cadrage
  public function lettre_cadrage()
  {
    $dompdf = new Dompdf();
    $data = [
      'imageSrc'    => $this->imageToBase64(ROOTPATH . '/public/uploads/document_generer/logo_finance.png'),//appeller la foction imageToBase64 avec  le lien de votre image
      'name'         => 'John Doe',
      'address'      => 'USA',
      'mobileNumber' => '000000000',
      'email'        => 'john.doe@email.com',
      'imageSrc2'    => $this->imageToBase64(ROOTPATH . '/public/uploads/document_generer/logo_finance2.png'),
      'imageSrc3'    => $this->imageToBase64(ROOTPATH . '/public/uploads/document_generer/logo_finance3.png'),
      'message_premier'=>'1. Le Gouvernement du Burundi a reçu un financement du Groupe de la Banque  Africaine de
                        Développement sur les ressources de la Facilité d’Appui à la Transition afin de couvrir le coût de
                        Projet d’Appui à l’Amélioration de la Mobilisation des Ressources et du Climat des Affaires
                        (PARMOCAF) et a l’intention d’utiliser une partie des sommes accordées au titre de ce Don pour
                        financer le contrat d’un Cabinet International pour l’accompagnement de l’Agence d’Appui à la
                        Réalisation des Contrats de Partenariat Public-Privé (ARCP).',
     'message_second'=>'2. L’objectif global de cette activité est de permettre une formation et un accompagnement de l’ARCP
                    par un Cabinet International, pour que cette structure soit dotée de capacités nécessaires pour mettre
                    en œuvre ses missions, notamment le pilotage au niveau stratégique et opérationnel de la mise en
                    œuvre du dispositif PPP au Burundi.',
     'message_trois'=>'Sans être exhaustif, le consultant sera appelé à :', 
     'message_quatre'=> '- Faire le Diagnostic d’évaluation des capacités des services et structure
                    institutionnels existants de l’ARCP ;
                    - Présenter à la Direction de l’ARCP une note méthodologique sur la formation
                    attendue afin d’évaluer si le champ de la formation est totalement couvert ;
                     Elaborer des modules de formation spécifique au thème à enseigner ;
                    - Organiser les sessions de formation par groupe de participant (personnel de
                    l’ARCP ; Membres du Comité National PPP et autres partenaires institutionnels ;
                    - Dispenser les formations proprement dites de façon que les participants maitrisent
                    la matière'             
    ];

    $html = view('Lettre_Cadrage_View', $data); //appel de la view
    $dompdf->loadHtml($html); //conversion de la page html en pdf
    $dompdf->render();
    $dompdf->stream('Lettre_Cadrage_View.pdf', [ 'Attachment' => false ]);

  }
  
  private function imageToBase64($path)
  { //fonction pour l'affichage d'une image
    $path = $path;
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    return $base64;
  }

  function htmlToPDF()
  { //fonction pour le telechagement du document
    $dompdf = new \Dompdf\Dompdf(); 
    $dompdf->loadHtml(view('Lettre_Cadrage_View'));
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream();
  }
}
?>