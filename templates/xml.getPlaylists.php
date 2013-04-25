<playlists>
    <? foreach ($_['response']['playlists'] as $playlist): ?>
    <playlist <?
    foreach ($playlist as $key => $value):
        if (is_bool($value)):
            if ($value == true):
                $value = 'true';
            else:
                $value = 'false';
            endif;
        else:
            $value = htmlentities($value);
        endif;
        echo $key . '="' . $value . '" ';
    endforeach;
    ?>/>
    <? endforeach; ?>
</playlists>
