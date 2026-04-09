<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_assets', function (Blueprint $table): void {
            $table->string('asset_type', 50)->default('document')->after('learning_module_id');
            $table->string('extracted_disk')->nullable()->after('storage_path');
            $table->text('extracted_path')->nullable()->after('extracted_disk');
            $table->text('launch_path')->nullable()->after('extracted_path');
            $table->json('manifest')->nullable()->after('launch_path');
            $table->json('processing_metadata')->nullable()->after('manifest');

            $table->index('asset_type');
        });
    }

    public function down(): void
    {
        Schema::table('learning_assets', function (Blueprint $table): void {
            $table->dropIndex(['asset_type']);
            $table->dropColumn([
                'asset_type',
                'extracted_disk',
                'extracted_path',
                'launch_path',
                'manifest',
                'processing_metadata',
            ]);
        });
    }
};
