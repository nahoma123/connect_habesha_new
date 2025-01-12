SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_order_item;
CREATE TABLE /*TABLE_PREFIX*/t_osp_order_item(
  pk_i_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fk_i_order_id INT UNSIGNED NOT NULL,
  fk_i_user_id INT,
  fk_i_item_id INT,
  fk_i_shipping_id INT,
  i_quantity INT,
  f_amount FLOAT,
  f_amount_regular FLOAT,
  f_discount FLOAT,
  s_amount_comment VARCHAR(200),
  s_location VARCHAR(200),
  s_title VARCHAR(200),
  s_currency_code VARCHAR(3),
  s_type VARCHAR(20),
  i_status INT,
  f_fee FLOAT,
  f_shipping FLOAT,
  s_comment VARCHAR(500),
  s_comment_alt VARCHAR(500),
  dt_last_update DATETIME,
  dt_date DATETIME,

  PRIMARY KEY (pk_i_id),
  FOREIGN KEY (fk_i_order_id) REFERENCES /*TABLE_PREFIX*/t_osp_order (pk_i_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_shipping;
CREATE TABLE /*TABLE_PREFIX*/t_osp_shipping(
  pk_i_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fk_i_user_id INT UNSIGNED NOT NULL,
  fk_c_country_code CHAR(2),
  fk_i_region_id INT,
  fk_i_city_id INT,
  fk_c_currency_code CHAR(3),
  i_speed INT,
  f_fee FLOAT,
  s_name VARCHAR(50),
  s_description VARCHAR(200),
  s_delivery VARCHAR(100),
  s_logo VARCHAR(30),
  s_type VARCHAR(20),
  i_status INT,
  dt_date DATETIME,

  PRIMARY KEY (pk_i_id),
  FOREIGN KEY (fk_i_user_id) REFERENCES /*TABLE_PREFIX*/t_user (pk_i_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


ALTER TABLE /*TABLE_PREFIX*/t_osp_item_data ADD COLUMN i_shipping INT DEFAULT 0;
ALTER TABLE /*TABLE_PREFIX*/t_osp_item_data ADD COLUMN f_fee FLOAT;
ALTER TABLE /*TABLE_PREFIX*/t_osp_item_data ADD COLUMN s_type VARCHAR(3) DEFAULT NULL;



SET FOREIGN_KEY_CHECKS=1;