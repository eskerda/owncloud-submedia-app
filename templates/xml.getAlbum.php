<album <? foreach ($_['response']['album'] as $key => $value):
if ($key != 'song'): echo $key . '="' . $value . '" '; endif;
endforeach; ?>>
<? foreach ($_['response']['album']['song'] as $song): ?>
<song <? foreach ($song as $key => $value): echo $key . '="' . $value . '" '; endforeach; ?>/>
<? endforeach; ?>
</album>
