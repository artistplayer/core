<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('integrity_hash', 32)->unique();
			$table->string('title', 64)->nullable(true);
			$table->string('artist', 32)->nullable(true);
			$table->mediumInteger('filesize');
			$table->string('filename', 255);
			$table->string('format', 8);
			$table->boolean('thumbnail')->default(false);
			$table->string('mime_type', 32);
			$table->mediumInteger('bitrate');
			$table->double('playtime', 15, 6);
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
