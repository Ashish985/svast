<?php

class Admin_model extends CI_Model{


  public function insertFileData($table='',$data='')
   {
		if(!empty($data) && !empty($table)){
          $this->db->insert($table,$data);
          return $this->db->insert_id();
		}else{
			return false;
		}
   }

   public function get_clients($table) 
    { 
      $this->db->select("name");    
      // $this->db->order_by("created_date", "desc");
      $query = $this->db->get($table);
      // print_r($query->result());
      // die();
      return $query->result();
    }

   public function getCount($table){
	return $this->db->count_all_results('tbl_output');
   }

   public function check_client($table,$name){
     
     $this->db->select('*');
     $this->db->from($table);
     $this->db->where('name', $name);
     $query = $this->db->get();
     if ($query->num_rows() > 0) {
         return $query->result();
     } else {
         return false;
     }
      
   }

   public function create($table,$data){

      return $this->db->insert($table, $data);
   }

   public function getOutputData($skip, $limit) 
   {
      $this->db->order_by("inserted_date", "desc");
      $query = $this->db->get('tbl_output', $limit, $skip);
	// print_r($query->result_array());
	return $query->result_array();
   }
  
   //exel outputfile data get by id 
   public function outputfileGet($table,$id) 
   {
      $this->db->select("*");
      $this->db->from($table);
      $this->db->where("id", $id);
      $query = $this->db->get();
  
      return $query->result();
   } 

   function is_record_exists($table, $id)
   {
      $this->db->where('id',$id);
      $query = $this->db->get($table);
      if ($query->num_rows() > 0){
         return true;
      }
      else{
         return false;
      }
   }


   //delete exel outputfile data by id 
   public function outputfileDeletedById($table,$id) 
   {
      print_r($id);
      
      if($this->is_record_exists($table, $id) == true){
         $this->db->where('id', $id);
         $this->db->delete($table);
         return array(
            'status' => 'success',
            'message' => 'record has been deleted',
        );
      }
      else{
         return array(
            'status' => 'error',
            'message' => 'id not exist!',
        );
      }
   }


   public function deletedByIdMul($table,$ids) 
   {
      for($i=0; $i<count($ids); $i++){
         if($this->is_record_exists($table, $ids[$i]) == true){
            $this->db->where('id', $ids[$i]);
            $this->db->delete($table);
         }
      }
      return array(
         'status' => 'success',
         'message' => 'record has been deleted',
     );
   }

   //edit exel outputfile data by id 
   public function outputfileEdit($table,$id,$data) 
   {
   //   print_r($data);
     if($this->is_record_exists($table, $id,$data) == true){
      $this->db->where('id', $id);
      return $this->db->update($table, $data); 
     }
     else{
      return false;
     }
      
   }


}

 ?>