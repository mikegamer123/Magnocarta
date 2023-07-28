<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookNormalized extends Model
{
    use HasFactory;
    protected $table = 'books_normalized';
}
