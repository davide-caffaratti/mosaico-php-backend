<?php 
include(dirname(__FILE__).'/backend-php/lib/config.inc.php');
include(dirname(__FILE__).'/backend-php/interface-lang/'.$config['USED_LANG'].'.inc.php');
include(dirname(__FILE__) . '/backend-php/lib/autoload.inc.php');
Autoloader::init();
?><!DOCTYPE html>
<html>
<head>
<title><?php echo $lang['EDITOR_TITLE'];?></title>

<meta name="viewport" content="width=1024, initial-scale=1">
<link rel="shortcut icon" href="<?php echo $config['BASE_URL'];?>/favicon.ico" type="image/x-icon" />
<link rel="icon" href="<?php echo $config['BASE_URL'];?>/favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
<style>
.template {
  margin: 10px;
  display: inline-block;
  vertical-align: top; 
}
.template {
  display: block;
  outline: 2px solid #333332;
  padding: 2px;
  width: 340px;
  height: 500px;
  overflow-y: auto;
}
.template:hover {
  outline: 5px solid #900000;
  transition: outline .2s;
}
a{
    color:#900000;
}
.title-tpl-choose,
.title{
    color:#900000;
}
</style>
<script type="text/javascript">
const server = '<?php echo $config['PHP_SERVER_URL'];?>tpl/';
 $(document).ready(function(){

        // New Template
        $('.new-tpl').on('click',function(){
          var template = $(this).data('template');
          var basename = $(this).data('basename');
          template_string = (template == 'undefined') ? '' : '&template='+template;
          
          let name = prompt('<?php echo $lang['ADD_TPL_NAME'];?>', '');
          if (name != null) { 
              name = encodeURI(name);
              document.location = '<?php echo $config['BASE_URL'];?>editor.php?name='+name+template_string;
          }
        });

        // Rename Template
        $('body').on('click','.rename',function(){
          var data_hash = $(this).data('hash');
          var rename = prompt('<?php echo $lang['RENAME_TPL'];?>', '');
          if (rename != null) {
            $.ajax({
                url:server, 
                type:'post',
                data:{action:'tpl-rename', name:rename, hash:data_hash},
                success:function(){
                  location.reload();
                },
                statusCode: {
                    404: function() {
                        alert('The template to rename is not valid!!');
                    }
                }
            }).fail(function(){
                console.log('Ajax error renamig template');
            });
          }
        });

        // Delete Template
        $('body').on('click','.delete',function(){
          var data_hash = $(this).data('hash');
          if (confirm('<?php echo $lang['DELETE_TPL'];?>')) {
            $.ajax({
                url:server, 
                type:'post',
                data:{action:'tpl-delete', hash:data_hash},
                success:function(){
                  location.reload();
                },
                statusCode: {
                    404: function() {
                        alert('The template to delete is not valid!!');
                    }
                }
            }).fail(function(){
                console.log('Ajax error deleting template');
            });
          }
        });

        // Duplicate Template
        $('body').on('click','.duplicate',function(){
          var data_hash = $(this).data('hash');
            $.ajax({
                url:server, 
                type:'post',
                data:{action:'tpl-duplicate', hash:data_hash},
                success:function(){
                  location.reload();
                },
                statusCode: {
                    404: function() {
                        alert('The template to duplicate is not valid!!');
                    }
                },
                error:function(){
                    console.log('Ajax error deleting template');
                }
            }).fail(function(){
                console.log('Ajax error deleting template');
            });
        });


    });


    </script>
    <style>

</style>


</head>
<body>
<div class="container text-center">
    <div class="row mb-2">
        <div class="col p-3 title">
            <h2><?php echo $lang['EDITOR_TITLE'];?></h2>
        </div>
    </div>

    <div id="saved" class="mb-2">
<?php
$db = new Db();
$sql = " SELECT `tpl_hash`, `user_id`, `tpl_basename`, `tpl_name`, `tpl_metadata`, `tpl_content`,".
       " `tpl_html`, DATE_FORMAT(`tpl_lastchange`,'".$config['DB_DATE_FORMAT']."') AS `tpl_lastchange`".
       " FROM `".$config['DB_TABLE']."` WHERE `user_id` = '".(int)$config['SESSION_USER_ID']."'";
$html = '';
if ($items = $db->get_results($sql, ARRAY_A)) {  
    $i = 1;
    
    $html .= 
    "        <table class=\"table table-striped table-hover rounded text-start\">\n".
    "            <thead>\n".
    "                <tr>\n".
    "                    <th scope=\"col\">#</th>\n".
    "                    <th scope=\"col\">".$lang['TABLE_FIELD_NAME']."</th>\n".
    "                    <th scope=\"col\">".$lang['TABLE_FIELD_TPL']."</th>\n".
    "                    <th scope=\"col\">".$lang['TABLE_FIELD_LCHANGE']."</th>\n".
    "                    <th scope=\"col\"></th>\n".
    "                </tr>\n".
    "           </thead>\n".
    "           <tbody>\n";
    foreach($items as $item) {
        $html .=
        "               <tr>\n".
        "                   <th scope=\"row\">".$i."</th>\n".
        "                   <td>\n".
        "                       <a href=\"editor.php?template=".$item['tpl_basename']."&name=". rawurlencode($item['tpl_name'])."#".$item['tpl_hash']."\" class=\"col-6 template-name\"><i class=\"fa fa-right-to-bracket me-1\"></i>".$item['tpl_name']."</a></td>\n".
        "                   <td>".$item['tpl_basename']."</td>\n".
        "                   <td>".$item['tpl_lastchange']."</td>\n".
        "                   <td class=\"text-end\">\n".
        "                      <button type=\"button\" class=\"btn btn-sm btn-outline-secondary dropdown-toggle\" data-bs-toggle=\"dropdown\">\n".
        "                          ".$lang['BTN_OPTIONS']."\n".
        "                      </button>\n".
        "                      <ul class=\"dropdown-menu\">\n".
        "                          <li><a data-hash=\"".$item['tpl_hash']."\" class=\"rename dropdown-item\" href=\"#\"><i class=\"fa fa-arrow-rotate-right me-1\"></i> ".$lang['BTN_RENAME']."</a></li>\n".
        "                          <li><a data-hash=\"".$item['tpl_hash']."\" class=\"duplicate dropdown-item\" href=\"#\"><i class=\"fa fa-copy me-1\"></i>".$lang['BTN_DUPLICATE']."</a></li>\n".
        "                          <li class=\"dropdown-divider\"></li>\n".
        "                          <li><a data-hash=\"".$item['tpl_hash']."\" class=\"delete dropdown-item text-danger\" href=\"#\"><i class=\"fa fa-trash me-1\"></i>".$lang['BTN_DELETE']."</a></li>".
        "                      </ul>\n".
        "                   </td>\n".
        "               </tr>\n";   
        $i++;
    }
    $html .= 
    "           </tbody>\n".
    "        </table>\n";
} 
else {
    $html .= 
    "        <div class=\"alert alert-warning d-flex align-items-center\" role=\"alert\">\n".
    "            <div>\n".
    "                <i class=\"fa fa-triangle-exclamation me-2\"></i>\n".
    "                ".$lang['NO_TPL_DB']."\n".
    "            </div>\n".
    "        </div>\n";
}   
echo $html;
?>
        </div>
        <div class="row mb-2">
            <div class="col p-3 title-tpl-choose">
                <h3><?php echo $lang['TITLE_CHOSE_TPL'];?></h3>
            </div>
        </div>
        <div id="templates-list" class="row g-2 g-sm-1 mb-2">
    
<?php
$path_tpl = $config['BASE_PATH'] . 'templates/'; 
$dirs = glob($path_tpl . '/*', GLOB_ONLYDIR);
    if (count($dirs) > 0) {
        foreach ($dirs as $dir) {
            $tpl_basename = basename($dir);
            if (file_exists($config['BASE_PATH'] . 'templates/' .$tpl_basename. '/template-' .$tpl_basename. '.html')) {
                echo
                "        <div class=\"col-sm-6 col-lg-4\">\n".
                "            <div class=\"template\">\n".
                "                <a href=\"#\" class=\"new-tpl\" data-template=\"" .$tpl_basename. "\" title=\"".$lang['USE_TPL_DESC']."\">\n".
                "                   <img src=\"" .$config['BASE_URL']. "templates/" .$tpl_basename. "/edres/_full.png\" class=\"img-fluid\">\n".
                "                </a>\n".
                "            </div>\n".
                "        </div>\n";
            }
        }
    } 
    // No templates in templates dir allert
    else {
        echo 
        "        <div class=\"alert alert-danger d-flex align-items-center\" role=\"alert\">\n".
        "            <div>\n".
        "                <i class=\"fa fa-triangle-exclamation me-2\"></i>\n".
        "                ".$lang['NO_TPL_FOLDER']."\n".
        "            </div>\n".
        "        </div>\n";
    }
?>
    </div>

</div>

</body>
</html>
