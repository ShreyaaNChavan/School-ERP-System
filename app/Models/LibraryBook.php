<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryBook extends Model
{
    protected $table = 'library_books';
    protected $fillable = ['school_id','isbn','title','author','publisher','category','edition','publication_year'];
    public function copies(): HasMany { return $this->hasMany(LibraryCopy::class, 'library_book_id'); }
}
