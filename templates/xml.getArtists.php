<? $indexes = $_['response']['artists']; ?>
<artists lastModified="<?=$indexes['lastModified'];?>">
    <? foreach ($indexes['index'] as $key => $index): ?>
    <index name="<?=$key;?>">
        <? foreach ($index as $artist): ?>
        <artist <? foreach ($artist as $key => $value): echo $key . '="' . $value . '" '; endforeach; ?>/>
        <? endforeach; ?>
    </index>
    <? endforeach; ?>
</artists>
