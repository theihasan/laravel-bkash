<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(config('bkash.database.table_prefix', 'bkash_') . 'refunds', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id');
            $table->string('original_trx_id');
            $table->string('refund_trx_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('BDT');
            $table->string('transaction_status');
            $table->timestamp('completed_time')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

           $table->foreign('payment_id')
                 ->references('payment_id')
                 ->on(config('bkash.database.table_prefix', 'bkash_') . 'payments')
                 ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('bkash.database.table_prefix', 'bkash_') . 'refunds');
    }
};
