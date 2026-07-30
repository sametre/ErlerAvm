<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use App\Concerns\Commerce\HasSeoSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $public_id
 * @property string $title
 * @property string $slug
 * @property string $status
 * @property string|null $excerpt
 * @property string|null $content
 * @property string|null $cover_image_path
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BlogPost extends Model
{
    use HasPublicId;
    use HasSeoSlug;

    protected $fillable = [
        'public_id',
        'title',
        'slug',
        'status',
        'excerpt',
        'cover_image_path',
        'content',
        'seo_title',
        'seo_description',
        'published_at',
        'approved_by_id',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function getUrlAttribute(): string
    {
        return route('blog.show', ['post' => $this->slug]);
    }
}
