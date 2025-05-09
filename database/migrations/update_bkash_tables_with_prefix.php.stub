<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $prefix = config('bkash.database.table_prefix', 'bkash_');

        if ($prefix === 'bkash_' || !Schema::hasTable('bkash_payments') || Schema::hasTable($prefix . 'payments')) {
            return;
        }

        Schema::create($prefix . 'payments', function (Blueprint $table) {
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

        Schema::create($prefix . 'refunds', function (Blueprint $table) {
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
                 ->on($prefix . 'payments')
                 ->cascadeOnDelete();
        });

        if (Schema::hasTable('bkash_payments')) {
            DB::statement("INSERT INTO {$prefix}payments SELECT * FROM bkash_payments");
        }

        if (Schema::hasTable('bkash_refunds')) {
            DB::statement("INSERT INTO {$prefix}refunds SELECT * FROM bkash_refunds");
        }
    }

    public function down()
    {
        $prefix = config('bkash.database.table_prefix', 'bkash_');

        if ($prefix === 'bkash_') {
            return;
        }

        Schema::dropIfExists($prefix . 'refunds');
        Schema::dropIfExists($prefix . 'payments');
    }
};
