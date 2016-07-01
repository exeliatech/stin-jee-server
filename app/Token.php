<?php namespace App;

use Illuminate\Database\Eloquent\Model;


class Token extends Model
{

    public $incrementing = false;
    protected $table = 'token';
    protected $primaryKey = 'object_id';

    protected $fillable=[
        'object_id',
        'country',
        'batch',
        'manager',
        'created_at',
        'updated_at',
        'email',
        'ends_at',
        'price',
        'purchase_date',
        'purchase_online',
    ];
}