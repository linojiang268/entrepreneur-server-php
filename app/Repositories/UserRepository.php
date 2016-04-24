<?php
namespace Entrepreneur\Repositories;

use Entrepreneur\Contracts\Repositories\UserRepository as UserRepositoryContract;
use Entrepreneur\Models\User;
use Entrepreneur\Utils\PaginationUtil;
use DB;

class UserRepository implements UserRepositoryContract
{
    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::findId()
     */
    public function findId($mobile)
    {
        $user = User::where(['mobile' => $mobile], ['id'])->first();
            
        return $user;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::findUser()
     */
    public function findUser($mobile)
    {
        return $this->convertToEntity(User::where('mobile', $mobile)->first());        
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::findIdsByMobiles()
     */
    public function findIdsByMobiles(array $mobiles)
    {
        if (empty($mobiles)) {
            return [];
        }
        // initialize mobileUsers, key is mobile, value is null,
        $mobileUsers = array_combine($mobiles, array_fill(0, count($mobiles), null));
        User::whereIn('mobile', $mobiles)->get()
                                         ->each(function ($user) use (&$mobileUsers) {
                                                $mobileUsers[$user->mobile] = $user->id;
                                         });
        return $mobileUsers;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::add()
     */
    public function add(array $user)
    {
        return User::create($user)->id;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::add()
     */
    public function multipleAdd(array $users)
    {
        return DB::table('users')->insert($users);
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::updatePassword()
     */
    public function updatePassword($user, $password, $salt)
    {
        return User::where('id', $user)
                    ->update(['password' => $password,
                              'salt' => $salt]);
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::updateIdentitySalt()
     */
    public function updateIdentitySalt($user, $salt)
    {
        return 1 == User::where('id', $user)
                        ->update(['identity_salt' => $salt]);
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::findWithTagById()
     */
    public function findWithTagById($user)
    {
        $user = User::with('tags')->find($user);

        return $user ? $user->toArray() : null;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::findById()
     */
    public function findById($id)
    {
        $userModel = User::find($id);

        return $userModel ? $userModel : null;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::updateById()
     */
    public function updateProfile($user, array $profile)
    {
        return User::where('id', $user)->update($profile);
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::updateAvatar()
     */
    public function updateAvatar($user, $avatar) {
        if (null == $user = User::find($user)) {
            throw new \Exception('非法用户');
        }
        $oldAvatarUrl = $user->avatar_url;

        User::where('id', $user->id)
            ->update([ 'avatar_url' => $avatar ]);

        return $oldAvatarUrl;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::findAllUsers()
     */
    public function findAllUsers($page, $pageSize, $mobile = null, $name = null, $status = null)
    {
        $query = User::where('id', '>', 0);
        if ($mobile) {
            $query->where('mobile', $mobile);
        }
        if ($name) {
            $query->where('name', $name);
        }
        if ($status) {
            $query->where('status', $status);
        }else{
            $query->where('status', '>', -1);
        }
        $count = $query->count();
        $page = PaginationUtil::genValidPage($page, $count, $pageSize);
        $users = $query->forPage($page, $pageSize)->get()->all();

        $users = array_map([ $this, 'convertToEntity' ], $users);

        return [$count, $users];
    }

    /**
     * {@inheritdoc}
     * @return \Entrepreneur\Models\User|null
     */
    private function convertToEntity($user)
    {
        return $user ? $user : null;
    }

    /**
     * {@inheritdoc}
     * @see \Entrepreneur\Contracts\Repositories\UserRepository::delete()
     */
    public function delete($id)
    {
        return User::where('id', $id)
            ->update(['status' => -1]);
    }
}
