<?php

class ModelOC extends DAO {
private static $instance;

public static function newInstance() {
  if( !self::$instance instanceof self ) {
    self::$instance = new self ;
  }
  return self::$instance ;
}

function __construct() {
  parent::__construct();
}

public function import($file) {
  $path = osc_plugin_resource($file);
  $sql = file_get_contents($path);
  if(!$this->dao->importSQL($sql)){ throw new Exception("Error importSQL::ModelOC<br>".$file.'<br>'.$path.'<br><br>Please check your database for if there are no plugin tables. <br>If any of those tables exists in your database, drop them!');} 
}
 
public function uninstall() {
  $this->dao->query('DROP TABLE '. $this->getTable_Chat());
  $this->dao->query('DROP TABLE '. $this->getTable_Ban());
}

public function getTable_Chat() {
  return DB_TABLE_PREFIX.'t_oc_chat';
}

public function getTable_Ban() {
  return DB_TABLE_PREFIX.'t_oc_chat_block';
}

public function getTable_User() {
  return DB_TABLE_PREFIX.'t_user';
}

public function getTable_Picture(){
  return DB_TABLE_PREFIX.'t_profile_picture';
}

public function getTable_Page() {
  return DB_TABLE_PREFIX.'t_pages' ;
}


public function getPages() {
  $this->dao->select('pk_i_id');
  $this->dao->from( $this->getTable_Page() );
  $this->dao->where('s_internal_name like "onc_%"');

  $result = $this->dao->get();

  if( !$result ) { return array(); }
  $prepare = $result->result();
  return $prepare;
}


public function getPictureByUserId( $user_id ) {
  $this->dao->select();
  $this->dao->from( $this->getTable_Picture() );
  $this->dao->where('user_id', $user_id);

  $result = $this->dao->get();
  if( !$result ) { return array(); }
  return $result->row();
}


public function getUserButtonsAvailability($user_id) {
  $this->dao->select('pk_i_id, dt_access_date');
  $this->dao->from( $this->getTable_User() );

  $this->dao->where('pk_i_id in (' . $user_id . ')');

  $result = $this->dao->get();
  if( !$result ) { return array(); }
  $prepare = $result->result();
  return $prepare;
}


public function getChatsForRemoval($date) {
  $this->dao->select('pk_i_chat_id, max(dt_datetime) as dt_datetime');
  $this->dao->from( $this->getTable_Chat() );

  $this->dao->having('max(dt_datetime) < "' . $date . '"');
  $this->dao->groupby('pk_i_chat_id');

  $result = $this->dao->get();
  if( !$result ) { return array(); }
  $prepare = $result->result();
  return $prepare;
}


function removeOldChats() {
  $days = osc_get_preference('delete_days', 'plugin-online_chat');

  if($days == '' || $days <= 0) {
    $days = 7;
  }

  $date = date('Y-m-d H:i:s', strtotime(' -' . $days . ' days', time()));
  $chats = $this->getChatsForRemoval($date);

  if(count($chats) > 0) {
    $remove = array_column($chats, 'pk_i_chat_id');
    $remove = array_unique($remove);
    $remove = implode(',', $remove);

    return $this->dao->query('DELETE FROM ' . $this->getTable_Chat() . ' WHERE pk_i_chat_id in (' . $remove . ')');
  }
}


public function insertChat($chat_id, $from_user_id, $from_user_name, $to_user_id, $to_user_name, $text) {
  $aSet = array(
    'pk_i_chat_id' => $chat_id,
    'i_from_user_id' => $from_user_id,
    's_from_user_name' => $from_user_name,
    'i_to_user_id' => $to_user_id,
    's_to_user_name' => $to_user_name,
    's_text' => $text,
    'dt_datetime' => date('Y-m-d H:i:s')
  );

  $this->dao->insert( $this->getTable_Chat(), $aSet);
  return $this->dao->insertedId();
}


public function insertChatWithoutId($from_user_id, $from_user_name, $to_user_id, $to_user_name, $text) {
  $chat_id = $this->getMaxChatId();
  if($chat_id == '' || $chat_id <= 0) {
    $chat_id = 1;
  } else {
    $chat_id = $chat_id + 1;
  }

  $aSet = array(
    'pk_i_chat_id' => $chat_id,
    'i_from_user_id' => $from_user_id,
    's_from_user_name' => $from_user_name,
    'i_to_user_id' => $to_user_id,
    's_to_user_name' => $to_user_name,
    's_text' => $text,
    'dt_datetime' => date('Y-m-d H:i:s')
  );

  $this->dao->insert( $this->getTable_Chat(), $aSet);
  $message_id = $this->dao->insertedId();
  
  return array(
    'chat_id' => $chat_id,
    'message_id' => $message_id 
  );
}


public function getLatestChats($user_id, $from_timestamp = NULL) {
  $this->dao->select();
  $this->dao->from($this->getTable_Chat());

  $this->dao->where('(i_to_user_id = ' . (int)$user_id . ' OR i_from_user_id = ' . (int)$user_id . ')');
  $this->dao->where('i_end <> ' . (int)$user_id);

  if($from_timestamp === NULL) {
    $this->dao->where('i_shown', 0);
  } else {
    $this->dao->where('dt_datetime >= "' . $from_timestamp . '"');
  }
  
  $this->dao->orderby('pk_i_chat_id DESC, dt_datetime ASC');

  $result = $this->dao->get();
  if( !$result ) { return array(); }
  $prepare = $result->result();

  $this->updateLatestShown($user_id);

  return $prepare;
}


public function getChatUserAvailability($user_id) {
  $query_from = 'SELECT c.pk_i_chat_id, u.dt_access_date FROM ' . $this->getTable_Chat() . ' c, ' . $this->getTable_User() . ' u WHERE c.i_to_user_id = u.pk_i_id AND c.i_end <> ' . $user_id . ' AND c.i_from_user_id = ' . $user_id . ' GROUP BY  c.pk_i_chat_id, u.dt_access_date';
  $query_to = 'SELECT c.pk_i_chat_id, u.dt_access_date FROM ' . $this->getTable_Chat() . ' c, ' . $this->getTable_User() . ' u WHERE c.i_from_user_id = u.pk_i_id AND c.i_end <> ' . $user_id . ' AND c.i_to_user_id = ' . $user_id . ' GROUP BY  c.pk_i_chat_id, u.dt_access_date';

  $result = $this->dao->query($query_from . ' UNION ' . $query_to);

  if( !$result ) { return array(); }
  $prepare = $result->result();
  return $prepare;
}


public function getMaxChatId() {
  $this->dao->select('max(pk_i_chat_id) as max_id');
  $this->dao->from( $this->getTable_Chat() );

  $result = $this->dao->get();
  if( !$result ) { return ''; }
  $row = $result->row();
  return $row['max_id'];
}


public function getAllChats($user_id) {
  $this->dao->select();
  $this->dao->from( $this->getTable_Chat() );

  $this->dao->where('(i_from_user_id = ' . $user_id . ' OR i_to_user_id = ' . $user_id . ')');
  $this->dao->where('i_end <> ' . $user_id);

  $this->dao->orderby('pk_i_chat_id DESC, dt_datetime ASC');

  $result = $this->dao->get();
  if( !$result ) { return array(); }
  $prepare = $result->result();

  $this->updateLatestShown($user_id);

  return $prepare;
}


public function getClosedChats($user_id) {
  $this->dao->select('pk_i_chat_id');
  $this->dao->from( $this->getTable_Chat() );

  $this->dao->where('(i_from_user_id = ' . $user_id . ' OR i_to_user_id = ' . $user_id . ')');
  $this->dao->where('i_end <> 0');
  $this->dao->where('i_end <> ' . $user_id);

  $this->dao->groupby('pk_i_chat_id');

  $result = $this->dao->get();
  if( !$result ) { return array(); }
  $prepare = $result->result();
  return $prepare;
}


public function updateLatestShown($user_id, $chat_id = '') {
  $aSet = array(
    'i_shown' => 1
  );

  if($chat_id <> '' && $chat_id > 0) {
    $aWhere = array('pk_i_chat_id' => $chat_id, 'i_to_user_id' => $user_id, 'i_shown' => 0);
  } else {
    $aWhere = array('i_to_user_id' => $user_id, 'i_shown' => 0);
  }

  return $this->_update($this->getTable_Chat(), $aSet, $aWhere);
}


public function getChatById($chat_id) {
  $this->dao->select();
  $this->dao->from( $this->getTable_Chat() );

  $this->dao->where('pk_i_chat_id = ' . $chat_id);

  $result = $this->dao->get();
  if( !$result ) { return array(); }
  $prepare = $result->result();
  return $prepare;
}


public function insertUserBan($chat_id, $to_user_id = '') {
  $user_id = osc_logged_user_id();

  if($to_user_id == '') {
    $chat = $this->getChatById($chat_id);
    $chat = $chat[0];

    if($chat['i_from_user_id'] == $user_id) {
      $to_user_id = $chat['i_to_user_id'];
    } else {
      $to_user_id = $chat['i_from_user_id'];
    }
  }

  $aSet = array(
    'i_user_id' => $user_id,
    'i_block_user_id' => $to_user_id
  );

  $this->dao->insert( $this->getTable_Ban(), $aSet);
  return $this->dao->insertedId();
}


public function insertUserBanAll() {
  $user_id = osc_logged_user_id();
  $to_user_id = 0;

  $aSet = array(
    'i_user_id' => $user_id,
    'i_block_user_id' => $to_user_id
  );

  $this->dao->insert( $this->getTable_Ban(), $aSet);
  return $this->dao->insertedId();
}


public function getUserBans($user_id = '') {
  if($user_id == '') {
    $user_id = osc_logged_user_id();
  }

  $this->dao->select('b.i_user_id, b.i_block_user_id, coalesce(u.s_name, "' . osc_esc_html(__('All users blocked', 'online_chat')) . '") as s_name');
  $this->dao->from( $this->getTable_Ban() . ' b' );
  $this->dao->join( $this->getTable_User() . ' as u', 'b.i_block_user_id = u.pk_i_id', 'LEFT OUTER' );

  $this->dao->where('b.i_user_id', $user_id);

  $this->dao->groupby('b.i_user_id, b.i_block_user_id, coalesce(u.s_name, "' . osc_esc_html(__('All users blocked', 'online_chat')) . '")');

  $result = $this->dao->get();
  if( !$result ) { return array(); }
  $prepare = $result->result();
  return $prepare;
}


public function removeUserBan($block_user_id) {
  $user_id = osc_logged_user_id();

  return $this->dao->query('DELETE FROM ' . $this->getTable_Ban() . ' WHERE i_user_id = ' . $user_id . ' AND i_block_user_id = ' . $block_user_id);
}


public function updateUserLastActive($user_id) {
  $aSet = array('dt_access_date' => date("Y-m-d H:i:s"));
  $aWhere = array('pk_i_id' => $user_id);
  
  return $this->_update($this->getTable_User(), $aSet, $aWhere);
}


public function updateChatRead($chat_id, $user_id) {
  $aSet = array('i_read' => 1);
  $aWhere = array('pk_i_chat_id' => $chat_id, 'i_to_user_id' => $user_id, 'i_read' => 0);

  return $this->_update($this->getTable_Chat(), $aSet, $aWhere);
}


public function getUserLastActive($user_id) {
  $this->dao->select('dt_access_date');
  $this->dao->from( $this->getTable_User() );

  $this->dao->where('pk_i_id', $user_id);

  $result = $this->dao->get();
  if(!$result) { 
    return date('Y-m-d H:i:s', strtotime('-5 year')); 
  }

  $row = $result->row();
  
  if(isset($row['dt_access_date'])) {
    return $row['dt_access_date'];
  } else {
    return date('Y-m-d H:i:s', strtotime('-5 year')); 
  }
}


public function getLastChatMessage($chat_id) {
  $this->dao->select('i_end');
  $this->dao->from( $this->getTable_Chat() );

  $this->dao->where('pk_i_chat_id', $chat_id);

  $result = $this->dao->get();
  if( !$result ) { return ''; }
  $row = $result->row();
  return $row['i_end'];
}


public function closeChat($chat_id, $user_id) {
  $close = $this->getLastChatMessage($chat_id);

  if($close == 0) {
    return $this->_update($this->getTable_Chat(), array('i_end' => (int)$user_id), array('pk_i_chat_id' => (int)$chat_id));
    //return $this->dao->query('UPDATE ' . $this->getTable_Chat() . ' SET i_end=' . (int)$user_id . ' WHERE pk_i_chat_id=' . (int)$chat_id);
  } else {
    return $this->dao->query('DELETE FROM ' . $this->getTable_Chat() . ' WHERE pk_i_chat_id = ' . $chat_id);
  }
}


// update function
function _update($table, $values, $where) {
  $this->dao->from($table);
  $this->dao->set($values);
  $this->dao->where($where);
  return $this->dao->update();
}

// End of DAO Class
}
?>