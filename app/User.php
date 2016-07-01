<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract  {
    use Authenticatable;

// Add your other methods here

    public $incrementing = true;
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable=[
        'id',
        'email',
        'name',
        'password',
    ];
    protected $hidden = ['password', 'remember_token'];

    public function getAuthIdentifier() {
        return $this->getKey();
    }
    public function getAuthPassword() {
        return $this->password;
    }
}