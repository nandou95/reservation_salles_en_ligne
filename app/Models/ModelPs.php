<?php
/**
 * @author julesyuliyusi@gmail.com
 * Reference https://codeigniter4.github.io/userguide/database/query_builder.html?highlight=query%20builder#insertbatch
 */

namespace App\Models;

use CodeIgniter\Model;


class ModelPs extends Model
{

    
    function __construct()
    {
        // code...
        parent::__construct();

    }
    //execute tous les requetes  pour insertion update et delete
    public function createUpdateDelete($requete,$bindparameters=array())
    {         
        $query =$this->db->query($requete,$bindparameters);
        if ($query) {
            // code...
            return TRUE;
        }else{
            return FALSE;
        }
        
    }
    
     
    //execute tous les requetes de selection qui retourne une ligne
    public function getRequeteOne($requete,$bindparameters=array())
    {   
        // print_r($bindparameters);die();
        $query =$this->db->query($requete,$bindparameters);
        return $query->getRowArray();
    }    
    //execute tous les requetes de selection qui retourne + d'une ligne
    public function getRequete($requete,$bindparameters=array())
    { 
            
        $query =$this->db->query($requete,$bindparameters);
        return $query->getResult();
    }
    // debut fonction pour datatable
    public function datatable($requete)
    {           
        $query =$this->db->query($requete);
        return $query->getResult();
    }
    public function filtrer($requete)
    {    
         $query =$this->db->query($requete);
         return $query->getRowArray();

    }    
   // debut fonction pour datatable
}
?>