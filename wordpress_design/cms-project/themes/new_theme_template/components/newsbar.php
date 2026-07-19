<?php
$news_items = NT_Data_Provider::get('newsbar');
if (empty($news_items)) return;
?>
<div class="nt-newsbar">
    <?php foreach ($news_items as $item): 
        $item = (object)$item;
    ?>
    <div class="nt-newsbar-item">
        <?php if (!empty($item->label)): ?><span class="nt-badge"><?php echo esc_html($item->label); ?></span><?php endif; ?>
        <span class="nt-newsbar-text"><?php echo esc_html($item->text); ?></span>
        <?php if (!empty($item->link_url)): ?>
            <a href="<?php echo esc_url($item->link_url); ?>" class="nt-newsbar-link">Read more &rarr;</a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
