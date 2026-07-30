<?php

namespace App\Concerns\Commerce;

use Illuminate\Support\Str;

trait HasSeoSlug
{
    protected static function bootHasSeoSlug(): void
    {
        static::saving(function ($model): void {
            $source = $model->name ?? $model->title ?? null;

            if (blank($model->slug) && filled($source)) {
                $model->slug = Str::slug($source);
            }
        });
    }
}
