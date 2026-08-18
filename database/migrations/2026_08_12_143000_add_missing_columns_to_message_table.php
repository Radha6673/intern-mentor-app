<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('message')) {
            Schema::table('message', function (Blueprint $table) {
                if (!Schema::hasColumn('message', 'type')) {
                    $table->enum('type', ['text', 'image', 'video', 'file'])->default('text')->after('message');
                }
                if (!Schema::hasColumn('message', 'file_path')) {
                    $table->string('file_path')->nullable()->after('type');
                }
                if (!Schema::hasColumn('message', 'file_name')) {
                    $table->string('file_name')->nullable()->after('file_path');
                }
                if (!Schema::hasColumn('message', 'is_read')) {
                    $table->boolean('is_read')->default(false)->after('file_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('message')) {
            Schema::table('message', function (Blueprint $table) {
                if (Schema::hasColumn('message', 'is_read')) {
                    $table->dropColumn('is_read');
                }
                if (Schema::hasColumn('message', 'file_name')) {
                    $table->dropColumn('file_name');
                }
                if (Schema::hasColumn('message', 'file_path')) {
                    $table->dropColumn('file_path');
                }
                if (Schema::hasColumn('message', 'type')) {
                    $table->dropColumn('type');
                }
            });
        }
    }
};
