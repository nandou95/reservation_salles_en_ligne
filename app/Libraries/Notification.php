<?php 
namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Notification
{
  protected $email;
  public function __construct()
  {
  }

  public function sendEmail_old($to, $subject, $message,$attach = array(),$cc_email=array())
  {
    $this->email = new PHPMailer(true);
    $this->email->isSMTP();
    $this->email->SMTPDebug = 0; // Mettez à 2 pour afficher les erreurs
    $this->email->Host = 'ssl://pongo.afriregister.com'; // Remplacez par votre serveur SMTP
    $this->email->SMTPAuth = true;
    $this->email->Username = 'nandou@mediabox.bi'; // Remplacez par votre adresse email
    $this->email->Password = 'H2bim2n219N2ndou95'; // Remplacez par votre mot de passe
    $this->email->SMTPSecure = 'tls';
    $this->email->charset = 'UTF-8';
    $this->email->Port = 465;        
    try
    {
      $this->email->setFrom('nandou@mediabox.bi', 'SUIVI PTBA');
      $this->email->addAddress($to);
      if(!empty($cc_email))
      {
        foreach ($cc_email as $email)
        {
          $this->mail->addCC($email);
        }                
      }

      // Contenu du message
      $this->email->isHTML(true);
      $this->email->Subject = $subject;
      $this->email->Body    = $message;
      if(!empty($attach))
      {
        foreach ($attach as $att)
        {
          $this->email->addAttachment($att);
        }
      }

      // Envoi du message
      $this->email->send();
      return true;
    }
    catch (Exception $e)
    {
      return false;
    }
  }

  public function sendEmail($to, $subject, $message,$attach = array(),$cc_email=array())
  {
    $this->email = new PHPMailer(true);
    $this->email->isSMTP();
    $this->email->SMTPDebug = 0; // Mettez à 2 pour afficher les erreurs
    $this->email->Host = 'ssl://pongo.afriregister.com'; // Remplacez par votre serveur SMTP
    $this->email->SMTPAuth = true;
    $this->email->Username = 'suiviptba@mediabox.bi'; // Remplacez par votre adresse email
    $this->email->Password = 'Suiviptbambx@2023'; // Remplacez par votre mot de passe
    $this->email->SMTPSecure = 'tls';
    $this->email->charset = 'UTF-8';
    $this->email->Port = 465;        
    try
    {
      $this->email->setFrom('suiviptba@mediabox.bi', 'SUIVI PTBA');
      $this->email->addAddress($to);
      if(!empty($cc_email))
      {
        foreach ($cc_email as $email)
        {
          $this->mail->addCC($email);
        }                
      }

      // Contenu du message
      $this->email->isHTML(true);
      $this->email->Subject = $subject;
      $this->email->Body    = $message;
      if(!empty($attach))
      {
        foreach ($attach as $att)
        {
          $this->email->addAttachment($att);
        }
      }

      // Envoi du message
      $this->email->send();
      return true;
    }
    catch (Exception $e)
    {
      return false;
    }
  }
}
?>
