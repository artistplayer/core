<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateFilePlaylistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_playlist', function (Blueprint $table) {
			$table->integer('playlist_id');
			$table->integer('file_id');
			$table->integer('position');
			$table->foreign('playlist_id')->references('id')->on('playlists')->onDelete('cascade');
			$table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
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
        Schema::dropIfExists('file_playlists');
    }
}
