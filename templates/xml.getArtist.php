<artist <? foreach ($_['response']['artist'] as $key => $value):
if ($key != 'album'): echo $key . '="' . $value . '" '; endif;
endforeach; ?>>
<? foreach ($_['response']['artist']['album'] as $album): ?>
<album <? foreach ($album as $key => $value): echo $key . '="' . $value . '" '; endforeach; ?>/>
<? endforeach; ?>
</artist>
