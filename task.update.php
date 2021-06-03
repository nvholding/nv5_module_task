<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Fri, 30 Dec 2016 01:40:16 GMT
 */
define('NV_SYSTEM', true);

define('NV_ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));

require NV_ROOTDIR . '/includes/mainfile.php';
require NV_ROOTDIR . '/includes/core/user_functions.php';

$array_database = array(
    $db_config['dbname']
);
if (defined('NV_CONFIG_DIR')) {
    $result = $db->query('SELECT dataname FROM ' . $db_config['prefix'] . '_site');
    while (list ($dataname) = $result->fetch(3)) {
        $array_database[] = $dataname;
    }
}

foreach ($array_database as $dataname) {
    $language_query = $db->query('SELECT lang FROM ' . $dataname . '.' . $db_config['prefix'] . '_setup_language WHERE setup = 1');
    while (list ($lang) = $language_query->fetch(3)) {
        $mquery = $db->query("SELECT title, module_data FROM " . $dataname . "." . $db_config['prefix'] . "_" . $lang . "_modules WHERE module_file = 'task'");
        while (list ($mod, $mod_data) = $mquery->fetch(3)) {
            $_sql = array();

            $data = array(
                'groups_manage' => '1',
                'groups_use' => '6',
                'default_status' => '0,1,2',
                'allow_useradd' => 1,
                'wlist' => 0,
                'resize' => '800x800',
                'upload_chanel' => 'local', // local, s3
                's3_bucket' => '',
                's3_key' => '',
                's3_secret' => '',
                's3_version' => 'latest',
                's3_region' => '',
                'structure_upload' => 'Ym'
            );
            foreach ($data as $config_name => $config_value) {
                $_sql[] = "INSERT INTO " . $dataname . "." . NV_CONFIG_GLOBALTABLE . " (lang, module, config_name, config_value) VALUES ('" . $lang . "', " . $db->quote($mod) . ", " . $db->quote($config_name) . ", " . $db->quote($config_value) . ")";
            }

            $_sql[] = "INSERT INTO " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_econtent (action, econtent) VALUES('cpltask', 'Xin chào <strong>&#91;USER_ADD&#93;</strong>!<br  /><br  />Công việc&nbsp;<strong>&#91;TITLE&#93;</strong> giao cho&nbsp;<strong>&#91;USER_WORKING&#93; </strong>đã hoàn thành.<br  />Dưới đây là thông tin chi tiết tại <strong>&#91;SITE_NAME&#93;</strong>:<ul><li><strong>Tiêu đề: </strong>&#91;TITLE&#93;</li><li><strong>Người giao việc:</strong> &#91;USER_ADD&#93;</li><li><strong>Người thực hiện</strong>: &#91;USER_WORKING&#93;</li><li><strong>Thời gian bắt đầu:</strong> &#91;TIME_START&#93;</li><li><strong>Thời gian hoàn thành:</strong> &#91;TIME_END&#93;</li><li><strong>Thời gian hoàn thành thực tế:</strong> &#91;TIME_REAL&#93;</li><li><strong>Trạng thái:</strong> &#91;STATUS&#93;</li></ul>&#91;CONTENT&#93;<br  /><br  />Theo dõi và cập nhật tiến độ công việc tại &#91;TASK_URL&#93;')";

            $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . " ADD projectid  mediumint(8) unsigned NOT NULL DEFAULT '0' AFTER useradd;";

            $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_performer ADD readed TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER follow;";

            $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . " ADD priority TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER status;";

            $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . " ADD realtime INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER exptime;";

            $_sql[] = "rename table " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_performer to " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_follow";

            $_sql[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_field (
            	fid mediumint(8) NOT NULL AUTO_INCREMENT,
            	field varchar(25) NOT NULL,
            	weight int(10) unsigned NOT NULL DEFAULT '1',
            	field_type enum('number','date','textbox','textarea','editor','select','radio','checkbox','multiselect') NOT NULL DEFAULT 'textbox',
            	field_choices text NOT NULL,
            	sql_choices text NOT NULL,
            	match_type enum('none','alphanumeric','email','url','regex','callback') NOT NULL DEFAULT 'none',
            	match_regex varchar(250) NOT NULL DEFAULT '',
            	func_callback varchar(75) NOT NULL DEFAULT '',
            	min_length int(11) NOT NULL DEFAULT '0',
            	max_length bigint(20) unsigned NOT NULL DEFAULT '0',
            	required tinyint(3) unsigned NOT NULL DEFAULT '0',
            	show_profile tinyint(4) NOT NULL DEFAULT '1',
            	class varchar(50) NOT NULL DEFAULT '',
            	language text NOT NULL,
            	default_value varchar(255) NOT NULL DEFAULT '',
            	PRIMARY KEY (fid),
            	UNIQUE KEY field (field)
            ) ENGINE=MyISAM";

            $_sql[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_info (
            	rows_id mediumint(8) unsigned NOT NULL,
            	PRIMARY KEY (rows_id)
            ) ENGINE=MyISAM";

            $_sql[] = "INSERT INTO " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_econtent (action, econtent) VALUES('print', '<strong>[SITE_NAME]</strong>: Tiêu đề website<br /> <strong>[TITLE]</strong>: Tiêu đề<br /> <strong>[USER_ADD]</strong>: Người giao việc<br /> <strong>[USER_WORKING]</strong>: Người thực hiện<br /> <strong>[TIME_START]</strong>: Thời gian bắt đầu<br /> <strong>[TIME_END]</strong>: Thời gian hoàn thành<br /> <strong>[CONTENT]</strong>: Nội dung công việc<br /> <strong>[STATUS]</strong>: Trạng thái<br /> <strong>[TASK_URL]</strong>: URL chi tiết')";

            $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . " ADD groups_made VARCHAR(255) NOT NULL AFTER performer;";

            $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_follow ADD type TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER readed;";

            $_sql[] = "ALTER " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . "_follow DROP INDEX taskid, ADD UNIQUE taskid (taskid, userid, type)";

            $_sql[] = "ALTER TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $mod_data . " ADD files TEXT NOT NULL AFTER priority;";

            $db->query("UPDATE " . $db_config['prefix'] . "_setup_extensions SET version='1.0.01 " . NV_CURRENTTIME . "' WHERE type='module' and basename=" . $db->quote($mod));

            if (!empty($_sql)) {
                foreach ($_sql as $sql) {
                    try {
                        $db->query($sql);
                    } catch (PDOException $e) {
                        //
                    }
                }
                $nv_Cache->delMod($mod);
                $nv_Cache->delMod('settings');
            }
        }
    }
}

die('OK');
