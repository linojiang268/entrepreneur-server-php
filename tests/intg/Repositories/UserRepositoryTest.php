<?php
namespace intg\Entrepreneur\Repositories;

use intg\Entrepreneur\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Entrepreneur\Models\User;

class UserRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    //=========================================
    //            findUser
    //=========================================
    public function testFindUserFound()
    {
        factory(User::class)->create([
            'id'     => 1,
            'mobile' => '13800138000',
        ]);
        $user = $this->getRepository()->findUser('13800138000');
        self::assertEquals(1, $user->id);
        self::assertEquals('13800138000', $user->mobile);
    }

    public function testFindUserNotFound()
    {
        self::assertNull($this->getRepository()->findUser('13800138000'));
    }

    //=========================================
    //            add
    //=========================================
    public function testAddUserNotExists()
    {
        $user = factory(User::class)->make(['mobile' => '13800138000'])->toArray();
        $user['salt'] = str_random(16);
        $user['password'] = str_random(32);
        self::assertGreaterThanOrEqual(1, $this->getRepository()->add($user));
    }

    public function testAddUserExists()
    {
        $user = factory(User::class)->create(['mobile' => '13800138000'])->toArray();
        $user['salt'] = str_random(16);
        $user['password'] = str_random(32);
        try {
            $this->getRepository()->add($user);
        } catch (\Exception $e) {
            self::assertContains('1062 Duplicate entry', $e->getMessage());
        }
    }

    //============================================
    //          updatePassword
    //============================================
    public function testUpdatePassword()
    {
        $user = factory(User::class)->create(['mobile' => '13800138000']);
        self::assertEquals(1, $this->getRepository()->updatePassword($user->id, str_random(32), str_random(16)));
    }

    //============================================
    //          findById
    //============================================
    public function testFindByIdUserExists()
    {
        $user = factory(User::class)->create(['mobile' => '13800138000']);
        self::assertEquals('13800138000', $this->getRepository()->findById($user->id)->mobile);
    }

    public function testFindByIdUserNotExists()
    {
        self::assertNull($this->getRepository()->findById(1));
    }

    //============================================
    //          updateProfile
    //============================================
    public function testUpdateProfile()
    {
        $user = factory(User::class)->create(['mobile' => '13800138000',
                                              'name'   => '腾讯']);
        $this->getRepository()->updateProfile($user->id, [
            'name'  => '阿里',
            'business' => 'aliPay',
        ]);
        $updatedUser = $this->getRepository()->findById($user->id);
        self::assertEquals('阿里', $updatedUser->name);
    }

    //============================================
    //          findAllUsers
    //============================================
    public function testFindAllUsersFound()
    {
        $userOne = factory(User::class)->create([
            'id'     => 1,
            'mobile' => '13800138000',
            'name'   => 'old_avatar_1',
        ]);
        $userTwo = factory(User::class)->create([
            'id'     => 2,
            'mobile' => '13800138022',
            'name'   => 'old_avatar_2',
        ]);

        $this->getRepository()->updateProfile($userOne->id, [
            'name' => 'qq',
        ]);
        $this->getRepository()->updateProfile($userTwo->id, [
            'name' => 'ali',
        ]);

        list($total, $users) = $this->getRepository()->findAllUsers(1, 10);

        self::assertEquals(2, $total);
        self::assertCount(2, $users);
        self::assertEquals(1, $users[0]->id);
        self::assertEquals('qq', $users[0]->name);
    }

    //============================================
    //          multipleAdd
    //============================================
    public function testMultipleAdd()
    {
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $mobile = '1380013800'.$i;
            $user = factory(User::class)->make(['mobile' => $mobile])->toArrayBackstage();
            $user['salt'] = str_random(16);
            $user['password'] = str_random(32);
            $users[] = $user;
        }

        self::assertGreaterThanOrEqual(true, $this->getRepository()->multipleAdd($users));
    }

    /**
     * @return \Entrepreneur\Contracts\Repositories\UserRepository
     */
    private function getRepository()
    {
        return $this->app[ \Entrepreneur\Contracts\Repositories\UserRepository::class ];
    }
}
