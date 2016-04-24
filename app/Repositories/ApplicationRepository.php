<?php
namespace Entrepreneur\Repositories;

use Entrepreneur\Contracts\Repositories\ApplicationRepository as ApplicationRepositoryContract;
use Entrepreneur\Models\Application;
use DB;
use Entrepreneur\Utils\SqlUtil;

class ApplicationRepository implements ApplicationRepositoryContract
{

    const PENDING_STATUS = 0;
    const APPROVE_STATUS = 1;
    const SUCCESS_STATUS = 2;
    const FAILURE_STATUS = 3;
    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\ApplicationRepository::findById()
     */
    public function findById($id)
    {
        $application = Application::with('creator', 'requirement')
            ->where(['id' => $id])->first();

        return $application ? $application : null;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\ApplicationRepository::findByUser()
     */
    public function findByUser($user, $page, $size)
    {
        $query = Application::with('creator', 'requirement')
            ->where(['user_id' => $user]);
        $count = $query->count();
        $applications = $query->forPage($page, $size)
            ->get()
            ->all();

        return [$count, $applications];
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\ApplicationRepository::findByRequirement()
     */
    public function findByRequirement($requirement, $page, $size)
    {
        $query = Application::with('creator')
            ->where(['req_id' => $requirement]);
        $count = $query->count();
        $applications = $query->forPage($page, $size)
            ->get()
            ->all();

        return [$count, $applications];
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\ApplicationRepository::find()
     */
    public function find($page, $size, $status, $orderBy = null)
    {
        $query = Application::with('creator', 'requirement');
        if (!empty($status)) {
            $query->whereIn('status', $status);
        }
        if (is_null($orderBy)) {
            $query->orderBy('updated_at', 'desc');
        } else {
            $query->orderBy($orderBy[0], $orderBy[1]);
        }
        $count = $query->count();
        $applications = $query->forPage($page, $size)
            ->get()
            ->all();

        return [$count, $applications];
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\ApplicationRepository::add()
     */
    public function add(array $application)
    {
        return Application::create($application)->id;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\ApplicationRepository::multipleAdd()
     */
    public function multipleAdd(array $applications)
    {
        return DB::table('applications')->insert($applications);
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\ApplicationRepository::search()
     */
    public function search($keyword, $status, $page, $size)
    {
        $query = Application::with('creator', 'requirement');
        if(strlen($keyword) == 11 && is_numeric($keyword)){
            $query->where(['mobile' => $keyword]);
        }else{
            $query->where('contacts', 'like', '%' . SqlUtil::escape(trim($keyword)) . '%');
        }
        $count = $query->count();
        $applications = $query->forPage($page, $size)
            ->get()
            ->all();

        return [$count, $applications];
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\ApplicationRepository::update()
     */
    public function update($application)
    {
        if (!isset($application['id']) || $application['id'] <= 0) {
            return false;
        }
        $applicationDb = Application::where('id', $application['id'])
            ->first();
        unset($application['id']);
        if (null == $applicationDb || empty($application)) {
            return false;
        }
        if ($application) {
            foreach ($application as $field => $value) {
                $applicationDb->$field = $value;
            }
        }

        return $applicationDb->save();
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\ApplicationRepository::delete()
     */
    public function delete($id)
    {
        return $this->update([
            'id'         => $id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\ApplicationRepository::checkUserApplicationExists()
     */
    public function checkUserApplicationExists($user, $requirement)
    {
        $query = Application::where(['req_id' => $requirement])
            ->where(['user_id' => $user]);
        $count = $query->count();

        return $count ? true : false;
    }

}