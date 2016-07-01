<?php namespace App;

use Illuminate\Database\Eloquent\Model;


class ManagerBillingLog extends Model
{

    public $incrementing = false;
    protected $table = 'managerBillingLog';
    protected $primaryKey = 'object_id';

    protected $fillable=[
        'object_id',
        'country',
        'month',
        'tokens',
        'price',
        'country',
        'manager_email',
        'manager_name',
        'status',
        'manager',
        'created_at',
        'updated_at',
    ];
}