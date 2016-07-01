<?php namespace App;

use Illuminate\Database\Eloquent\Model;


class Batch extends Model
{

    public $incrementing = false;
    protected $table = 'batch';
    protected $primaryKey = 'object_id';

    protected $fillable=[
        'object_id',
        'country',
        'promotion',
        'active',
        'ends_at',
        'email',
        'purchased_online',
        'created_at',
        'updated_at',
    ];
}