<h2>Blog Archive</h2>

<?php foreach ($posts as $post): ?>
    <article>
        <h3><a href="/blog/<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
        <p class="meta"><?= date('F j, Y', strtotime($post['published_at'])) ?></p>
        <p><?= htmlspecialchars($post['excerpt']) ?></p>
    </article>
<?php endforeach; ?>
