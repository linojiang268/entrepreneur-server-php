<?php
namespace Entrepreneur\Repositories;

use Entrepreneur\Contracts\Repositories\RequirementRepository as RequirementRepositoryContract;
use Entrepreneur\Models\Requirement;
use Entrepreneur\Utils\SqlUtil;
use DB;

class RequirementRepository implements RequirementRepositoryContract
{

    const PENDING_STATUS = 0;
    const APPROVE_STATUS = 1;
    const STOP_STATUS = 2;
    const CLOSE_STATUS = 3;

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\RequirementRepository::findById()
     */
    public function findById($id)
    {
        $requirement = Requirement::with('creator')
            ->where(['id' => $id])->first();

        return $requirement ? $requirement : null;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\RequirementRepository::findByUser()
     */
    public function findByUser($user, $page, $size)
    {
        $query = Requirement::with('creator')
            ->where(['user_id' => $user]);
        $count = $query->count();
        $requirements = $query->forPage($page, $size)
            ->get()
            ->all();

        return [$count, $requirements];
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\RequirementRepository::find()
     */
    public function find($page, $size, $status, $orderBy = null, $timeout = false)
    {
        $query = Requirement::with('creator');
        if (!empty($status)) {
            $query->whereIn('status', $status);
        }
        if($timeout){
            $query->where('end_time', '>', date('Y-m-d'));
        }
        if (is_null($orderBy)) {
            $query->orderBy('updated_at', 'desc');
        } else {
            $query->orderBy($orderBy[0], $orderBy[1]);
        }
        $count = $query->count();
        $requirements = $query->forPage($page, $size)
            ->get()
            ->all();

        return [$count, $requirements];
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\RequirementRepository::add()
     */
    public function add(array $requirement)
    {
        return Requirement::create($requirement)->id;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\RequirementRepository::multipleAdd()
     */
    public function multipleAdd(array $requirements)
    {
        return DB::table('requirements')->insert($requirements);
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\RequirementRepository::search()
     */
    public function search($keyword, $status, $page, $size)
    {
        $query = Requirement::with('creator');
        if(strlen($keyword) == 11 && is_numeric($keyword)){
            $query->where(['mobile' => $keyword]);
        }else{
            $query->where('title', 'like', '%' . SqlUtil::escape(trim($keyword)) . '%');
        }
        if (!empty($status)) {
            $query->whereIn('status', $status);
        }
        $count = $query->count();
        $requirements = $query->forPage($page, $size)
            ->get()
            ->all();

        return [$count, $requirements];
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\RequirementRepository::update()
     */
    public function update($requirement)
    {
        if (!isset($requirement['id']) || $requirement['id'] <= 0) {
            return false;
        }
        $requirementDb = Requirement::where('id', $requirement['id'])
            ->first();
        unset($requirement['id']);
        if (null == $requirementDb || empty($requirement)) {
            return false;
        }
        if ($requirement) {
            foreach ($requirement as $field => $value) {
                $requirementDb->$field = $value;
            }
        }

        return $requirementDb->save();
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\RequirementRepository::delete()
     */
    public function delete($id)
    {
        return $this->update([
            'id'         => $id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);
    }
}