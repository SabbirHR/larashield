<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permission_permission_group', function(Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('permission_group_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();

            $table->foreign('permission_group_id')->references('id')->on('permission_groups')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('permission_permission_group'); }
};
