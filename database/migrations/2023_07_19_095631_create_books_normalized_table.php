<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books_normalized', function (Blueprint $table) {
            $table->id();
            $table->string("isbn");
            $table->string("title");
            $table->text("description")->nullable();
            $table->string("cover_image");
            $table->string("number_of_pages")->nullable();
            $table->integer("review_count")->nullable();
            $table->decimal("ratings", 8, 4);
            $table->string("series")->nullable();
            $table->text("series_description")->nullable();
            $table->string("publisher")->nullable();
            $table->date("date_published")->default('1900-01-01');
            $table->date("created_at_book");
            $table->string("category")->nullable();
            $table->string("authors")->nullable();
            $table->string("illustrators")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books_normalized');
    }
};
