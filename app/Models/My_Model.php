<?php 
namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;
class My_Model extends Model {

	protected $db;
	public function __construct(ConnectionInterface &$db) {
		$this->db =& $db;
	}


	public function getList($table,$critere=array()) {

		return $this->db
		->table($table)
		->where($critere)
		->get()
		->getResultarray();
	}

	public function getOne($table,$critere_array) 
	{

		return $this->db
		->table($table)
		->where($critere_array)
		->get()
		->getRowarray();
	}

	public function create($table,$data_array) 
	{

		return $this->db
		->table($table)
		->insert($data_array);
	}

	public function update_data($table,$critere_array=array(),$data_array=array()) 
	{
		return $this->db
		->table($table)
		->where($critere_array)
		->set($data_array)
		->update();
	}

	
	public function delete_data($table,$critere_array) {
		return $this->db
		->table($table)
		->where($critere_array)
		->delete();
	}
	public function getRequete($sql) {

		return $this->db
		->query($sql)
		->getResultarray();
	}

	public function getRequeteOne($sql) {

		return $this->db
		->query($sql)
		// ->get()
		->getRowarray();
	}



	 public function datatable($requete)
    { 
       
        return $this->db
		->query($requete)
	    // ->mysqli_next_result()

	    //->get()
		->getResult();

    }
    public function filtrer($requete)
    {
     // $query =$this->db->query($requete);
     // mysqli_next_result($this->db->conn_id);
     // return $query->getRowarray();
// print_r($requete);die();
      return $this->db
		->query($requete)
		->getRowarray();

 }    


  //execute tous les requetes de selection qui retourne une ligne
    public function procegetRequeteOne($requete,$bindparameters=array())
    { 

        return $this->db
		->query($requete,$bindparameters)
		->getRowarray();
    }    
    //execute tous les requetes de selection qui retourne + d'une ligne
    public function procegetRequete($requete,$bindparameters=array())
    {   
         
		$query=$this->db->query($requete,$bindparameters);
		return $query->getResultarray();

		if($query->getResultarray() === false){
        return false;
        }
        $query->getResultarray();

    }


	



}
?>