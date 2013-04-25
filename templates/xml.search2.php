<? $data = $_['response']['searchResult2']; ?>
<searchResult2>
<? if (isset($data['artist'])): foreach ($data['artist'] as $artist): ?>
<artist name="<?=$artist['name'];?>" id="<?=$artist['id'];?>" />
<? endforeach; endif; ?>
<? if (isset($data['album'])): foreach ($data['album'] as $album): ?>
<album <? foreach ($album as $key => $value): echo $key . '="' . $value . '" '; endforeach; ?>/>
<? endforeach; endif; ?>
<? if (isset($data['song'])): foreach ($data['song'] as $song): ?>
<song <? foreach ($song as $key => $value): echo $key . '="' . $value . '" '; endforeach; ?>/>
<? endforeach; endif; ?>
</searchResult2>
