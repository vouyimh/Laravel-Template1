<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentFile extends Model
{
     protected $table = 'comment_files';

    protected $fillable = [
        'comment_id',
        'file_name',
        'file_path'
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
