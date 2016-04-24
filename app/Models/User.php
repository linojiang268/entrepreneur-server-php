<?php
namespace Entrepreneur\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['salt', 'password', 'remember_token'];

    public function toArray()
    {
        $user = [
            'id'         => $this->id,
            'name'       => $this->name,
            'created_at'  =>  $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : '',
            'status'     => $this->status,
        ];

        return $user;
    }

    public function toArrayBackstage()
    {
        $user = $this->toArray();
        $user = array_merge($user, [
            'mobile'     => $this->mobile,
        ]);

        return $user;
    }

}
