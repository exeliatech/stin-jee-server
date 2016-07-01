<?php namespace App;

use Illuminate\Database\Eloquent\Model;


class Manager extends Model
{

    public $incrementing = false;
    protected $table = 'managers';
    protected $primaryKey = 'object_id';

    protected $fillable=[
        'object_id',
        'name',
        'email',
        'country',
        'created_at',
        'updated_at',
    ];
}