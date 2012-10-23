<?php header ("Content-Type: text/xml"); ?>
<?xml version="1.0" encoding="UTF-8"?>
<subsonic-response xmlns="http://subsonic.org/restapi" version="1.7.0" status="<?php echo $_['status']; ?>">
<?php if (isset($_['error'])): ?>
	<error code="<?php echo $_['error']['code']; ?>" message="<?php echo $_['error']['message']; ?>"/>
<?php endif; ?>
<?php
	$tmpl = new OCP\Template('submedia', 'xml.'.$_['action']);
	$tmpl->assign('response', $_['response']);
	$tmpl->printpage();
?>
</subsonic-response>