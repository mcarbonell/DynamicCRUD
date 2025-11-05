<article>
    <h2><?= htmlspecialchars($post['title']) ?></h2>
    <p class="meta"><?= date('F j, Y', strtotime($post['published_at'])) ?></p>
    <div class="content">
        <?= $post['content'] ?>
    </div>
</article>
