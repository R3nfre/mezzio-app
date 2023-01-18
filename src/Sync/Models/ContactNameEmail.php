<?php

namespace Sync\Models;

use Illuminate\Database\Eloquent\Model;

class ContactNameEmail extends Model
{

    /**
     * Первичный ключ таблицы БД.
     *
     * @var string
     */
    protected $primaryKey = 'pk_id';

    /**
     * Таблица БД, ассоциированная с моделью.
     *
     * @var string
     */
    protected $table = 'contacts_name_email';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contact_name', 'email', 'contact_id'];

    public $timestamps = false;
}