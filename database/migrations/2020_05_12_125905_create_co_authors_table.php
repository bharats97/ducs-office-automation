<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('co_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->string('name')->nullable();
            $table->string('noc_path')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('co_authors');
    }
}
