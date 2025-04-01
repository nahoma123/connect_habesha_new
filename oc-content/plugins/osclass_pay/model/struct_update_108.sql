SET FOREIGN_KEY_CHECKS=0;

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


SET FOREIGN_KEY_CHECKS=1;