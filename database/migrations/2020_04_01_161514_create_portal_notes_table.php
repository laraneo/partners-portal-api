<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortalNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('portal_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description',255)->nullable();
            $table->bigInteger('portal_department_id')->nullable();
            $table->bigInteger('portal_note_type_id')->nullable();
            $table->string('usuario', 255)->nullable();
            $table->string('share_number', 255)->nullable();
            $table->date('fecha')->nullable();
            $table->string('attach1', 255)->nullable();
            $table->string('attach2', 255)->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portal_notes');
    }
}
