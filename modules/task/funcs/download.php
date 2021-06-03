<?php

/**
 * @Project NUKEVIET 4.x
 * @Author hongoctrien (hongoctrien@2mit.org)
 * @Copyright (C) 2015 hongoctrien. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Fri, 04 Sep 2015 23:27:19 GMT
 */
if (!defined('NV_IS_MOD_TASK')) die('Stop!!!');

$id = $nv_Request->get_int('id', 'post,get', 0);
$index = $nv_Request->get_int('index', 'post,get', 0);

if (empty($id) or empty($index)) die('NO');

$files = $db->query('SELECT files FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE id=' . $id)->fetchColumn();
if (!empty($files)) {
    $files = explode(',', $files);
    if(isset($files[$index])){
        if (!nv_is_url($files[$index])) {
            $download = new NukeViet\Files\Download(NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_upload . '/' . $files[$index], NV_UPLOADS_REAL_DIR);
            $download->download_file();
        } else {
            $upload = new NukeViet\Files\Upload($global_config['file_allowed_ext'], $global_config['forbid_extensions'], $global_config['forbid_mimes'], $array_config['maxfilesize'], NV_MAX_WIDTH, NV_MAX_HEIGHT);
            $save_info = $upload->save_urlfile($files[$index], NV_ROOTDIR . '/' . NV_TEMP_DIR);
            if (empty($save_info['error'])) {
                $download = new NukeViet\Files\Download($save_info['name'], NV_ROOTDIR . '/' . NV_TEMP_DIR);
                $download->download_file();
            }
        }
    }
}
