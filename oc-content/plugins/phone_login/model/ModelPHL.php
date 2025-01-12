<?php
class ModelPHL extends DAO {
private static $instance;

public static function newInstance() {
  if( !self::$instance instanceof self ) {
    self::$instance = new self;
  }
  return self::$instance;
}

function __construct() {
  parent::__construct();
}


public function getTable_item() {
  return DB_TABLE_PREFIX.'t_item';
}

public function getTable_user() {
  return DB_TABLE_PREFIX.'t_user';
}

public function getTable_category() {
  return DB_TABLE_PREFIX.'t_category';
}


public function import($file) {
  $path = osc_plugin_resource($file);
  $sql = file_get_contents($path);

  if(!$this->dao->importSQL($sql) ){
    throw new Exception("Error importSQL::ModelPHL<br>" . $file . "<br>" . $this->dao->getErrorLevel() . " - " . $this->dao->getErrorDesc() );
  }
}


public function install($version = '') {
  if($version == '') {
    //$this->import('phone_login/model/struct.sql');

    osc_set_preference('version', 100, 'plugin-phone_login', 'INTEGER');
  }
}


public function uninstall() {
  // DELETE ALL TABLES
  //$this->dao->query(sprintf('DROP TABLE %s', $this->getTable_attribute()));


  // DELETE ALL PREFERENCES
  $db_prefix = DB_TABLE_PREFIX;
  $query = "DELETE FROM {$db_prefix}t_preference WHERE s_section = 'plugin-phone_login'";
  $this->dao->query($query);
}



// FIND USER BY EMAIL OR PHONE
public function findUser($email_phone) {
  if($email_phone == '') {
    return false; 
  }

  $this->dao->select();
  $this->dao->from($this->getTable_user());

  $this->dao->where('s_email = "' . $email_phone . '" OR s_phone_mobile = "' . $email_phone . '"');
  $this->dao->limit(1);

  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }

  return false;
}



// FIND USER BY PHONE NUMBER
public function findUserByPhone($phone, $advanced = true) {
  if($phone == '') {
    return false; 
  }

  $this->dao->select();
  $this->dao->from($this->getTable_user());

  if($advanced == false) {
    //$this->dao->where('s_phone_mobile', $phone);
    $this->dao->where('(s_phone_mobile = "' . $phone . '" OR s_phone_land = "' . $phone . '")');
  } else {
    $this->dao->where(sprintf('(trim(LEADING "0" FROM replace(replace(replace(replace(replace(replace(s_phone_mobile, "+", ""), ")", ""), "(", ""), "-", ""), "/", ""), " ", "")) like "%%%s%%" or trim(LEADING "0" FROM replace(replace(replace(replace(replace(replace(s_phone_land, "+", ""), ")", ""), "(", ""), "-", ""), "/", ""), " ", "")) like "%%%s%%")', $phone, $phone));
  }

  $this->dao->where('pk_i_id != ' . osc_logged_user_id());
 
  $this->dao->limit(1);

  $result = $this->dao->get();
  
  if($result) {
    $data = $result->row();
    
    if(isset($data['pk_i_id'])) {
      return $data;
    }
  }

  return false;
}


}
?>