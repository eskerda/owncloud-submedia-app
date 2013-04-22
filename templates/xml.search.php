<?php $data = $_['response']['searchResult']; ?>
<searchResult offset="<?php echo $data['offset']; ?>" totalHits="<?php echo $data['totalHits']; ?>">
<?php if (isset($data['match'])): ?>
<?php foreach($data['match'] as $match): ?>
<song <?php foreach($match as $key=>$value): ?>
<?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?> />
<?php endforeach; ?>
<?php endif; ?>
</searchResult>
