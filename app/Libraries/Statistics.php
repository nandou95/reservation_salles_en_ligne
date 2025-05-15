<?php
/**
 * @author jules
 */
namespace App\Libraries;
use App\Models\ModelS;

class Statistics
{
	
	function __construct()
	{
		// code...
	}

	public static function log_activity($value='')
	{
		// code...
		$request = \Config\Services::request();
		$router = \Config\Services::router();
		$ModelS=new ModelS();//get model
		$session= session();
		$agent =$request->getUserAgent();
        $ip = $request->getIPAddress();
        $url = current_url();
        $date = date("d/m/Y H:i:s");
        $user = ($session->get('email')) ? $session->get('email') : 'Anonymous' ;
        $user_id = ($session->get('id')) ? $session->get('id') : '-1' ;
        $method = $request->getMethod();
		if ($agent->isBrowser()) {
		    $currentAgent = $agent->getBrowser() . '-v-' . $agent->getVersion();
		} elseif ($agent->isRobot()) {
		    $currentAgent = $agent->getRobot();
		} elseif ($agent->isMobile()) {
		    $currentAgent = $agent->getMobile();
		} else {
		    $currentAgent = 'Unidentified User Agent';//154.117.208.115
		}
        $browser=$currentAgent;
        $os=$agent->getPlatform();
        $_method = $router->methodName();
        $_controller = $router->controllerName();
        $uri= uri_string(); 
        $logs = sprintf("%s-%s-%s--%s-%s-%s", $date,$url,$method,$uri,$_controller,$_method);
        $enc_logs = $logs;
        $AuditTrailData=[
			"logs"=>$enc_logs,
			"date_time"=>date("Y-m-d H:i:s"),
			"username"=>$user,
			'user_id'=>$user_id,
			'ip_adresse'=>$ip,
			'operating_system'=>$os,
			'browser_used'=>$browser
        ];
       $ModelS->create("logs",$AuditTrailData);



	}
}
?>