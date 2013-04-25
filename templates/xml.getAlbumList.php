<albumList>
<? foreach ($_['response'] as $album): ?>
<album <? foreach ($album as $key => $value): echo $key . '="' . $value . '" '; endforeach; ?>/>
<?php endforeach; ?>
</albumList>
