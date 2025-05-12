<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(config('bkash.database.table_prefix', 'bkash_') . 'payments', function (Blueprint $table) {
             $table->id();
             $table->string('payment_id')->unique();
             $table->string('trx_id')->nullable();
             $table->string('agreement_id')->nullable();
             $table->string('payer_reference')->nullable();
             $table->string('customer_msisdn')->nullable();
             $table->decimal('amount', 10, 2);
             $table->string('currency')->default('BDT');
             $table->string('intent')->default('sale');
             $table->string('merchant_invoice_number');
             $table->string('transaction_status');
             $table->timestamp('payment_create_time')->nullable();
             $table->timestamp('payment_execute_time')->nullable();
             $table->timestamp('agreement_execute_time')->nullable();
             $table->string('agreement_status')->nullable();
             $table->string('status_code')->nullable();
             $table->string('status_message')->nullable();
             $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('bkash.database.table_prefix', 'bkash_') . 'payments');
    }
};
