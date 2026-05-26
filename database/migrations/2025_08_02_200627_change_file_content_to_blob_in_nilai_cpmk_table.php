<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change file_content column to LONGBLOB for binary data storage
        DB::statement('ALTER TABLE nilai_cpmk MODIFY COLUMN file_content LONGBLOB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change back to LONGTEXT
        DB::statement('ALTER TABLE nilai_cpmk MODIFY COLUMN file_content LONGTEXT');
    }
};
