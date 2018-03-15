<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Entry extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    
    //default är att det måste finns timestampkolumner i varje databastabell
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    //default är att tabellnamnet = modelnamnet + 's' 
    protected $table = 'mrbs_entry';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //lägg till samtliga kolumner
         'name', 'create_by'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    protected $hidden = ['confirmation_code'];
    /*
    protected $hidden = [
        'password_hash'
    ];
    */
}
