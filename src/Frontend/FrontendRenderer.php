<?php

namespace DynamicCRUD\Frontend;

use DynamicCRUD\Template\TemplateEngine;

/**
 * FrontendRenderer
 * 
 * Renders public-facing pages from database content
 */
class FrontendRenderer
{
    private \PDO $pdo;
    private ?TemplateEngine $engine;
    private string $contentType;
    
    public function __construct(\PDO $pdo, string $contentType = 'blog', ?TemplateEngine $engine = null)
    {
        $this->pdo = $pdo;
        $this->contentType = $contentType;
        $this->engine = $engine;
    }
    
    /**
     * Render single post/page
     */
    public function renderSingle(string $slug): string
    {
        $post = $this->getPostBySlug($slug);
        
        if (!$post) {
            return $this->render404();
        }
        
        $data = [
            'post' => $post,
            'title' => $post['title'],
            'content' => $post['content']
        ];
        
        return $this->renderTemplate('single', $data);
    }
    
    /**
     * Render archive (list of posts)
     */
    public function renderArchive(int $page = 1, int $perPage = 10): string
    {
        $offset = ($page - 1) * $perPage;
        $posts = $this->getPosts($perPage, $offset);
        $total = $this->getTotalPosts();
        $totalPages = ceil($total / $perPage);
        
        $data = [
            'posts' => $posts,
            'page' => $page,
            'totalPages' => $totalPages,
            'title' => 'Blog'
        ];
        
        return $this->renderTemplate('archive', $data);
    }
    
    /**
     * Render category archive
     */
    public function renderCategory(string $slug, int $page = 1, int $perPage = 10): string
    {
        $category = $this->getCategoryBySlug($slug);
        
        if (!$category) {
            return $this->render404();
        }
        
        $offset = ($page - 1) * $perPage;
        $posts = $this->getPostsByCategory($category['id'], $perPage, $offset);
        $total = $this->getTotalPostsByCategory($category['id']);
        $totalPages = ceil($total / $perPage);
        
        $data = [
            'posts' => $posts,
            'category' => $category,
            'page' => $page,
            'totalPages' => $totalPages,
            'title' => $category['name']
        ];
        
        return $this->renderTemplate('category', $data);
    }
    
    /**
     * Render tag archive
     */
    public function renderTag(string $slug, int $page = 1, int $perPage = 10): string
    {
        $tag = $this->getTagBySlug($slug);
        
        if (!$tag) {
            return $this->render404();
        }
        
        $offset = ($page - 1) * $perPage;
        $posts = $this->getPostsByTag($tag['id'], $perPage, $offset);
        $total = $this->getTotalPostsByTag($tag['id']);
        $totalPages = ceil($total / $perPage);
        
        $data = [
            'posts' => $posts,
            'tag' => $tag,
            'page' => $page,
            'totalPages' => $totalPages,
            'title' => $tag['name']
        ];
        
        return $this->renderTemplate('tag', $data);
    }
    
    /**
     * Render home page
     */
    public function renderHome(): string
    {
        $posts = $this->getPosts(5);
        
        $data = [
            'posts' => $posts,
            'title' => 'Home'
        ];
        
        return $this->renderTemplate('home', $data);
    }
    
    /**
     * Render search results
     */
    public function renderSearch(string $query, int $page = 1, int $perPage = 10): string
    {
        $offset = ($page - 1) * $perPage;
        $posts = $this->searchPosts($query, $perPage, $offset);
        $total = $this->getTotalSearchResults($query);
        $totalPages = ceil($total / $perPage);
        
        $data = [
            'posts' => $posts,
            'query' => $query,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'title' => "Search: {$query}"
        ];
        
        return $this->renderTemplate('search', $data);
    }
    
    /**
     * Render 404 page
     */
    public function render404(): string
    {
        http_response_code(404);
        
        $data = [
            'title' => '404 Not Found',
            'message' => 'The page you are looking for does not exist.'
        ];
        
        return $this->renderTemplate('404', $data);
    }
    
    /**
     * Render template
     */
    private function renderTemplate(string $template, array $data): string
    {
        if ($this->engine) {
            return $this->engine->render($template, $data);
        }
        
        // Fallback: simple HTML
        return $this->renderSimpleHTML($template, $data);
    }
    
    /**
     * Simple HTML fallback (no template engine)
     */
    private function renderSimpleHTML(string $template, array $data): string
    {
        extract($data);
        
        $html = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '<title>' . htmlspecialchars($title ?? 'Blog') . '</title>';
        $html .= '<style>body{font-family:sans-serif;max-width:800px;margin:0 auto;padding:20px;}</style>';
        $html .= '</head><body>';
        
        if ($template === 'single' && isset($post)) {
            $html .= '<h1>' . htmlspecialchars($post['title']) . '</h1>';
            $html .= '<div>' . $post['content'] . '</div>';
        } elseif (isset($posts)) {
            $html .= '<h1>' . htmlspecialchars($title) . '</h1>';
            foreach ($posts as $post) {
                $html .= '<article>';
                $html .= '<h2><a href="/blog/' . htmlspecialchars($post['slug']) . '">' . htmlspecialchars($post['title']) . '</a></h2>';
                $html .= '<p>' . htmlspecialchars($post['excerpt'] ?? '') . '</p>';
                $html .= '</article>';
            }
        }
        
        $html .= '</body></html>';
        return $html;
    }
    
    // Database queries
    
    private function getPostBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE slug = :slug AND status = 'published' AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getPosts(int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE status = 'published' AND deleted_at IS NULL ORDER BY published_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    private function getTotalPosts(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published' AND deleted_at IS NULL");
        return (int) $stmt->fetchColumn();
    }
    
    private function getCategoryBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getPostsByCategory(int $categoryId, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE category_id = :category_id AND status = 'published' AND deleted_at IS NULL ORDER BY published_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    private function getTotalPostsByCategory(int $categoryId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = :category_id AND status = 'published' AND deleted_at IS NULL");
        $stmt->execute(['category_id' => $categoryId]);
        return (int) $stmt->fetchColumn();
    }
    
    private function getTagBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tags WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getPostsByTag(int $tagId, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.* FROM posts p
            INNER JOIN post_tags pt ON p.id = pt.post_id
            WHERE pt.tag_id = :tag_id AND p.status = 'published' AND p.deleted_at IS NULL
            ORDER BY p.published_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':tag_id', $tagId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    private function getTotalPostsByTag(int $tagId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM posts p
            INNER JOIN post_tags pt ON p.id = pt.post_id
            WHERE pt.tag_id = :tag_id AND p.status = 'published' AND p.deleted_at IS NULL
        ");
        $stmt->execute(['tag_id' => $tagId]);
        return (int) $stmt->fetchColumn();
    }
    
    private function searchPosts(string $query, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM posts 
            WHERE (title LIKE :query OR content LIKE :query) 
            AND status = 'published' AND deleted_at IS NULL
            ORDER BY published_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $searchQuery = '%' . $query . '%';
        $stmt->bindValue(':query', $searchQuery);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    private function getTotalSearchResults(string $query): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM posts 
            WHERE (title LIKE :query OR content LIKE :query) 
            AND status = 'published' AND deleted_at IS NULL
        ");
        $searchQuery = '%' . $query . '%';
        $stmt->execute(['query' => $searchQuery]);
        return (int) $stmt->fetchColumn();
    }
}
