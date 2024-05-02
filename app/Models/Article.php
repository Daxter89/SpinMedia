<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'link',
        'description',
        'pubDate',
        'picture',
        'item1_url',
        'item1_title',
        'item1_source',
        'item2_url',
        'item2_title',
        'item2_source',
        'article_title',
        'article_description',
        'is_published'
    ];
}
