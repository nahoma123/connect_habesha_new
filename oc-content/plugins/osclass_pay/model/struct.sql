SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_log;
CREATE TABLE /*TABLE_PREFIX*/t_osp_log (
  pk_i_id INT NOT NULL AUTO_INCREMENT,
  s_concept VARCHAR(200) NOT NULL,
  dt_date DATETIME NOT NULL,
  s_code VARCHAR(255) NOT NULL,
  f_amount FLOAT NOT NULL,
  i_amount BIGINT(40) NULL,
  s_currency_code VARCHAR(3) NULL,
  s_email VARCHAR(200) NULL,
  fk_i_user_id INT NULL,
  s_cart VARCHAR(5000),
  s_source VARCHAR(20) NOT NULL,
  i_product_type VARCHAR(20) NOT NULL,

  PRIMARY KEY(pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_wallet;
CREATE TABLE /*TABLE_PREFIX*/t_osp_wallet (
  fk_i_user_id INT UNSIGNED NOT NULL,
  i_amount BIGINT(20) NULL,

  PRIMARY KEY (fk_i_user_id),
  FOREIGN KEY (fk_i_user_id) REFERENCES /*TABLE_PREFIX*/t_user (pk_i_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_item;
CREATE TABLE /*TABLE_PREFIX*/t_osp_item (
  pk_i_id INT NOT NULL AUTO_INCREMENT,
  i_item_id INT UNSIGNED NOT NULL,
  s_type VARCHAR(3),
  i_paid SMALLINT NOT NULL,
  i_hours VARCHAR(10),
  i_repeat INT,
  dt_date DATETIME NOT NULL,
  dt_expire DATETIME,
  s_keyword VARCHAR(250) NULL,
  fk_i_payment_id INT NOT NULL,

  PRIMARY KEY (pk_i_id),
  FOREIGN KEY (i_item_id) REFERENCES /*TABLE_PREFIX*/t_item (pk_i_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';



DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_price_category;
CREATE TABLE /*TABLE_PREFIX*/t_osp_price_category (
  pk_i_id INT NOT NULL AUTO_INCREMENT,
  fk_i_category_id INT UNSIGNED NOT NULL,
  s_type VARCHAR(3),
  i_hours VARCHAR(10) NULL,
  f_fee FLOAT NULL,

  PRIMARY KEY (pk_i_id),
  FOREIGN KEY (fk_i_category_id) REFERENCES /*TABLE_PREFIX*/t_category (pk_i_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_price_location;
CREATE TABLE /*TABLE_PREFIX*/t_osp_price_location (
  pk_i_id INT NOT NULL AUTO_INCREMENT,
  fk_c_country_code CHAR(2),
  fk_i_region_id INT,
  s_type VARCHAR(3),
  f_fee FLOAT NULL,

  PRIMARY KEY (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_user_group;
CREATE TABLE /*TABLE_PREFIX*/t_osp_user_group (
  pk_i_id INT NOT NULL AUTO_INCREMENT,
  s_name VARCHAR(20),
  s_description VARCHAR(500),
  f_price FLOAT NULL,
  i_discount INT,
  i_days INT,
  s_color VARCHAR(8),
  s_category VARCHAR(1000),
  i_pbonus INT,
  s_custom VARCHAR(100),
  i_rank INT,
  i_attr INT,
  i_max_items INT DEFAULT 10,
  i_max_items_days INT DEFAULT 30,
  i_free_items_101 INT DEFAULT 0,
  i_free_items_201 INT DEFAULT 0,
  i_free_items_401 INT DEFAULT 0,

  PRIMARY KEY (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';



DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_user_to_group;
CREATE TABLE /*TABLE_PREFIX*/t_osp_user_to_group (
  fk_i_user_id INT UNSIGNED NOT NULL,
  fk_i_group_id INT NULL,
  dt_expire DATETIME,

  PRIMARY KEY (fk_i_user_id),
  FOREIGN KEY (fk_i_user_id) REFERENCES /*TABLE_PREFIX*/t_user (pk_i_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (fk_i_group_id) REFERENCES /*TABLE_PREFIX*/t_osp_user_group (pk_i_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';



DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_pack;
CREATE TABLE /*TABLE_PREFIX*/t_osp_pack (
  pk_i_id INT NOT NULL AUTO_INCREMENT,
  s_name VARCHAR(20),
  s_description VARCHAR(500),
  f_price FLOAT,
  f_extra FLOAT,
  i_group INT,
  s_color VARCHAR(8),

  PRIMARY KEY (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_user_cart;
CREATE TABLE /*TABLE_PREFIX*/t_osp_user_cart (
  fk_i_user_id INT UNSIGNED NOT NULL,
  s_content VARCHAR(5000),

  PRIMARY KEY (fk_i_user_id),
  FOREIGN KEY (fk_i_user_id) REFERENCES /*TABLE_PREFIX*/t_user (pk_i_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_bank_transfer;
CREATE TABLE /*TABLE_PREFIX*/t_osp_bank_transfer(
  pk_i_id INT NOT NULL AUTO_INCREMENT,
  i_user_id INT NULL,
  s_transaction VARCHAR(20),
  s_variable VARCHAR(20),
  s_cart VARCHAR(5000), 
  s_description VARCHAR(500),
  s_extra VARCHAR(5000) NULL,
  i_paid INT,
  f_price FLOAT,
  dt_date DATETIME,
  dt_date_paid DATETIME,

  PRIMARY KEY (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_banner;
CREATE TABLE /*TABLE_PREFIX*/t_osp_banner(
  pk_i_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fk_i_user_id INT,
  fk_s_banner_id VARCHAR(500),
  i_type INT(1),
  s_name VARCHAR(100),
  s_key VARCHAR(100),
  s_url VARCHAR(500),
  s_code VARCHAR(5000),
  d_price_click DECIMAL(10, 3) DEFAULT 0,
  d_price_view DECIMAL(10, 3) DEFAULT 0,
  d_budget DECIMAL(10, 3) DEFAULT 0,
  dt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  s_category VARCHAR(100),
  s_size_width VARCHAR(10),
  s_size_height VARCHAR(10),
  i_status INT(1) DEFAULT 0,
  s_comment VARCHAR(1000),
  i_ba_advert_id INT,

  PRIMARY KEY (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_currency_rate;
CREATE TABLE /*TABLE_PREFIX*/t_osp_currency_rate(
  s_from VARCHAR(3) NOT NULL,
  s_to VARCHAR(3) NOT NULL,
  f_rate FLOAT NULL DEFAULT 1.0,
  dt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (s_from, s_to)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_order;
CREATE TABLE /*TABLE_PREFIX*/t_osp_order(
  pk_i_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fk_i_user_id INT,
  s_cart VARCHAR(5000),
  s_item_id VARCHAR(1000),
  f_amount FLOAT,
  f_amount_regular FLOAT,
  s_amount_comment VARCHAR(200),
  i_discount INT,
  s_currency_code VARCHAR(3),
  i_status INT,
  s_comment VARCHAR(500),
  dt_date DATETIME,
  fk_i_payment_id INT NOT NULL,

  PRIMARY KEY (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


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


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_item_data;
CREATE TABLE /*TABLE_PREFIX*/t_osp_item_data(
  fk_i_item_id INT,
  i_sell INT DEFAULT 0,
  i_quantity INT DEFAULT 1,
  i_shipping INT DEFAULT 0,
  f_fee FLOAT,
  s_type VARCHAR(3) DEFAULT NULL,

  PRIMARY KEY (fk_i_item_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_pending;
CREATE TABLE /*TABLE_PREFIX*/t_osp_pending (
  pk_i_id INT NOT NULL AUTO_INCREMENT,
  s_transaction_id VARCHAR(100) NULL,
  fk_i_user_id INT NULL,
  s_email VARCHAR(100) NULL,
  s_extra VARCHAR(5000) NULL,
  s_source VARCHAR(20) NULL,
  dt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY(pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_pack_locale;
CREATE TABLE /*TABLE_PREFIX*/t_osp_pack_locale (
  fk_i_pack_id INT NOT NULL,
  fk_c_locale_code CHAR(5) NOT NULL,
  s_name VARCHAR(20),
  s_description VARCHAR(500),

  PRIMARY KEY(fk_i_pack_id, fk_c_locale_code),
  FOREIGN KEY (fk_i_pack_id) REFERENCES /*TABLE_PREFIX*/t_osp_pack (pk_i_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';


DROP TABLE IF EXISTS /*TABLE_PREFIX*/t_osp_user_group_locale;
CREATE TABLE /*TABLE_PREFIX*/t_osp_user_group_locale (
  fk_i_group_id INT NOT NULL,
  fk_c_locale_code CHAR(5) NOT NULL,
  s_name VARCHAR(20),
  s_description VARCHAR(500),
  s_custom VARCHAR(100),

  PRIMARY KEY(fk_i_group_id, fk_c_locale_code),
  FOREIGN KEY (fk_i_group_id) REFERENCES /*TABLE_PREFIX*/t_osp_user_group (pk_i_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';



INSERT INTO /*TABLE_PREFIX*/t_osp_user_group (s_name, s_description, f_price, i_discount, i_days, s_color, s_category, i_pbonus, i_attr) VALUES
('Sliver', 'Members get 10% flat discount.', 29, 10, 90, '#70C1B3', '', 5, 0),
('Gold', 'Members get 20% flat discount.', 59, 20, 90, '#EFB237', '', 10, 0),
('Platinum', 'Members get 30% flat discount.', 99, 30, 90, '#D90429', '', 20, 0);


INSERT INTO /*TABLE_PREFIX*/t_osp_pack (s_name, s_description, f_price, f_extra, i_group, s_color) VALUES
('Pack #1', 'Buy pack and get 20% more credits!', 10, 2, 0, '#E9C46A'),
('Pack #2', 'Buy pack and get 40% more credits!', 30, 12, 0, '#F4A261'),
('Pack #3', 'Buy pack and get 60% more credits!', 60, 36, 0, '#E76F51');


SET FOREIGN_KEY_CHECKS=1;