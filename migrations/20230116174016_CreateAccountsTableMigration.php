<?php

use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

class CreateAccountsTableMigration extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $container['db']->schema()->create('accounts', function (Blueprint $table) {
            $table->id('pk_id');
            $table->string('user_name');
            $table->json('account_data');
            $table->timestamps();
        });

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $container['db']->schema()->drop('accounts');
    }
}
