<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function show(string $post): Response
    {
        $post = BlogPost::query()
            ->where('slug', $post)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->firstOrFail();

        return Inertia::render('Storefront/BlogShow', [
            'post' => [
                ...$post->only(['title', 'slug', 'excerpt', 'published_at', 'cover_image_path', 'seo_title', 'seo_description']),
                'content_html' => $this->sanitizeAdminHtml((string) $post->content),
            ],
        ]);
    }

    private function sanitizeAdminHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><a><span>';
        $clean = strip_tags($html, $allowedTags);
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', (string) $clean);
        $clean = preg_replace('/javascript\s*:/i', '', (string) $clean);

        return trim((string) $clean);
    }
}
