<playlists>
    <?php foreach ($_['response']['playlists'] as $playlist): ?>
    <playlist <?php foreach ($playlist as $key=>$value): ?>
    <?php if (is_bool($value)){
        if ($value == true)
            $value = "true";
        else
            $value = "false";
    }else{
            $value = htmlentities($value);
        } ?>
    <?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?> />
    <?php endforeach; ?>
</playlists>