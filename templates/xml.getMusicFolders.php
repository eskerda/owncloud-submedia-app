<musicFolders>
    <?php foreach($_['response']['musicFolders']['musicFolder'] as $mFolder): ?>
    <musicFolder id="<?php echo $mFolder['id']; ?>" name="<?php echo $mFolder['name']; ?>"/>
    <?php endforeach; ?>
</musicFolders>
