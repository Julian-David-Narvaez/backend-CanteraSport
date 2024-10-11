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
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->integer('edad');
            $table->string('tipo_identificacion', 20);
            $table->string('identificacion', 50)->unique();
            $table->string('numero_celular', 15);
            $table->string('correo', 100)->unique();
            $table->string('password');
            $table->enum('id_rol', ['admin', 'profesor', 'estudiante']);
            $table->integer('id_grupo')->nullable();
            $table->boolean('es_menor_de_edad')->default(false);
            $table->string('acudiente', 100)->nullable();
            $table->string('telefono_acudiente', 15)->nullable();
            $table->string('correo_acudiente', 100)->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
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