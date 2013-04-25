<? $data = $_['response']['searchResult']; ?>
<searchResult offset="<?=$data['offset'];?>" totalHits="<?=$data['totalHits'];?>">
<? if (isset($data['match'])): foreach ($data['match'] as $match): ?>
<song <? foreach ($match as $key => $value): echo $key . '="' . $value . '" '; endforeach; ?>/>
<? endforeach; endif; ?>
</searchResult>
