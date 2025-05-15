<?php
/**
 * 
 */
namespace App\Libraries;
class CodePlayHelper
{
	function __construct()
	{
		# code...
	}

	public function sendEmail($value='')
	{
		// code...
		return $value;
	}

	public function generate_password($taille)
	{
		$Caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMOPQRSTUVXWYZ0123456789,.@{-_/#'; 
		$QuantidadeCaracteres = strlen($Caracteres); 
		$QuantidadeCaracteres--; 

		$Hash=NULL; 
		for($x=1;$x<=$taille;$x++)
		{ 
			$Posicao = rand(0,$QuantidadeCaracteres); 
			$Hash.= substr($Caracteres,$Posicao,1);
		}
		return $Hash; 
	}

	public function generate_code_user($taille)
	{
		$Caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMOPQRSTUVXWYZ0123456789,.@{-_/#'; 
		$QuantidadeCaracteres = strlen($Caracteres); 
		$QuantidadeCaracteres--; 

		$Hash=NULL; 
		for($x=1;$x<=$taille;$x++)
		{ 
			$Posicao = rand(0,$QuantidadeCaracteres); 
			$Hash.= substr($Caracteres,$Posicao,1);
		}
		return $Hash; 
	}

	function send_mail($emailTo = array(), $subjet, $cc_emails = array(), $message, $attach = array())
	{
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = 'ssl://mamba.afriregister.com';
		$config['smtp_port'] = 465;
		$config['smtp_user'] = 'alexis@mediabox.bi';
		$config['smtp_pass'] = 'Badia@79839653';
		$config['mailtype'] = 'html';
		$config['charset'] = 'UTF-8';
		$config['wordwrap'] = TRUE;
		$config['smtp_timeout'] = 20;
		$config['newline'] = "\r\n";
		$this->CI->email->initialize($config);
		$this->CI->email->set_mailtype("html");

		$this->CI->email->from('alexis@mediabox.bi', 'notification');
		$this->CI->email->to($emailTo);
		if(!empty($cc_emails))
		{
			foreach ($cc_emails as $key => $value)
			{
				$this->CI->email->cc($value);
			}
		}

		$this->CI->email->subject($subjet);
		$this->CI->email->message($message);

		if(!empty($attach))
		{
			foreach ($attach as $att)
			{
				$this->CI->email->attach($att);
			}
		}
		$this->CI->email->send();
	}
}
?>