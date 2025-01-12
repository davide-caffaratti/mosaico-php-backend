<?php 
include(dirname(__FILE__).'/backend-php/lib/config.inc.php');
include(dirname(__FILE__).'/backend-php/interface-lang/'.$config['USED_LANG'].'.inc.php');
?><!DOCTYPE html>
<html>
<head>
<title><?php echo $lang['EDITOR_TITLE'];?></title>

<meta name="viewport" content="width=1024, initial-scale=1">
<link rel="shortcut icon" href="<?php echo $config['BASE_URL'];?>/favicon.ico" type="image/x-icon" />
<link rel="icon" href="<?php echo $config['BASE_URL'];?>/favicon.ico" type="image/x-icon" />
<script src="<?php echo $config['BASE_URL'];?>dist/rs/mosaico-libs-and-tinymce.min.js?v=<?php echo $config['MOSAICO_LIB_VERSION'];?>"></script>
<script src="<?php echo $config['BASE_URL'];?>dist/rs/mosaico.min.js?v=<?php echo $config['MOSAICO_LIB_VERSION'];?>"></script>

<script type="text/javascript">

$(function() {
    if (!Mosaico.isCompatible()) {
        alert('<?php echo $lang['BROWSER_INCOMPATIBLE'];?>');
        return;
    }
    let hash = window.location.hash.split("#").pop();  
    if (hash == '') {
        hash = '<?php echo uniqid('', true); ?>';
    }
    const baseServer = '<?php echo $config['PHP_SERVER_URL'];?>';
    const baseUrl = '<?php echo $config['BASE_URL'];?>';
    const templateName = '<?php echo (filter_has_var(INPUT_GET, 'name')) ? rawurldecode($_GET['name']) : $lang['NEW_TPL_DEF_NAME'];?>';
    const templateBaseDir = '<?php echo (filter_has_var(INPUT_GET, 'template') && !empty($_GET['template']) && $_GET['template'] != 'undefined') ? ($_GET['template']) : $config['DEF_TEMPLATE_BASEDIR'];?>';
    const template = baseUrl + 'templates/' + templateBaseDir + '/template-' + templateBaseDir + '.html';

    var plugins = [
        function(viewModel) {
            // Save viewModel to window
            window.viewModel = viewModel;
            console.log('SAVING VIEWMODEL AND PROCESS PLUGIN');
            
            var saveCmd = {
                name: 'Save', // l10n happens in the template
                enabled: ko.observable(true)
            };
            saveCmd.execute = function() {
                saveCmd.enabled(false);
                if (typeof viewModel.metadata.created == 'undefined') {
                    viewModel.metadata.created = Date.now();
                }
                if (typeof viewModel.metadata.key == 'undefined') {
                    alert('no key defined!!');
                    return;
                }
                viewModel.metadata.changed = Date.now();                
                viewModel.metadata.name = templateName;
                
                // Save in the database
                $.ajax({
                    url: baseServer + 'tpl/',
                    type: 'post',
                    dataType: 'json',
                    data: { 
                        action: 'save',
                        name: templateName,
                        hash: viewModel.metadata.key,
                        metadata: viewModel.exportMetadata(),
                        content: viewModel.exportJSON(),
                        html: viewModel.exportHTML()
                    },
                    success: function(response) {
                        switch(response.type) {
                            case 'error':
                                viewModel.notifier.error(response.message);
                                break;
                            default:
                                viewModel.notifier.success(response.message);
                                break;
                        }
                    }
                }).fail(function(){
                    viewModel.notifier.error(viewModel.t('Unexpected error talking to server: contact us!'));
                }).always(function() {
                    saveCmd.enabled(true);
                });                
            };
             
            var testCmd = {
                name: 'Test', // l10n happens in the template
                enabled: ko.observable(true)
            };
                
            testCmd.execute = function() {
                testCmd.enabled(false);
                var email = localStorage.getItem("testemail");
                if (email === null || email == 'null') email = viewModel.t('Insert here the recipient email address');
                if (typeof prompt !== 'function') {
                    alert(viewModel.t('This feature is not supported by your browser'));
                    testCmd.enabled(true);
                } 
                else {
                    email = prompt(viewModel.t("Test email address"), email);
                    if (typeof email !== 'undefined' && email !== null && email.match(/@/)) {
                        localStorage.setItem("testemail", email);
                        var postUrl = baseServer + 'dl/';
                        var post = $.post(postUrl, {
                            action: 'email',
                            rcpt: email,
                            subject: '[test email] ' + templateName,
                            html: viewModel.exportHTML()
                        }, null, 'html');
                        post.fail(function() {
                            console.log("fail", arguments);
                            viewModel.notifier.error(viewModel.t('Unexpected error talking to server: contact us!'));
                        });
                        post.success(function() {
                            console.log("success", arguments);
                            viewModel.notifier.success(viewModel.t("Test email sent..."));
                        });
                        post.always(function() {
                            testCmd.enabled(true);
                        });
                    } 
                    else {
                        alert(viewModel.t('Invalid email address'));
                        testCmd.enabled(true);
                    }
                }
            };
            <?php 
            # Check if we use de download email function #
            if ($config['USE_DOWNLOAD_HTML']) {?>
            
            var downloadCmd = {
                name: 'Download', // l10n happens in the template
                enabled: ko.observable(true)
            };
            downloadCmd.execute = function() {
                downloadCmd.enabled(false);
                viewModel.notifier.info(viewModel.t("Downloading..."));
                viewModel.exportHTMLtoTextarea('#downloadHtmlTextarea');
                var postUrl = baseServer + '/dl/';
                document.getElementById('downloadForm').setAttribute("action", postUrl);
                document.getElementById('downloadForm').submit();
                downloadCmd.enabled(true);
            };
            
            viewModel.download = downloadCmd;            
            <?php }?>
            viewModel.save = saveCmd;
            viewModel.test = testCmd;
            
            viewModel.logoPath = baseUrl + 'dist/rs/img/mosaico32.png';
            return viewModel;
        }
    ];
    
    
    $.ajax({
        url:  baseServer + 'tpl/',
        type: 'post',
        dataType: 'json',
        data: { action: 'fetch', 'hash': hash },
        success: function(response){
            console.log(response);
            var metadata = response.metadata;
            var content = response.content;
            
            var ok = Mosaico.start({
            imgProcessorBackend: baseServer + 'img/',
            emailProcessorBackend: baseServer + 'dl/',
            titleToken: '<?php echo $lang['TITTLE_TOCKEN'];?>',
            fileuploadConfig: {
              url: baseServer + 'uploads/',
            },
<?php 
# Here we insert the Mosaico interface translation if we need
            if (!empty($config['LANG_FILE']) && file_exists($config['BASE_PATH'] . $config['LANG_DIR'] . $config['LANG_FILE'])) {
                $language = file_get_contents($config['BASE_PATH'] . $config['LANG_DIR'] . $config['LANG_FILE']);
                echo 'strings: ' . $language."\n";
            }
?>
            }, template, $.parseJSON(metadata) /* metadata */, $.parseJSON(content) /* model */, plugins);
            if (!ok) {
                console.log("Missing initialization hash, redirecting to main entrypoint"+ok);
                //document.location = '.';
            }
        },
        statusCode: {
            404: function() {
                var metadata = {};
                var content = {};
                metadata.key = hash;
                metadata.template = template;
            
            var ok = Mosaico.start({
            imgProcessorBackend: baseServer + 'img/',
            emailProcessorBackend: baseServer + 'dl/',
            titleToken: '<?php echo $lang['TITTLE_TOCKEN'];?>',
            fileuploadConfig: {
              url: baseServer + 'uploads/',
            },
<?php 
# Here we insert the Mosaico javascript translation if we need
            if (!empty($config['LANG_FILE']) && file_exists($config['BASE_PATH'] . $config['LANG_DIR'] . $config['LANG_FILE'])) {
                $language = file_get_contents($config['BASE_PATH'] . $config['LANG_DIR'] . $config['LANG_FILE']);
                echo 'strings: ' . $language."\n";
            }
?>
            }, template, (metadata) /* metadata */, (content) /* model */, plugins);
                if (!ok) {
                    console.log("Missing initialization hash, redirecting to main entrypoint"+ok);
                    //document.location = '.';
                }
            }
        }
    });

    /*
    * Add Send email function
     */
    function addCustomButton() {
        var vm = window.viewModel;
        if ($('#page .rightButtons').is(':visible')) {
            $("#page .rightButtons").append('<button id="process-send" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" title="<?php echo $lang['BTN_CUSTOM_TITLE'];?>"><span class="ui-button-icon-primary ui-icon fa fa-fw fa-envelope"></span><span class="ui-button-text"><?php echo $lang['BTN_CUSTOM'];?></span></button>');
        } 
        else {
            console.log('timeout 50');
            setTimeout(addCustomButton, 50);
        }
        
        $('#process-send').click(function (e) {
            // Save in the database
            $.ajax({
                url: baseServer + 'send/',
                type: 'post',
                data: { 
                    name: templateName,
                    hash: vm.metadata.key,
                    metadata: vm.exportMetadata(),
                    content: vm.exportJSON(),
                    html: vm.exportHTML()
                },
                success: function(response) {
                    $('.mo-standalone').html(response);
                    
                    $('#email-back').click(function (e) {
                        location.reload();
                    });
                    $('#email-send').click(function (e) {
                        document.location = '<?php echo $config['PHP_SENDEMAIL_URL'];?>?hash=' + vm.metadata.key;                 
                    });
                },
                statusCode: {
                    404: function() {
                        vm.notifier.error(data.message);
                    }                    
                }
            }).fail(function(){
                vm.notifier.error(vm.t('Unexpected error talking to server: contact us!'));
            });         
        });
    }
    /*
     * Add Button send newsletter
     */
    addCustomButton();
});
  
  </script>

  <link rel="stylesheet" href="<?php echo $config['BASE_URL'];?>dist/rs/mosaico-libs-and-tinymce.min.css?v=<?php echo $config['MOSAICO_LIB_VERSION'];?>" />
  <link rel="stylesheet" href="<?php echo $config['BASE_URL'];?>dist/rs/mosaico-material.min.css?v=<?php echo $config['MOSAICO_LIB_VERSION'];?>" />
  </head>
  <body class="mo-standalone">

  </body>
</html>
