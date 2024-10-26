<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('link_ig')->nullable();
            $table->string('link_fb')->nullable();
            $table->string('link_x')->nullable();
            $table->string('link_yt')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['link_ig', 'link_fb', 'link_x', 'link_yt']);
        });
    }
};
