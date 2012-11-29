<?php
    $errorReporting = false;

    if (!$errorReporting){
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
    }
    
    $fix_text = function($text){
        return htmlspecialchars(html_entity_decode($text));
    };

    $_['response'] = OC_Submedia_Utils::fixBooleanKeys( $_['response'], 
                                                    array("isDir","isVideo"),
                                                    "true", "false",
                                                    $fix_text);

?>
<?php header ("Content-Type: text/xml"); ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<subsonic-response xmlns="http://subsonic.org/restapi" version="1.7.0" status="<?php echo $_['status']; ?>">
<?php if (isset($_['error'])): ?>
	<error code="<?php echo $_['error']['code']; ?>" message="<?php echo $_['error']['message']; ?>"/>
<?php endif; ?>
<?php if (file_exists(OC::$SERVERROOT.'/apps/submedia/templates/xml.'.$_['action'].'.php')): ?>
	<?php
		$tmpl = new OCP\Template('submedia', 'xml.'.$_['action']);
		$tmpl->assign('response', $_['response'], false);
		$tmpl->printpage();
	?>
<?php endif; ?>
</subsonic-response>