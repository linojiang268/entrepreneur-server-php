<?php
namespace Entrepreneur\ApplicationServices;

use Entrepreneur\Contracts\Repositories\RequirementRepository;
use Auth;
use DB;
use Crypt;
use Entrepreneur\Models\Requirement;

class RequirementServices
{
    private $requirementRepository;

    public function __construct(RequirementRepository $requirementRepository)
    {
        $this->requirementRepository = $requirementRepository;
    }

    /*
     * set requirement value
     */
    private function makeRequirementData($requirement, $backstage = false)
    {
        if ($requirement instanceof Requirement) {
            if ($backstage) {
                return $requirement->toArrayBackstage();
            } else {
                return $requirement->toArray();
            }
        }

        return null;
    }

    /**
     * get user publish requirements
     *
     * @param int $user_id
     * @param int $page
     * @param int $size
     *
     * @return array
     */
    public function getMy($user_id, $page, $size)
    {
        $requirements = $this->requirementRepository->findByUser($user_id, $page, $size);
        $count = $requirements[0];
        $requirements = array_map([$this, 'makeRequirementData'], $requirements[1]);

        return [$count, $requirements];

    }

    /**
     * get requirement detail
     *
     * @param int $id
     *
     * @return array|null
     */
    public function getApiOne($id)
    {
        return $this->getOne($id, false);
    }

    /**
     * get requirement detail
     *
     * @param int $id
     *
     * @return array|null
     */
    public function getBackstageOne($id)
    {
        return $this->getOne($id, true);
    }

    private function getOne($id, $backstage)
    {
        $requirement = $this->requirementRepository->findById($id);
        if (!$requirement) {
            return [];
        }

        return $this->makeRequirementData($requirement, $backstage);
    }

    /**
     * get requirements list by status
     *
     * @param int $status
     * @param int $page
     * @param int $size
     *
     * @return array
     */
    public function getApiList($status, $page, $size)
    {
        return $this->getListData(false, $status, $page, $size, true);
    }

    /**
     * backstage get requirements list by status
     *
     * @param int|null $status
     * @param int      $page
     * @param int      $size
     *
     * @return array
     */
    public function getBackstageList($status, $page, $size)
    {
        return $this->getListData(true, $status, $page, $size);
    }

    private function getListData($backstage, $status, $page, $size, $timeout = false)
    {
        if(!is_array($status)){
            if(is_null($status)){
                $status = null;
            }else{
                $status = [$status];
            }
        }
        $requirements = $this->requirementRepository->find($page, $size, $status, null, $timeout);
        $count = $requirements[0];
        $requirements = array_map(function ($requirement) use ($backstage) {
            return $this->makeRequirementData($requirement, $backstage);
        }, $requirements[1]);

        return [$count, $requirements];
    }

    /**
     * search requirements by keyword and status
     *
     * @param string $keyword
     * @param int    $status
     * @param int    $page
     * @param int    $size
     *
     * @return array
     */
    public function requirementApiSearch($keyword, $status, $page, $size)
    {
        return $this->search(false, $keyword, $status, $page, $size);
    }

    /**
     * backstage search requirements by keyword and status
     *
     * @param string $keyword
     * @param array  $status
     * @param int    $page
     * @param int    $size
     *
     * @return array
     */
    public function requirementBackstageSearch($keyword, $status, $page, $size)
    {
        return $this->search(true, $keyword, $status, $page, $size);
    }

    private function search($backstage, $keyword, $status, $page, $size)
    {
        $requirements = $this->requirementRepository
            ->search($keyword, $status, $page, $size);
        $count = $requirements[0];
        $requirements = array_map(function ($requirement) use ($backstage) {
            return $this->makeRequirementData($requirement, $backstage);
        }, $requirements[1]);

        return [$count, $requirements];
    }

    /**
     * pending requirement
     *
     * @param array $requirement
     *
     * @return int
     */
    public function add($requirement)
    {
        return $this->requirementRepository->add($requirement);
    }

    /**
     * add requirements
     *
     * @param array $requirements
     *
     * @return int
     */
    public function multipleAdd($requirements)
    {
        return $this->requirementRepository->multipleAdd($requirements);
    }

    /**
     * delete requirement
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete($id)
    {
        $requirement = $this->requirementRepository->findById($id);
        if (!$requirement) {
            throw new \Exception('非法请求');
        }

        return $this->requirementRepository->delete($id);
    }

    /**
     * change requirement status
     *
     * @param int $id
     * @param int $status
     *
     * @return bool
     * @throws \Exception
     */
    public function changeStatus($id, $status)
    {
        $requirement = $this->requirementRepository->findById($id);
        if (!$requirement) {
            throw new \Exception('非法请求');
        }

        return $this->requirementRepository->update([
            'id'     => intval($id),
            'status' => intval($status),
        ]);
    }
}