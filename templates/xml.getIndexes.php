<? $indexes = $_['response']['indexes']; ?>
<indexes lastModified="<?=$indexes['lastModified'];?>">
    <? foreach ($indexes['index'] as $key => $index): ?>
    <index name="<?=$key;?>">
        <? foreach ($index as $artist): ?>
        <artist name="<?=$artist['name'];?>" id="<?=$artist['id'];?>"/>
        <? endforeach; ?>
    </index>
    <? endforeach; ?>
</indexes>
