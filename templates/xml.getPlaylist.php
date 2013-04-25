<playlist <? foreach ($_['response']['playlist'] as $key => $value): echo $key . '="' . $value . '" '; endforeach; ?>>
    <?php foreach($_['response']['entry'] as $entry): ?>
    <entry <? foreach ($entry as $key => $value): echo $key . '="' . $value . '" '; endforeach; ?>/>
    <?php endforeach; ?>
</playlist>
