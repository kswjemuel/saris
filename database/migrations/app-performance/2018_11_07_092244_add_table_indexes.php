<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_bee', function (Blueprint $table) {
            $table->index('customer_ranking', 'customer_ranking');
            $table->index('rejection_rules_status', 'rejection_rules_status');
            $table->index('customer_approval_status', 'customer_approval_status');
            $table->index('customer_failed_rule', 'customer_failed_rule');
        });

        Schema::table('customer_loans', function (Blueprint $table) {
            $table->index('customer_bee_history_id', 'customer_bee_history_id');
            $table->index('customer_bee_ranking', 'customer_bee_ranking');
            $table->index('loan_status', 'loan_status');
            $table->index('principle_disbursed', 'principle_disbursed');
            $table->index('loan_due_on', 'loan_due_on');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_loans', function (Blueprint $table) {
            //
        });
    }
}
