<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Articles extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'content',
        'published_at'
    ];

    protected $appends = [
        'preview'
    ];

    public function getPreviewAttribute() {
        $preview = html_entity_decode(strip_tags($this->content));

        return strlen($preview) > 100 ? substr($preview,0,100)."..." : $preview;
    }
}
