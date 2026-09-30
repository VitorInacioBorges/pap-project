<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('senha')->nullable();
            $table->enum('cargo',['medico','enfermeiro','secretario','admin']);
            $table->string('alias', 11);
            $table->string('email', 30);
            $table->boolean('first_login')->default(TRUE);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
    * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
