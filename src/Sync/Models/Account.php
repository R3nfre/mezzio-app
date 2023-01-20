<?php

namespace Sync\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    /**
     * Таблица БД, ассоциированная с моделью.
     *
     * @var string
     */
    protected $table = 'accounts';

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
    protected $fillable = ['user_name', 'account_data', 'unisender_api_key'];

    /**
     * @param Builder $builder
     * @param int $hour
     * @return Collection
     */
    public function scopeToUpdate(Builder $builder, int $hour): Collection
    {
        return $builder->get()->filter(function (Account $account) use ($hour) {
           return (new Carbon(json_decode($account->account_data, true)['expires']))->diff(Carbon::now())->h < $hour;
        });
    }

}