<h2>Recent Posts</h2>

<?php if (empty($posts)): ?>
    <p>No posts yet.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article>
            <h3><a href="/blog/<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
            <p class="meta"><?= date('F j, Y', strtotime($post['published_at'])) ?></p>
            <p><?= htmlspecialchars($post['excerpt']) ?></p>
            <a href="/blog/<?= htmlspecialchars($post['slug']) ?>">Read more →</a>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
