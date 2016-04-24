<?php
namespace Entrepreneur\Contracts\Repositories;

interface RequirementRepository
{
    /**
     * find requirement by id
     *
     * @param int $id requirement id
     *
     * @return \Entrepreneur\Models\Requirement|null
     */
    public function findById($id);

    /**
     * Find user by his/her mobile number
     *
     * @param int $user user id
     * @param int $page
     * @param int $size
     *
     * @return \Entrepreneur\Models\Requirement|null
     */
    public function findByUser($user, $page, $size);

    /**
     * find requirement
     *
     * @param int    $page
     * @param int    $size
     * @param array  $status
     * @param string $orderBy
     *
     * @return  array  requirements
     */
    public function find($page, $size, $status, $orderBy = null, $timeout = false);

    /**
     * add a new requirement
     *
     * @param array $requirement
     *
     * @return int   id of the newly added requirement
     */
    public function add(array $requirement);

    /**
     * multiple add new requirements
     *
     * @param array $requirements
     *
     * @return int   id of the newly added requirements
     */
    public function multipleAdd(array $requirements);

    /**
     * search requirement
     *
     * @param string $keyword
     * @param array $status
     * @param int $page
     * @param int $size
     *
     * @return mixed
     */
    public function search($keyword, $status, $page, $size);

    /**
     * update requirement
     *
     * @param $requirement
     *
     * @return bool
     */
    public function update($requirement);

    /**
     * update requirement status
     *
     * @param $id
     *
     * @return bool
     */
    public function delete($id);

}
