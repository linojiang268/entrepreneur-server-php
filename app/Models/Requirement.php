<?php
namespace Entrepreneur\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requirement extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'requirements';

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

    public function applications()
    {
        return $this->belongsTo(\Entrepreneur\Models\Application::class, 'id', 'req_id');
    }

    public function toArrayBackstage()
    {
        $requirement = $this->toArray();
        $requirement = array_merge($requirement, [
            'user_id'    => $this->user_id,
            'mobile'     => $this->mobile,
        ]);
        if ($this->relationLoaded('creator')) {
            $requirement['creator'] = $this->creator->toArrayBackstage();
        } else {
            $requirement['creator'] = [];
        }

        return $requirement;
    }

    public function toArray()
    {
        $requirement = [
            'id'         => $this->id,
            'title'      => $this->title,
            'intro'      => $this->intro,
            'status'     => $this->status,
            'contacts'   => $this->contacts,
            'begin_time' => $this->begin_time,
            'end_time'   => $this->end_time,
            'created_at'  =>  $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : '',
        ];

        if ($this->relationLoaded('creator')) {
            $requirement['creator'] = $this->creator->toArray();
        } else {
            $requirement['creator'] = [];
        }

        return $requirement;
    }
}