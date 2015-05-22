<div class="page-buttonbar clearfix">
    <h1 class="page-header pull-left">{-"Filesharing"|translate}</h1>
    <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#newfiles_modal" id="newfiles"><i
                class="fa fa-plus"></i>&nbsp;{-"New file"|translate}</button>
    <div class="btn-group pull-right">
        <button type="button" class="btn btn-default dropdown-toggle tooltip-trigger" id="filter-dropdown"
                data-toggle="dropdown" data-title="{-"Filter the files you want to see"|translate}"><i
                    class="fa fa-filter"></i></button>
        <ul class="dropdown-menu dropdown-checkbox-menu" role="menu" style="left:0;right:auto">
            <li class="dropdown-header">{-"Filter the files you want to see"|translate}</li>
            <li>
                <label>
                    <input type="checkbox" class="filess-filter" value="own">&nbsp;{-"My files"|translate}
                </label>
            </li>
            <li>
                <label>
                    <input type="checkbox" class="filess-filter" value="foreign">&nbsp;{-"Friends files"|translate}
                </label>
            </li>
            <li>
                <button class="btn btn-primary btn-xs btn-block"
                        onclick="applyFilter();">{-"Apply filter"|translate}</button>
            </li>
        </ul>
    </div>
</div>
<div id="fileslist">
    <div class="filesharing-loader block-loader loader"></div>
    <div class="filesharing-list clearfix"></div>
    <div class="alert alert-block alert-danger"><p>{-"There are no files to show!"|translate}</p></div>
</div>
<div class="modal fade" id="newfiles_modal" tabindex="-1" role="dialog" aria-labelledby="newfiles_modal"
     aria-hidden="true" data-moveto="body">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{-"Create new fle"|translate}</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal ajaxform" action="{-"index.php?m=ilesharing&action=create"|URL}" role="form"
                      id="newfiles_form" method="POST">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{-"Title"|translate}</label>

                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="title" placeholder="{-"Title"|translate}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{-"Description"|translate}</label>

                        <div class="col-sm-10">
                            <textarea class="form-control" rows="3" name="description"
                                      placeholder="{-"Enter a short description for your files. Optional you can leave that blank!"|translate}"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{-"Privacy"|translate}</label>

                        <div class="col-sm-10">
                            <div class="btn-group" data-toggle="buttons" style="width:100%">
                                <label class="btn btn-default tooltip-trigger"
                                       data-title="{-"You can choose friends who are allowed to see this files"|translate}"
                                       data-container="#newfiles_modal" style="width:33.3333%">
                                    <input type="radio" name="privacy" value="0" id="newfiles_shared"><i
                                            class="fa fa-check-square-o"></i>&nbsp;{-"Shared"|translate}
                                </label>
                                <label class="btn btn-default tooltip-trigger"
                                       data-title="{-"Only your friends can see the images"|translate}"
                                       data-container="#newfiles_modal" style="width:33.3333%">
                                    <input type="radio" name="privacy" value="1"><i
                                            class="fa fa-group"></i>&nbsp;{-"Friends"|translate}
                                </label>
                                <label class="btn btn-default tooltip-trigger active"
                                       data-title="{-"Every user can see the images"|translate}"
                                       data-container="#newfiles_modal" style="width:33.3333%">
                                    <input type="radio" name="privacy" value="2" checked><i class="fa fa-globe"></i>&nbsp;{-"Public"|translate}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group hidden" id="newfiles_shared_options">
                        <div class="col-sm-offset-2 col-sm-10">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="allow_upload"
                                           value="1"> {-"Friends can also upload images"|translate}
                                </label>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" class="ajaxform-callback" value="filescreated">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{-"Cancel"|translate}</button>
                <button type="button" id="createfiles" onclick="$('#newfiles_form').submit();" class="btn btn-primary"
                        data-loading-text="{-"Please wait.."|translate}">{-"Create"|translate}</button>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="filess-template">
    <div class="fileslist-item pull-left user-files-{%=o.owner_id%} {% if (o.owner_id == {-$user.userid}) { %}files-own{% } else { %}files-foreign{% } %}">
        <a href="{%=convertUrl({'module':'filesharing','action': o.id,'x':o.title.replace(' ','_')})%}"
           class="fileslist-item-image"
           style="background-image:url('{%=checkImage(o.filename,'filesharing','thumb_')%}');"></a>
        <a href="{%=convertUrl({'module':'filesharing','action': o.id,'x':o.title.replace(' ','_')})%}"
           class="fileslist-item-name">{% if (o.type == 'profile') { %}{-"Profile Images"|translate}{% } else if (o.type == 'newsfeed'){ %}{-"Posted Images"|translate}{% } else { %}{%=o.title%}{% } %}</a>

        <div class="fileslist-item-ownerbox clearfix">
            <a href="{-"index.php?m=profile&action="|URL}{%=o.username%}"><img
                        src="{%=checkImage(o.pimg,'user','cr_')%}"
                        class="fileslist-item-owner tooltip-trigger pull-left" data-title="{%=o.name%}"
                        data-placement="right"></a>
            <i class="fileslist-item-time">{%#convertDate(o.time)%}</i>
        </div>
    </div>
</script>