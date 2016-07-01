<?php namespace App;

use Illuminate\Database\Eloquent\Model;


class Device extends Model
{

    public $incrementing = false;
    protected $table = 'device';
    protected $primaryKey = 'object_id';

    protected $fillable=[
        'object_id',
        'location_latitude',
        'location_longitude',
        'device_token',
        'device_type',
        'sended',
        'device_id',
        'last_visit_date',
        'id',
        'created_at',
        'updated_at',
    ];
}