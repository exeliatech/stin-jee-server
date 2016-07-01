<?php namespace App;

use Illuminate\Database\Eloquent\Model;


class Transaction extends Model
{

    public $incrementing = false;
    protected $table = 'transaction';
    protected $primaryKey = 'object_id';

    protected $fillable=[
        'object_id',
        'card_number',
        'person',
        'amount',
        'batch_id',
        'country',
        'email',
        'company',
        'city',
        'store',
        'address',
        'province',
        'postal_id',
        'fiscal_id',
        'updated',
        'tokens',
        'buyed_online',
        'online',
        'created_at',
        'updated_at',
    ];
}