<?php
namespace Entrepreneur\Contracts\Repositories;

interface ApplicationRepository
{
    /**
     * find application by id
     *
     * @param int $id application id
     *
     * @return \Entrepreneur\Models\Application|null
     */
    public function findById($id);

    /**
     * Find user by his/her mobile number
     *
     * @param int $user user id
     * @param int $page
     * @param int $size
     *
     * @return \Entrepreneur\Models\Application|null
     */
    public function findByUser($user, $page, $size);

    /**
     * Find applications by requirement id
     *
     * @param int $requirement requirement id
     * @param int $page
     * @param int $size
     *
     * @return \Entrepreneur\Models\Application|null
     */
    public function findByRequirement($requirement, $page, $size);

    /**
     * find requirement
     *
     * @param int    $page
     * @param int    $size
     * @param array    $status
     * @param string $orderBy
     *
     * @return  array  applications
     */
    public function find($page, $size, $status, $orderBy = null);

    /**
     * add a new application
     *
     * @param array $application
     *
     * @return int   id of the newly added application
     */
    public function add(array $application);

    /**
     * multiple add new applications
     *
     * @param array $applications
     *
     * @return int   id of the newly added applications
     */
    public function multipleAdd(array $applications);

    /**
     * search application
     *
     * @param $keyword
     * @param $status
     * @param $page
     * @param $size
     *
     * @return mixed
     */
    public function search($keyword, $status, $page, $size);

    /**
     * update application
     *
     * @param $application
     *
     * @return bool
     */
    public function update($application);

    /**
     * update application status
     *
     * @param $id
     *
     * @return bool
     */
    public function delete($id);

    /**
     * check user requirement application exists
     *
     * @param int $user
     * @param int $requirement
     *
     * @return bool
     */
    public function checkUserApplicationExists($user, $requirement);
}
