<? if (!empty($_['response']['directory'])):
    $directory = $_['response']['directory']; ?>
<directory id="<?=$directory['id'];?>" name="<?=$directory['name'];?>">
<? foreach ($directory['child'] as $child): ?>
<child <? foreach ($child as $key => $value): echo $key . '="' . $value . '" '; endforeach; ?>/>
<? endforeach; ?>
</directory>
<? endif; ?>
