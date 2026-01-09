<?php
// database/migrations/xxxx_xx_xx_add_status_to_posts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'status')) {
                $table->enum('status', ['draft', 'pending', 'published', 'rejected'])
                    ->default('published')
                    ->after('content');
            }
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};