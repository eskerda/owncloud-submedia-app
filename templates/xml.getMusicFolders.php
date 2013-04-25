<musicFolders>
    <? foreach ($_['response']['musicFolders']['musicFolder'] as $mFolder): ?>
    <musicFolder id="<?=$mFolder['id'];?>" name="<?=$mFolder['name'];?>" />
    <? endforeach; ?>
</musicFolders>
