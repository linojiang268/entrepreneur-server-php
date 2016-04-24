<?php
namespace Entrepreneur\ApplicationServices;

use Entrepreneur\Contracts\Repositories\ApplicationRepository;
use Auth;
use DB;
use Crypt;
use Entrepreneur\Models\Application;


class ApplicationServices
{
    private $applicationRepository;

    public function __construct(ApplicationRepository $applicationRepository)
    {
        $this->applicationRepository = $applicationRepository;
    }

    /*
     * make application data
     */
    private function makeApplicationData($application, $backstage = false)
    {
        if ($application instanceof Application) {
            if ($backstage) {
                return $application->toArrayBackstage();
            } else {
                return $application->toArray();
            }
        }

        return null;
    }

    /**
     * get applications by user id
     *
     * @param int $user_id
     * @param int $page
     * @param int $size
     *
     * @return array
     */
    public function getMy($user_id, $page, $size)
    {
        $applications = $this->applicationRepository->findByUser($user_id, $page, $size);
        $count = $applications[0];
        $applications = array_map([$this, 'makeApplicationData'], $applications[1]);

        return [$count, $applications];

    }

    /**
     * get application detail
     *
     * @param int $id
     *
     * @return array|null
     * @throws \Exception
     */
    public function getApiOne($id)
    {
        return $this->getOne($id, false);
    }

    /**
     * get application detail
     *
     * @param int $id
     *
     * @return array|null
     * @throws \Exception
     */
    public function getBackstageOne($id)
    {
       return $this->getOne($id, true);
    }

    private function getOne($id, $backstage)
    {
        $application = $this->applicationRepository->findById($id);
        if (!$application) {
            return [];
        }
        return $this->makeApplicationData($application, $backstage);
    }

    /**
     * get applications list by status
     *
     * @param array $status
     * @param int $page
     * @param int $size
     *
     * @return array
     */
    public function getList($status, $page, $size)
    {
        $applications = $this->applicationRepository->find($page, $size, $status);
        $count = $applications[0];
        if (!$count) {
            return [$count, []];
        }
        $applications = array_map(function ($requirement) {
            return $this->makeApplicationData($requirement, true);
        }, $applications[1]);
        return [$count, $applications];
    }

    /**
     * get applications list by requirement id
     *
     * @param int $requirement
     * @param int $page
     * @param int $size
     *
     * @return array
     */
    public function getListByRequirement($requirement, $page, $size)
    {
        $applications = $this->applicationRepository->findByRequirement(intval($requirement) ,$page, $size);
        $count = $applications[0];
        if (!$count) {
            return [$count, []];
        }
        $applications = array_map(function ($requirement) {
            return $this->makeApplicationData($requirement, true);
        }, $applications[1]);
        return [$count, $applications];
    }

    /**
     * search applications by keyword and status
     *
     * @param string $keyword
     * @param int $status
     * @param int $page
     * @param int $size
     *
     * @return array
     */
    public function search($keyword, $status, $page, $size)
    {
        $applications = $this->applicationRepository
            ->search($keyword, $status, $page, $size);
        $count = $applications[0];
        if (!$count) {
            return [$count, []];
        }
        $applications = array_map(function ($requirement) {
            return $this->makeApplicationData($requirement, true);
        }, $applications[1]);
        return [$count, $applications];
    }

    /**
     * add application
     *
     * @param array $application
     *
     * @return int
     */
    public function add($application)
    {
        $rst = $this->applicationRepository->checkUserApplicationExists($application['user_id'], $application['req_id']);
        if($rst){
            throw new \Exception('不可重复申请', 9000);
        }

        return $this->applicationRepository->add($application);
    }

    /**
     * add applications
     *
     * @param array $applications
     *
     * @return int
     */
    public function multipleAdd($applications)
    {
        return $this->applicationRepository->multipleAdd($applications);
    }

    /**
     * delete application
     *
     * @param int $id
     *
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $application = $this->applicationRepository->findById($id);
        if (!$application) {
            throw new \Exception('非法请求');
        }

        return $this->applicationRepository->delete($id);
    }

    /**
     * change application status
     *
     * @param int $id
     * @param int $status
     *
     * @return bool
     */
    public function changeStatus($id, $status)
    {
        $application = $this->applicationRepository->findById($id);
        if (!$application) {
            throw new \Exception('非法请求');
        }

        return $this->applicationRepository->update([
            'id'     => intval($id),
            'status' => intval($status),
        ]);
    }

}