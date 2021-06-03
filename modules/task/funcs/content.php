<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2018 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sun, 07 Jan 2018 13:39:51 GMT
 */
if (!defined('NV_IS_MOD_TASK')) die('Stop!!!');

$workforce_leader = $workforce_member = $workforce_group_member = array();
$array_groups = nv_task_groups_list();
$row = array();
$row['id'] = $nv_Request->get_int('id', 'post,get', 0);
$row['answerid'] = $nv_Request->get_int('answerid', 'post,get', 0);

$array_field_config = array();
$result_field = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_field ORDER BY weight ASC');
while ($row_field = $result_field->fetch()) {
    $language = unserialize($row_field['language']);
    $row_field['title'] = (isset($language[NV_LANG_DATA])) ? $language[NV_LANG_DATA][0] : $row['field'];
    $row_field['description'] = (isset($language[NV_LANG_DATA])) ? nv_htmlspecialchars($language[NV_LANG_DATA][1]) : '';
    if (!empty($row_field['field_choices'])) {
        $row_field['field_choices'] = unserialize($row_field['field_choices']);
    } elseif (!empty($row_field['sql_choices'])) {
        $row_field['sql_choices'] = explode('|', $row_field['sql_choices']);
        $query = 'SELECT ' . $row_field['sql_choices'][2] . ', ' . $row_field['sql_choices'][3] . ' FROM ' . $row_field['sql_choices'][1];
        $result = $db->query($query);
        $weight = 0;
        while (list ($key, $val) = $result->fetch(3)) {
            $row_field['field_choices'][$key] = $val;
        }
    }
    $array_field_config[] = $row_field;
}

$username_alias = change_alias($user_info['username']);
$array_structure_image = array();
$array_structure_image[''] = $module_upload;
$array_structure_image['Y'] = $module_upload . '/' . date('Y');
$array_structure_image['Ym'] = $module_upload . '/' . date('Y_m');
$array_structure_image['Y_m'] = $module_upload . '/' . date('Y/m');
$array_structure_image['Ym_d'] = $module_upload . '/' . date('Y_m/d');
$array_structure_image['Y_m_d'] = $module_upload . '/' . date('Y/m/d');
$array_structure_image['username'] = $module_upload . '/' . $username_alias;

$array_structure_image['username_Y'] = $module_upload . '/' . $username_alias . '/' . date('Y');
$array_structure_image['username_Ym'] = $module_upload . '/' . $username_alias . '/' . date('Y_m');
$array_structure_image['username_Y_m'] = $module_upload . '/' . $username_alias . '/' . date('Y/m');
$array_structure_image['username_Ym_d'] = $module_upload . '/' . $username_alias . '/' . date('Y_m/d');
$array_structure_image['username_Y_m_d'] = $module_upload . '/' . $username_alias . '/' . date('Y/m/d');

$structure_upload = isset($array_config['structure_upload']) ? $array_config['structure_upload'] : 'Ym';
$currentpath = isset($array_structure_image[$structure_upload]) ? $array_structure_image[$structure_upload] : '';

if (file_exists(NV_UPLOADS_REAL_DIR . '/' . $currentpath)) {
    $upload_real_dir_page = NV_UPLOADS_REAL_DIR . '/' . $currentpath;
} else {
    $upload_real_dir_page = NV_UPLOADS_REAL_DIR . '/' . $module_upload;
    $e = explode('/', $currentpath);
    if (!empty($e)) {
        $cp = '';
        foreach ($e as $p) {
            if (!empty($p) and !is_dir(NV_UPLOADS_REAL_DIR . '/' . $cp . $p)) {
                $mk = nv_mkdir(NV_UPLOADS_REAL_DIR . '/' . $cp, $p);
                if ($mk[0] > 0) {
                    $upload_real_dir_page = $mk[2];
                    try {
                        $db->query("INSERT INTO " . NV_UPLOAD_GLOBALTABLE . "_dir (dirname, time) VALUES ('" . NV_UPLOADS_DIR . "/" . $cp . $p . "', 0)");
                    } catch (PDOException $e) {
                        trigger_error($e->getMessage());
                    }
                }
            } elseif (!empty($p)) {
                $upload_real_dir_page = NV_UPLOADS_REAL_DIR . '/' . $cp . $p;
            }
            $cp .= $p . '/';
        }
    }
    $upload_real_dir_page = str_replace('\\', '/', $upload_real_dir_page);
}
$currentpath = str_replace(NV_ROOTDIR . '/', '', $upload_real_dir_page);

if ($row['id'] > 0) {
    $lang_module['task_add'] = $lang_module['task_edit'];
    $row = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE id=' . $row['id'])->fetch();
    if (empty($row) || !nv_check_task_admin($row['useradd'])) {
        Header('Location: ' . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
        die();
    }
    $row['performer'] = $row['performer_old'] = !empty($row['performer']) ? array_map('intval', explode(',', $row['performer'])) : array();
    $row['groups_made'] = !empty($row['groups_made']) ? array_map('intval', explode(',', $row['groups_made'])) : array();
    $row['groups_made_user'] = array();
    if (!empty($row['groups_made'])) {
        $_result = $db->query('SELECT userid FROM ' . NV_USERS_GLOBALTABLE . '_groups_users WHERE approved=1 AND group_id IN (' . implode(',', $row['groups_made']) . ')');
        while (list ($userid) = $_result->fetch(3)) {
            $row['groups_made_user'][] = $userid;
        }
    }
    $row['groups_made_user'] = array_unique($row['groups_made_user']);
    $row['groups_made_user_old'] = array_unique($row['groups_made_user']);

    $row['files'] = !empty($row['files']) ? explode(',', $row['files']) : array();
    $row['files_old'] = $row['files'];

    // field
    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_info WHERE rows_id=' . $row['id'];
    $result = $db->query($sql);
    $custom_fields = $result->fetch();
} else {
    $row['id'] = 0;
    $row['title'] = '';
    $row['performer'] = $row['performer_old'] = $row['groups_made'] = $row['groups_made_old'] = $row['groups_made_user'] = $row['groups_made_user_old'] = array();
    $row['begintime'] = NV_CURRENTTIME;
    $row['exptime'] = 0;
    $row['description'] = '';
    $row['status'] = 0;
    $row['useradd'] = $user_info['userid'];
    $row['priority'] = 2;
    $row['files'] = $row['files_old'] = array();
    $row['projectid'] = $nv_Request->get_int('projectid', 'post,get', 0);
    if ($row['answerid'] > 0) {
        $result = $db->query('SELECT t1.title, t1.addtime, t2.content FROM ' . NV_PREFIXLANG . '_support t1 INNER JOIN ' . NV_PREFIXLANG . '_support_answer t2 ON t1.id=t2.topicid WHERE t2.id=' . $row['answerid']);
        list ($row['title'], $row['begintime'], $row['description']) = $result->fetch(3);
    }
    $custom_field = array();
}

$row['callback'] = $nv_Request->get_string('callback', 'post,get', '');
$row['ajax'] = $nv_Request->get_int('ajax', 'post,get', 0);
$row['redirect'] = $nv_Request->get_string('redirect', 'post,get', '');

if (!$row['ajax'] && !$client_info['is_mobile']) {
    if (defined('NV_EDITOR')) {
        require_once NV_ROOTDIR . '/' . NV_EDITORSDIR . '/' . NV_EDITOR . '/nv.php';
    } elseif (!nv_function_exists('nv_aleditor') and file_exists(NV_ROOTDIR . '/' . NV_EDITORSDIR . '/ckeditor/ckeditor.js')) {
        define('NV_EDITOR', true);
        define('NV_IS_CKEDITOR', true);
        $my_head .= '<script type="text/javascript" src="' . NV_BASE_SITEURL . NV_EDITORSDIR . '/ckeditor/ckeditor.js"></script>';

        function nv_aleditor($textareaname, $width = '100%', $height = '450px', $val = '', $customtoolbar = '')
        {
            global $module_data, $module_name, $currentpath;

            $upload_allow_ckeditor = "filebrowserImageUploadUrl: '" . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . "=upload&editor=ckeditor&responseType=upload&path=" . $currentpath . "&type=image',";

            $return = '<textarea style="width: ' . $width . '; height:' . $height . ';" id="' . $module_data . '_' . $textareaname . '" name="' . $textareaname . '">' . $val . '</textarea>';
            $return .= "<script type=\"text/javascript\">
	CKEDITOR.replace( '" . $module_data . "_" . $textareaname . "', {width: '" . $width . "',height: '" . $height . "'," . $upload_allow_ckeditor . "});
	</script>";
            return $return;
        }
    }
}

if ($nv_Request->isset_request('submit', 'post')) {
    $row['title'] = $nv_Request->get_title('title', 'post', '');
    $row['performer'] = $nv_Request->get_typed_array('performer', 'post', 'int');
    $row['groups_made'] = $nv_Request->get_typed_array('groups_made', 'post', 'int');
    $row['status'] = $nv_Request->get_int('status', 'post', 0);
    $row['files'] = $nv_Request->get_array('files', 'post');

    if (!empty($array_config['allow_useradd'])) {
        $row['useradd'] = $nv_Request->get_int('useradd', 'post', 0);
    } else {
        $row['useradd'] = $user_info['userid'];
    }
    $row['priority'] = $nv_Request->get_int('priority', 'post', 0);

    if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $nv_Request->get_string('begindate', 'post'), $m)) {
        $begintime = $nv_Request->get_string('begintime', 'post');
        $begintime = !empty($begintime) ? explode(':', $begintime) : array(
            0,
            0
        );
        $row['begintime'] = mktime($begintime[0], $begintime[1], 0, $m[2], $m[1], $m[3]);
    } else {
        $row['begintime'] = 0;
    }

    if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $nv_Request->get_string('expdate', 'post'), $m)) {
        $exptime = $nv_Request->get_string('exptime', 'post');
        $exptime = !empty($exptime) ? explode(':', $exptime) : array(
            23,
            59
        );
        $row['exptime'] = mktime($exptime[0], $exptime[1], 59, $m[2], $m[1], $m[3]);
    } else {
        $row['exptime'] = 0;
    }

    if (defined('NV_EDITOR')) {
        $row['description'] = $nv_Request->get_editor('description', '', NV_ALLOWED_HTML_TAGS);
    } else {
        $row['description'] = $nv_Request->get_textarea('description', '', NV_ALLOWED_HTML_TAGS);
    }

    $performer = !empty($row['performer']) ? implode(',', $row['performer']) : '';
    $groups_made = !empty($row['groups_made']) ? implode(',', $row['groups_made']) : '';

    $row['groups_made_user'] = array();
    if (!empty($row['groups_made'])) {
        $_result = $db->query('SELECT userid FROM ' . NV_USERS_GLOBALTABLE . '_groups_users WHERE approved=1 AND group_id IN (' . implode(',', $row['groups_made']) . ')');
        while (list ($userid) = $_result->fetch(3)) {
            $row['groups_made_user'][] = $userid;
        }
        $row['groups_made_user'] = array_unique($row['groups_made_user']);
    }

    if (empty($row['title'])) {
        nv_jsonOutput(array(
            'error' => 1,
            'msg' => $lang_module['error_required_title'],
            'input' => 'title'
        ));
    } elseif (empty($row['performer']) && empty($row['groups_made'])) {
        nv_jsonOutput(array(
            'error' => 1,
            'msg' => $lang_module['error_required_userid'],
            'input' => 'useradd'
        ));
    } elseif (!empty($row['begintime']) && !empty($row['exptime']) && $row['begintime'] > $row['exptime']) {
        nv_jsonOutput(array(
            'error' => 1,
            'msg' => $lang_module['error_vaild_exptime'],
            'input' => 'exptime'
        ));
    }

    if (isset($_FILES['upload_fileupload']) && !empty($_FILES['upload_fileupload']['name'][0])) {
        $array_files = normalizeFiles($_FILES['upload_fileupload']);
        foreach ($array_files as $file) {
            $upload = new NukeViet\Files\Upload($global_config['file_allowed_ext'], $global_config['forbid_extensions'], $global_config['forbid_mimes'], NV_UPLOAD_MAX_FILESIZE, NV_MAX_WIDTH, NV_MAX_HEIGHT);
            $upload_info = $upload->save_file($file, NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_upload, false);
            @unlink($file['tmp_name']);
            if (empty($upload_info['error'])) {
                mt_srand((double) microtime() * 1000000);
                $maxran = 1000000;
                $random_num = mt_rand(0, $maxran);
                $random_num = md5($random_num);
                $nv_pathinfo_filename = nv_pathinfo_filename($upload_info['name']);
                $new_name = NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_upload . '/' . $nv_pathinfo_filename . '.' . $random_num . '.' . $upload_info['ext'];
                $rename = nv_renamefile($upload_info['name'], $new_name);
                if ($rename[0] == 1) {
                    $fileupload = $new_name;
                } else {
                    $fileupload = $upload_info['name'];
                }
                @chmod($fileupload, 0644);
                $row['files'][] = str_replace(NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_upload . '/', '', $fileupload);
            } else {
                nv_jsonOutput(array(
                    'error' => 1,
                    'msg' => $upload_info['error'],
                    'input' => 'listfile'
                ));
            }
            unset($upload, $upload_info);
        }
    }

    // field
    $custom_fields = $nv_Request->get_array('custom_fields', 'post');
    if (!empty($array_field_config)) {
        require NV_ROOTDIR . '/modules/' . $module_file . '/fields.check.php';
    }

    try {
        $new_id = 0;
        $files = !empty($row['files']) ? implode(',', $row['files']) : '';
        if (empty($row['id'])) {
            $_sql = 'INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . ' (title, performer, groups_made, begintime, exptime, description, useradd, projectid, addtime, edittime, status, priority, files) VALUES (:title, :performer, :groups_made, :begintime, :exptime, :description, :useradd, :projectid, ' . NV_CURRENTTIME . ', ' . NV_CURRENTTIME . ', :status, :priority, :files)';
            $data_insert = array();
            $data_insert['title'] = $row['title'];
            $data_insert['performer'] = $performer;
            $data_insert['groups_made'] = $groups_made;
            $data_insert['begintime'] = $row['begintime'];
            $data_insert['exptime'] = $row['exptime'];
            $data_insert['description'] = $row['description'];
            $data_insert['useradd'] = $row['useradd'];
            $data_insert['status'] = $row['status'];
            $data_insert['projectid'] = $row['projectid'];
            $data_insert['priority'] = $row['priority'];
            $data_insert['files'] = $files;
            $new_id = $db->insert_id($_sql, 'id', $data_insert);
        } else {
            $stmt = $db->prepare('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . ' SET title = :title, performer = :performer, groups_made = :groups_made, begintime = :begintime, exptime = :exptime, description = :description, edittime = ' . NV_CURRENTTIME . ', status = :status, priority = :priority, files = :files, useradd = :useradd WHERE id=' . $row['id']);
            $stmt->bindParam(':title', $row['title'], PDO::PARAM_STR);
            $stmt->bindParam(':performer', $performer, PDO::PARAM_STR);
            $stmt->bindParam(':groups_made', $groups_made, PDO::PARAM_STR);
            $stmt->bindParam(':begintime', $row['begintime'], PDO::PARAM_INT);
            $stmt->bindParam(':exptime', $row['exptime'], PDO::PARAM_INT);
            $stmt->bindParam(':description', $row['description'], PDO::PARAM_STR, strlen($row['description']));
            $stmt->bindParam(':status', $row['status'], PDO::PARAM_INT);
            $stmt->bindParam(':useradd', $row['useradd'], PDO::PARAM_INT);
            $stmt->bindParam(':priority', $row['priority'], PDO::PARAM_INT);
            $stmt->bindParam(':files', $files, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $new_id = $row['id'];
            }
        }

        if ($new_id > 0) {

            if ($row['id'] > 0) {
                if (!empty($array_field_config)) {
                    $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_info SET ' . implode(', ', $query_field) . ' WHERE rows_id=' . $new_id);
                }
            } else {
                $query_field['rows_id'] = $new_id;
                $db->query('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_info (' . implode(', ', array_keys($query_field)) . ') VALUES (' . implode(', ', array_values($query_field)) . ')');
            }

            if ($row['performer'] != $row['performer_old']) {
                foreach ($row['performer_old'] as $userid) {
                    if (!in_array($userid, $row['performer'])) {
                        $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_follow WHERE userid = ' . $userid . ' AND taskid=' . $new_id . ' AND type=0');
                    }
                }

                $sth = $db->prepare('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_follow (taskid, userid) VALUES( :taskid, :userid)');
                foreach ($row['performer'] as $userid) {
                    if (!in_array($userid, $row['performer_old'])) {
                        $sth->bindParam(':taskid', $new_id, PDO::PARAM_INT);
                        $sth->bindParam(':userid', $userid, PDO::PARAM_INT);
                        $sth->execute();
                    }
                }
            }

            if ($row['groups_made_user'] != $row['groups_made_user_old']) {
                $sth = $db->prepare('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_follow (taskid, userid, type) VALUES( :taskid, :userid, 1)');
                foreach ($row['groups_made_user'] as $userid) {
                    if (!in_array($userid, $row['groups_made_user_old'])) {
                        $sth->bindParam(':taskid', $new_id, PDO::PARAM_INT);
                        $sth->bindParam(':userid', $userid, PDO::PARAM_INT);
                        $sth->execute();
                    }
                }

                foreach ($row['groups_made_user_old'] as $userid) {
                    if (!in_array($userid, $row['groups_made_user'])) {
                        $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_follow WHERE userid = ' . $userid . ' AND taskid=' . $new_id . ' AND type=1');
                    }
                }
            }

            if ($row['files'] != $row['files_old']) {
                foreach ($row['files_old'] as $path) {
                    if (!in_array($path, $row['files'])) {
                        if (file_exists(NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_upload . '/' . $path)) {
                            nv_deletefile(NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_upload . '/' . $path);
                        }
                    }
                }
            }

            if (!empty($row['redirect'])) {
                $redirect = nv_redirect_decrypt($row['redirect']);
            } else {
                $redirect = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=detail&id=' . $new_id;
            }

            if (empty($row['id'])) {
                // thêm vào task list dự án
                if (isset($site_mods['projects']) && $row['projectid']) {
                    $stmt = $db->prepare('INSERT INTO ' . NV_PREFIXLANG . '_projects_task (taskid, projectid) VALUES (:taskid, :projectid)');
                    $stmt->bindParam(':taskid', $new_id, PDO::PARAM_INT);
                    $stmt->bindParam(':projectid', $row['projectid'], PDO::PARAM_INT);
                    $stmt->execute();
                }

                // notification
                if (isset($site_mods['notification']) && file_exists(NV_ROOTDIR . '/modules/notification/site.functions.php')) {
                    require_once NV_ROOTDIR . '/modules/notification/site.functions.php';

                    $url = NV_MY_DOMAIN . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=detail&id=' . $new_id;

                    // gửi thông báo cho danh sách người thực hiện
                    $content = sprintf($lang_module['new_task'], $row['title']);
                    nv_send_notification($row['performer'], $content, 'new_task', $module_name, $url);

                    // gửi thông báo cho danh sách người trong nhóm thực hiện
                    $content = sprintf($lang_module['new_task_groups'], $row['title']);
                    nv_send_notification($row['groups_made_user'], $content, 'new_task', $module_name, $url);

                    // gửi thông báo nếu người giao không phải là người đang thực hiện thêm task
                    if ($row['useradd'] != $user_info['userid']) {
                        $array_userid = array(
                            $row['useradd']
                        );
                        $content = sprintf($lang_module['new_task_add'], $workforce_assign[$user_info['userid']]['fullname'], $row['title']);
                        nv_send_notification($array_userid, $content, 'new_task_add', $module_name, $url);
                    }
                }

                // gui mail thong bao
                $message = $db->query('SELECT econtent FROM ' . NV_PREFIXLANG . '_' . $module_data . '_econtent WHERE action="newtask"')->fetchColumn();
                if (!empty($message)) {
                    $user_working = array();
                    if (!empty($row['performer'])) {
                        foreach ($row['performer'] as $userid) {
                            if ($userid != $user_info['userid']) {
                                $user_working[] = $workforce_assign[$userid]['fullname'];
                            }
                        }
                    }
                    if (!empty($row['groups_made'])) {
                        foreach ($row['groups_made'] as $groupid) {
                            $user_working[] = $array_groups[$groupid];
                        }
                    }
                    $user_working = !empty($user_working) ? implode(', ', $user_working) : '';

                    $array_replace = array(
                        'SITE_NAME' => $global_config['site_name'],
                        'TITLE' => $row['title'],
                        'USER_ADD' => $workforce_assign[$user_info['userid']]['fullname'],
                        'USER_WORKING' => $user_working,
                        'TIME_START' => !empty($row['begintime']) ? nv_date('H:I d/m/Y', $row['begintime']) : '',
                        'TIME_END' => !empty($row['exptime']) ? nv_date('H:I d/m/Y', $row['exptime']) : '',
                        'CONTENT' => $row['description'],
                        'STATUS' => $lang_module['task_status_' . $row['status']],
                        'TASK_URL' => NV_MY_DOMAIN . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=detail&amp;id=' . $new_id
                    );
                    $message = nv_unhtmlspecialchars($message);
                    foreach ($array_replace as $index => $value) {
                        $message = str_replace('[' . $index . ']', $value, $message);
                    }

                    $array_userid = $row['performer'];
                    if (!empty($row['groups_made_user'])) {
                        $array_userid = array_merge($array_userid, $row['groups_made_user']);
                        $array_userid = array_unique($array_userid);
                    }

                    foreach ($array_userid as $userid) {
                        nv_sendmail($workforce_assign[$user_info['userid']]['email'], $workforce_assign[$userid]['email'], $row['title'], $message, '', false, true);
                    }

                    $url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=detail&amp;id=' . $new_id;
                    if (empty($row['id'])) {
                        nv_insert_logs(NV_LANG_DATA, $module_name, $lang_module['task_add'], sprintf($lang_module['logs_create_task'], $row['title']), $user_info['userid'], $url);
                    } else {
                        nv_insert_logs(NV_LANG_DATA, $module_name, $lang_module['task_edit'], sprintf($lang_module['logs_edit_task'], $row['title']), $user_info['userid'], $url);
                    }

                    $nv_Cache->delMod($module_name);
                  
                } else {
                    //
                }
            }
            die((json_encode(array(
                'error' => 0,
                'redirect' => $redirect,
                'ajax' => $row['ajax'],
                'projectid' => $row['projectid']
            ))));
        }
    } catch (PDOException $e) {
        trigger_error($e->getMessage());
        nv_jsonOutput(array(
            'error' => 1,
            'msg' => $lang_module['error_unknow']
        ));
    }
}

$row['begindate'] = !empty($row['begintime']) ? date('d/m/Y', $row['begintime']) : '';
$row['begintime'] = !empty($row['begintime']) ? date('H:i', $row['begintime']) : '';
$row['expdate'] = !empty($row['exptime']) ? date('d/m/Y', $row['exptime']) : '';
$row['exptime'] = !empty($row['exptime']) ? date('H:i', $row['exptime']) : '';

$row['description'] = htmlspecialchars(nv_editor_br2nl($row['description']));
if (defined('NV_EDITOR') and nv_function_exists('nv_aleditor')) {
    $row['description'] = nv_aleditor('description', '100%', '300px', $row['description']);
} else {
    $row['description'] = '<textarea style="width:100%;height:100px" class="form-control" name="description">' . $row['description'] . '</textarea>';
}

if ($array_config['wlist'] == 0) {
    foreach (array_keys($workforce_list) as $userid) {
        if (in_array($userid, $array_leader)) {
            $workforce_leader[$userid] = $workforce_list[$userid];
        }
    }

    foreach (array_keys($workforce_list) as $userid) {
        if (in_array($userid, $array_userid) && !in_array($userid, $array_leader)) {
            $workforce_member[$userid] = $workforce_list[$userid];
        }
    }
} elseif ($array_config['wlist'] == 1) {
    $result = $db->query('SELECT t1.userid, t1.group_id, t2.email, first_name, last_name, username FROM ' . NV_USERS_GLOBALTABLE . '_groups_users t1 INNER JOIN ' . NV_USERS_GLOBALTABLE . ' t2 ON t1.userid=t2.userid INNER JOIN ' . NV_USERS_GLOBALTABLE . '_groups t3 ON t1.group_id=t3.group_id WHERE t1.group_id IN (' . $array_config['groups_use'] . ') ORDER BY t3.weight');
    while ($_row = $result->fetch()) {
        $_row['fullname'] = nv_show_name_user($_row['first_name'], $_row['last_name'], $_row['username']);
        $workforce_group_member[$_row['group_id']][$_row['userid']] = $_row;
    }
}

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('TEMPLATE', $module_info['template']);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('MODULE_UPLOAD', $module_upload);
$xtpl->assign('OP', $op);
$xtpl->assign('ROW', $row);

if ($array_config['wlist'] == 0) {
    if (!empty($workforce_leader)) {
        foreach ($workforce_leader as $user) {
            $user['selected'] = in_array($user['userid'], $row['performer']) ? 'selected="selected"' : '';
            $xtpl->assign('USER', $user);
            $xtpl->parse('main.wlist_0.leader.loop');
        }
        $xtpl->parse('main.wlist_0.leader');
    }

    if (!empty($workforce_member)) {
        foreach ($workforce_member as $user) {
            $user['selected'] = in_array($user['userid'], $row['performer']) ? 'selected="selected"' : '';
            $xtpl->assign('USER', $user);
            $xtpl->parse('main.wlist_0.member.loop');
        }
        $xtpl->parse('main.wlist_0.member');
    }
    $xtpl->parse('main.wlist_0');
} elseif ($array_config['wlist'] == 1) {
    if (!empty($workforce_group_member)) {
        foreach ($workforce_group_member as $group_id => $user) {
            if (!empty($user)) {
                $xtpl->assign('GROUP', $array_groups[$group_id]);
                foreach ($user as $u) {
                    $u['selected'] = in_array($u['userid'], $row['performer']) ? 'selected="selected"' : '';
                    $xtpl->assign('USER', $u);
                    $xtpl->parse('main.wlist_1.group.user');
                }
                $xtpl->parse('main.wlist_1.group');
            }
        }
    }
    $xtpl->parse('main.wlist_1');
}

foreach ($array_priority as $index => $value) {
    $sl = $index == $row['priority'] ? 'selected="selected"' : '';
    $xtpl->assign('VALUE', array(
        'index' => $index,
        'value' => $value,
        'selected' => $sl
    ));
    $xtpl->parse('main.looppriority');
}

if (!empty($array_config['allow_useradd'])) {
    if (!empty($workforce_assign)) {
        foreach ($workforce_assign as $user) {
            $user['selected'] = $user['userid'] == $row['useradd'] ? 'selected="selected"' : '';
            $xtpl->assign('USERADD', $user);
            $xtpl->parse('main.allow_useradd.useradd');
        }
    }
    $xtpl->parse('main.allow_useradd');
}

foreach ($array_task_status as $index => $value) {
    $sl = $index == $row['status'] ? 'selected="selected"' : '';
    $xtpl->assign('STATUS', array(
        'index' => $index,
        'value' => $value,
        'selected' => $sl
    ));
    $xtpl->parse('main.status');
}

foreach ($array_groups as $index => $value) {
    $xtpl->assign('GROUP', array(
        'index' => $index,
        'value' => $value,
        'selected' => in_array($index, $row['groups_made']) ? 'selected="selected"' : ''
    ));
    $xtpl->parse('main.groups');
}

if (!empty($array_field_config)) {
    foreach ($array_field_config as $_row) {
        if ($row['id'] == 0 and empty($custom_fields)) {
            if (!empty($_row['field_choices'])) {
                if ($_row['field_type'] == 'date') {
                    $_row['value'] = ($_row['field_choices']['current_date']) ? NV_CURRENTTIME : $_row['default_value'];
                } elseif ($_row['field_type'] == 'number') {
                    $_row['value'] = $_row['default_value'];
                } else {
                    $temp = array_keys($_row['field_choices']);
                    $tempkey = intval($_row['default_value']) - 1;
                    $_row['value'] = (isset($temp[$tempkey])) ? $temp[$tempkey] : '';
                }
            } else {
                $_row['value'] = $_row['default_value'];
            }
        } else {
            $_row['value'] = (isset($custom_fields[$_row['field']])) ? $custom_fields[$_row['field']] : $_row['default_value'];
        }
        $_row['required'] = ($_row['required']) ? 'required' : '';
        $xtpl->assign('FIELD', $_row);
        if ($_row['required']) {
            $xtpl->parse('main.field.loop.required');
        }
        if ($_row['field_type'] == 'textbox' or $_row['field_type'] == 'number') {
            $xtpl->parse('main.field.loop.textbox');
        } elseif ($_row['field_type'] == 'date') {
            $_row['value'] = (empty($_row['value'])) ? '' : date('d/m/Y', $_row['value']);
            $xtpl->assign('FIELD', $_row);
            $xtpl->parse('main.field.loop.date');
        } elseif ($_row['field_type'] == 'textarea') {
            $_row['value'] = nv_htmlspecialchars(nv_br2nl($_row['value']));
            $xtpl->assign('FIELD', $_row);
            $xtpl->parse('main.field.loop.textarea');
        } elseif ($_row['field_type'] == 'editor') {
            $_row['value'] = htmlspecialchars(nv_editor_br2nl($_row['value']));
            if (defined('NV_EDITOR') and nv_function_exists('nv_aleditor')) {
                $array_tmp = explode('@', $_row['class']);
                $edits = nv_aleditor('custom_fields[' . $_row['field'] . ']', $array_tmp[0], $array_tmp[1], $_row['value']);
                $xtpl->assign('EDITOR', $edits);
                $xtpl->parse('main.field.loop.editor');
            } else {
                $_row['class'] = '';
                $xtpl->assign('FIELD', $_row);
                $xtpl->parse('main.field.loop.textarea');
            }
        } elseif ($_row['field_type'] == 'select') {
            foreach ($_row['field_choices'] as $key => $value) {
                $xtpl->assign('FIELD_CHOICES', array(
                    'key' => $key,
                    'selected' => ($key == $_row['value']) ? ' selected="selected"' : '',
                    'value' => $value
                ));
                $xtpl->parse('main.field.loop.select.loop');
            }
            $xtpl->parse('main.field.loop.select');
        } elseif ($_row['field_type'] == 'radio') {
            $number = 0;
            foreach ($_row['field_choices'] as $key => $value) {
                $xtpl->assign('FIELD_CHOICES', array(
                    'id' => $_row['fid'] . '_' . $number++,
                    'key' => $key,
                    'checked' => ($key == $_row['value']) ? ' checked="checked"' : '',
                    'value' => $value
                ));
                $xtpl->parse('main.field.loop.radio');
            }
        } elseif ($_row['field_type'] == 'checkbox') {
            $number = 0;
            $valuecheckbox = (!empty($_row['value'])) ? explode(',', $_row['value']) : array();
            foreach ($_row['field_choices'] as $key => $value) {
                $xtpl->assign('FIELD_CHOICES', array(
                    'id' => $_row['fid'] . '_' . $number++,
                    'key' => $key,
                    'checked' => (in_array($key, $valuecheckbox)) ? ' checked="checked"' : '',
                    'value' => $value
                ));
                $xtpl->parse('main.field.loop.checkbox');
            }
        } elseif ($_row['field_type'] == 'multiselect') {
            foreach ($_row['field_choices'] as $key => $value) {
                $xtpl->assign('FIELD_CHOICES', array(
                    'key' => $key,
                    'selected' => ($key == $_row['value']) ? ' selected="selected"' : '',
                    'value' => $value
                ));
                $xtpl->parse('main.field.loop.multiselect.loop');
            }
            $xtpl->parse('main.field.loop.multiselect');
        }
        $xtpl->parse('main.field.loop');
    }
    $xtpl->parse('main.field');
}

$i = 0;
if (!empty($row['files']) and !empty($row['id'])) {
    $path = '';
    if (file_exists(NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_upload . '/' . $path)) {
        foreach ($row['files'] as $path) {
            $xtpl->assign('FILES', array(
                'index' => $i++,
                'path' => NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_upload . '/' . $path,
                'basename' => basename($path)
            ));
            $xtpl->parse('main.view_files');
        }
    }
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$page_title = $lang_module['task_add'];
$array_mod_title[] = array(
    'title' => $page_title,
    'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op
);

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents, !$row['ajax']);
include NV_ROOTDIR . '/includes/footer.php';