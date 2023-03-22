<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 32)->charset('ascii')->nullable(); //ид сообщения в месседжере
            $table->enum('messager', ['SMS', 'VK', 'WhatsApp', 'Telegram']);

            $table->bigInteger('phone')->unsigned();

            $table->text('text')->charset('utf8mb4')->nullable();

            $table->boolean('incoming')->default(false);
            $table->enum('delivery_status', ['DEFERRED', 'IN_QUEUE', 'ERROR', 'PENDING', 'DELIVERED', 'READ'])->default('delivered');

            // обработчик входящего сообщения если подразумевается реакция на него
            $table->string('notification_class', 200)->charset('ascii')->nullable();
            //$table->json('template_vars')->nullable();

            //id каскадного сообщения, если нужен каскад
            $table->unsignedBigInteger('cascade_id')->nullable();
            $table->foreign('cascade_id', 'messages_message_cascade_fk')
                    ->on('messages_cascades')
                    ->references('id')
                    ->nullOnDelete();
            $table->index('cascade_id', 'messages_message_cascade_idx');

            $table->timestamp('datetime')->useCurrent();


        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages_messages');
        Schema::dropIfExists('messages_message_cascade_fk');
        Schema::dropIfExists('messages_message_cascade_idx');
    }
};
