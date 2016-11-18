<?php namespace App;

use Illuminate\Database\Eloquent\Model;


class Specials extends Model
{

    public $incrementing = false;
    protected $table = 'specials';
    protected $primaryKey = 'object_id';

    protected $fillable=[
        'object_id',
        'country',
        'country_code',
        'name',
        'description',
        'status',
        'location_latitude',
        'location_longitude',
        'address',
        'phone',
        'type',
        'source',
        'action',
        'image',
        'batch',
        'token',
        'store',
        'site',
        'let_admin_choose_image',
        'valid_for',
        'active',
        'activated_at',
        'ends_at',
        'image600',
        'image320',
        'image100',
        'views_count',
        'store_logo',
        'store_logo_bg',
        'created_at',
        'updated_at',
    ];
}