<?php
namespace Entrepreneur\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'applications';

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
    protected $hidden = [];

    public function creator()
    {
        return $this->belongsTo(\Entrepreneur\Models\User::class, 'user_id', 'id');
    }

    public function requirement()
    {
        return $this->belongsTo(\Entrepreneur\Models\Requirement::class, 'req_id', 'id');
    }

    public function toArrayBackstage()
    {
        $application = $this->toArray();
        if ($this->relationLoaded('creator')) {
            $application['creator'] = $this->creator->toArrayBackstage();
        } else {
            $application['creator'] = [];
        }

        if ($this->relationLoaded('requirement')) {
            $application['requirement'] = $this->requirement->toArrayBackstage();
        } else {
            $application['requirement'] = [];
        }

        return $application;
    }

    public function toArray()
    {
        $application = [
            'id'       => $this->id,
            'user_id'  => $this->user_id,
            'req_id'   => $this->req_id,
            'contacts' => $this->contacts,
            'mobile'   => $this->mobile,
            'intro'    => $this->intro,
            'status'   => $this->status,
        ];

        if ($this->relationLoaded('creator')) {
            $application['creator'] = $this->creator->toArray();
        } else {
            $application['creator'] = [];
        }

        if ($this->relationLoaded('requirement')) {
            $application['requirement'] = $this->requirement->toArray();
        } else {
            $application['requirement'] = [];
        }

        return $application;
    }

}