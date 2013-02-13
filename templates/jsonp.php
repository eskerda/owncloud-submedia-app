<?php
    $tmpl = new OCP\Template('submedia', 'json');
    $tmpl->assign('error', $_['error']);
    $tmpl->assign('callback', $_['callback']);
    $tmpl->assign('response', $_['response']);
    $tmpl->printpage();
?>
