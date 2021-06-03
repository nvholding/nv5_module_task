<!-- BEGIN: main -->
<ul class="pull-right list-inline">
    <li><a href="{CONTROL.url_print}" id="btn-print" class="btn btn-primary btn-xs"><em class="fa fa-print">&nbsp;</em>{LANG.print}</a></li>
    <li><a href="{CONTROL.url_change_read}" class="btn btn-primary btn-xs"><em class="fa fa-sign-out">&nbsp;</em>{LANG.change_read}</a></li>
    <li><a href="{CONTROL.url_add}" class="btn btn-primary btn-xs"><em class="fa fa-sign-in">&nbsp;</em>{LANG.task_add}</a></li>
    <!-- BEGIN: admin -->
    <li><a href="{CONTROL.url_edit}" class="btn btn-default btn-xs"><em class="fa fa-edit">&nbsp;</em>{LANG.task_edit}</a></li>
    <li><a href="{CONTROL.url_delete}" class="btn btn-danger btn-xs" onclick="return confirm(nv_is_del_confirm[0]);"><em class="fa fa-trash-o">&nbsp;</em>{LANG.delete}</a></li>
    <!-- END: admin -->
</ul>
<div class="clearfix"></div>
<div class="row">
    <div class="col-xs-24 col-sm-12 col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">{ROW.title}</div>
            <div class="panel-body">
                <ul class="list-info m-bottom">
                    <!-- BEGIN: project -->
                    <li><label>{LANG.project}</label><a href="{ROW.project.link}">{ROW.project.title}</a></li>
                    <!-- END: project -->
                    <li><label>{LANG.useradd}</label>{ROW.useradd_str}</li>
                    <li><label>{LANG.performer}</label>{ROW.performer_str}</li>
                    <li><label>{LANG.begintime}</label>{ROW.begintime}</li>
                    <li><label>{LANG.exptime}</label>{ROW.exptime}</li>
                    <li><label>{LANG.realtime}</label>{ROW.realtime}</li>
                    <li><label class="pull-left" style="margin-top: 6px">{LANG.status}</label> <select class="form-control" style="width: 200px" id="change_status_{ROW.id}" onchange="nv_chang_status('{ROW.id}');">
                            <!-- BEGIN: status -->
                            <option value="{STATUS.index}"{STATUS.selected}>{STATUS.value}</option>
                            <!-- END: status -->
                    </select></li>
                    <li><label class="pull-left" style="margin-top: 6px">{LANG.priority}</label> <select class="form-control" style="width: 200px" id="change_priority_{ROW.id}" onchange="nv_chang_priority('{ROW.id}');">
                            <!-- BEGIN: priority -->
                            <option value="{PRIORITY.index}"{PRIORITY.selected}>{PRIORITY.value}</option>
                            <!-- END: priority -->
                    </select></li>
                    <!-- BEGIN: field -->
                    <!-- BEGIN: loop -->
                    <li><label>{FIELD.title}</label>{FIELD.value}</li>
                    <!-- END: loop -->
                    <!-- END: field -->
                </ul>
            </div>
        </div>
        <!-- BEGIN: description -->
        <div class="panel panel-default" id="description">
            <div class="panel-heading">{LANG.description}</div>
            <div class="panel-body">{ROW.description}</div>
        </div>
        <!-- END: description -->
        <!-- BEGIN: files -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <em class="fa fa-download">&nbsp;</em>{LANG.download}
            </div>
            <div class="panel-body">
                <ul style="list-style: none; padding: 0; margin: 0">
                    <!-- BEGIN: loop -->
                    <li>
                        <!-- BEGIN: show_quick_viewpdf --> <a href="" class="open_file" data-key="{FILES.key}"> <i class="fa fa-file-pdf-o">&nbsp;</i>{FILES.title}
                    </a> <!-- END: show_quick_viewpdf --> <!-- BEGIN: show_quick_viewimg --> <a href="javascript:void(0)" class="open_file" data-key="{FILES.key}" data-src="{FILES.src}"> <i class="fa fa-file-image-o">&nbsp;</i>{FILES.title}
                    </a> <!-- END: show_quick_viewimg --> <!-- BEGIN: show_quick_viewpdf_url --> <a href="" class="open_file" data-key="{FILES.key}"> <i class="fa fa-file-pdf-o">&nbsp;</i>{FILES.title}
                    </a> <!-- END: show_quick_viewpdf_url --> <!-- BEGIN: show_quick_viewimg_url --> <a href="javascript:void(0)" class="open_file" data-key="{FILES.key}" data-src="{FILES.src}"> <i class="fa fa-file-image-o">&nbsp;</i>{FILES.title}
                    </a> <!-- END: show_quick_viewimg_url --> <!-- BEGIN: show_download --> <a href="{FILES.url}" target="_blank"> <i class="fa fa-file-image-o">&nbsp;</i>{FILES.title}
                    </a> <!-- END: show_download -->
                    </li>
                    <li id="file_content" style="display: none;">
                        <!-- BEGIN: content_quick_viewpdf_url -->
                        <div id="{FILES.key}" data-src="{FILES.src}">
                            <iframe frameborder="0" height="800" scrolling="yes" src="" width="900px"></iframe>
                        </div> <!-- END: content_quick_viewpdf_url --> <!-- BEGIN: content_quick_viewdoc_url -->
                        <div id="{FILES.key}" data-src="{FILES.urldoc}">
                            <iframe frameborder="0" height="800" scrolling="yes" src="" width="900px"></iframe>
                        </div> <!-- END: content_quick_viewdoc_url --> <!-- BEGIN: content_quick_viewpdf -->
                        <div id="{FILES.key}" data-src="{FILES.urlpdf}">
                            <iframe frameborder="0" height="800" scrolling="yes" src="" width="900px"></iframe>
                        </div> <!-- END: content_quick_viewpdf --> <!-- BEGIN: content_quick_viewdoc -->
                        <div id="{FILES.key}" data-src="{FILES.urldoc}">
                            <iframe frameborder="0" height="800" scrolling="yes" src="" width="900px"></iframe>
                        </div> <!-- END: content_quick_viewdoc --> <!-- BEGIN: content_quick_viewimg -->
                        <div id="{FILES.key}" data-src="{FILES.src}">
                            <img src="" style="max-width: 900px" />
                        </div> <!-- END: content_quick_viewimg -->
                    </li>
                    <!-- END: loop -->
                </ul>
            </div>
        </div>
        <!-- END: files -->
    </div>
    <div class="col-xs-24 col-sm-12 col-md-12">
        <!-- BEGIN: comment -->
        <div class="panel panel-default">
            <div class="panel-body">{COMMENT}</div>
        </div>
        <!-- END: comment -->
    </div>
</div>
<script>
    $('#btn-print').click(function() {
        nv_open_browse($(this).attr('href'), "NVImg", 850, 620, "resizable=no,scrollbars=no,toolbar=no,location=no,status=no");
        return !1;
    });
</script>
<!-- END: main -->
<!-- BEGIN: print -->
<div id="print">{CONTENT}</div>
<script type="text/javascript">
    $(document).ready(function() {
        window.print();
    });
</script>
<!-- BEGIN: print -->