<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color'];

    protected $attributes = [
        'color' => '#0172ad'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getAbbreviationAttribute()
{
    $words = explode(' ', $this->name);

    // Если одно слово и длина >= 2
    if (count($words) === 1 && mb_strlen($this->name, 'UTF-8') >= 2) {
        return mb_strtoupper(mb_substr($this->name, 0, 2, 'UTF-8'), 'UTF-8');
    }

    // Для нескольких слов или коротких слов
    $abbr = '';
    foreach ($words as $word) {
        $abbr .= mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8');
        if (mb_strlen($abbr, 'UTF-8') >= 2) break;
    }

    return $abbr ?: mb_strtoupper(mb_substr($this->name, 0, 1, 'UTF-8'), 'UTF-8');
}
}
