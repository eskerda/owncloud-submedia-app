<?

$errorReporting = false;

if (!$errorReporting) {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

$_['response'] = OCA\Submedia\Utils::fixBooleanKeys(
    $_['response'],
    array('isDir', 'isVideo'),
    'true',
    'false',
    function($text) {
        return htmlspecialchars(html_entity_decode($text, ENT_QUOTES));
    }
);

header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';

?>
<subsonic-response xmlns="http://subsonic.org/restapi" version="<?=OCA\Submedia\Subsonic::$api_version;?>" status="<?=$_['status'];?>">
<? if (isset($_['error'])): ?>
<error code="<?=$_['error']['code'];?>" message="<?=$_['error']['message'];?>" />
<? elseif (is_file(OC::$SERVERROOT . '/apps/submedia/templates/xml.' . $_['action'] . '.php')): ?>
<?
$tmpl = new OCP\Template('submedia', 'xml.' . $_['action']);
$tmpl->assign('response', $_['response'], false);
$tmpl->printpage();
?>
<? endif; ?>
</subsonic-response>
