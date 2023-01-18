<?php

namespace Sync\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{

    /**
     * Первичный ключ таблицы БД.
     *
     * @var string
     */
    protected $primaryKey = 'pk_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['fk_account_id', 'fk_contact_name_email'];

    public $timestamps = false;

}