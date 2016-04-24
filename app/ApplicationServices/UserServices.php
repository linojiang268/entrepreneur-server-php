<?php
namespace Entrepreneur\ApplicationServices;

use Entrepreneur\Contracts\Repositories\UserRepository;
use Entrepreneur\Hashing\PasswordHasher;
use Auth;
use DB;
use Crypt;

/**
 * User related service
 *
 */
class UserServices
{
    const STATUS_DELETE = -1;
    const STATUS_MO_ACTIVATION = 0;
    const STATUS_ACTIVATION = 1;
    /**
     * repository for user
     *
     * @var \Entrepreneur\Contracts\Repositories\UserRepository
     */
    private $userRepository;

    /**
     * hasher to hash user's password. we hardcode our hasher here (
     * since we don't want change framework's hasher), although our
     * hasher complies with \Illuminate\Contracts\Hashing\Hasher.
     *
     * @var \Entrepreneur\Hashing\PasswordHasher
     */
    private $hasher;


    public function __construct(UserRepository $userRepository,
                                PasswordHasher $hasher = null)
    {
        $this->userRepository = $userRepository;
        $this->hasher = $hasher ?: new PasswordHasher();
    }

    /**
     * User registration
     *
     * @param string $mobile
     * @param string $password length between 6 and 32
     * @param array  $profile  length between 6 and 32
     *
     * @return int                  id for the registered user
     * @throws \Exception  if user already exists
     */
    public function register($mobile, $password, $profile = [])
    {
        $userId = null;
        $user = $this->userRepository->findUser($mobile);
        if ($user) {
            throw new \Exception('该手机号已注册');
        }
        $salt = $this->generateSalt();
        $password = $this->hashPassword($password, $salt);
        $registerData = [
            'mobile'   => $mobile,
            'salt'     => $salt,
            'password' => $password,
            'status'   => self::STATUS_MO_ACTIVATION,
        ];
        if (!empty($profile)) {
            $registerData = array_merge($registerData, $profile);
        }

        return $this->userRepository->add($registerData);
    }

    public function changeUserStatus($id, $status)
    {
        // Check originalPassword
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \Exception('非法请求');
        }
        if ($user->status == $status)
            return true;

        return $this->userRepository->updateProfile($id, [
            'status' => intval($status),
        ]);
    }

    /**
     * complete user profile
     *
     * @param integer $userId
     * @param array   $profile detail of a request for user profile, keys as below:
     *                         - name    string
     *                         - intro   txt
     *
     */
    public function completeProfile($userId, array $profile)
    {
        unset($profile['password']);
        unset($profile['mobile']);
        $this->userRepository->updateProfile($userId, $profile);
    }

    /**
     * Change user password
     *
     * @param integer $userId           user id
     * @param string  $originalPassword user current password
     * @param string  $newPassword      user new password, will be set for user
     *
     * @return boolean
     * @throws \Exception
     */
    public function changePassword($userId, $originalPassword, $newPassword)
    {
        // Check originalPassword
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \Exception('非法请求');
        }
        if (!$this->checkPassword($originalPassword, $user)) {
            throw new \Exception('当前密码不正确');
        }
        $salt = $this->generateSalt();
        $password = $this->hashPassword($newPassword, $salt);
        $success = (1 == $this->userRepository->updatePassword($user->id, $password, $salt));

        return $success;
    }

    public function backstageResetPassword($userId)
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \Exception('非法请求');
        }
        $salt = $this->generateSalt();
        $password = $this->hashPassword("000000", $salt);
        $success = (1 == $this->userRepository->updatePassword($user->id, $password, $salt));

        return $success;
    }

    /**
     * check whether passed in password equeal hashedPassword
     *
     * @param string $password passed in password should be check
     * @param array  $user
     *
     * @return boolean
     */
    private function checkPassword($password, $user)
    {
        $ret = $this->hashPassword($password, $user->salt);

        return $user->password == $ret;
    }


    /**
     * user login
     *
     * @param string $mobile      user's mobile
     * @param string $password    plain password
     * @param bool   $remember    true to remember the user once successfully logged in.
     *                            false otherwise.
     *
     * @return bool  true if login successfully, false otherwise.
     */
    public function login($mobile, $password, $remember = false)
    {
        return Auth::attempt([
            'mobile'   => $mobile,
            'password' => $password,
        ], $remember);
    }

    /**
     * logout user
     */
    public function logout()
    {
        // Get rememberme token
        $user = Auth::user();
        $rememberToken = $user->getRememberToken();

        Auth::logout();

        // save remember token back to user, make sure remember token
        // not be changed. Cause we want a user not be kicked when
        // the same user logout in other side.
        // eg: a user, whoes mobile is 13800138000,
        // logined on a android device, the same user also logined on
        // pc browser, when user logout from pc, the user still logined
        // on android device.
        Auth::getProvider()->updateRememberToken(
            $user, $rememberToken);
    }

    /**
     * User reset password
     *
     * @param string $mobile   user's mobile
     * @param string $password plain password
     * @param string $salt     salt value
     *
     * @throws \Exception   if user not exist
     * @return bool                     true if password is reset. false otherwise
     */
    public function resetPassword($mobile, $password, $salt = null)
    {
        if (null == ($user = $this->userRepository->findUser($mobile))) {
            throw new \Exception();
        }
        $salt = $salt ?: $this->generateSalt(); //  generate salt if needed
        $password = $this->hashPassword($password, $salt);

        return 1 == $this->userRepository->updatePassword($user->id, $password, $salt);
    }

    /**
     * list all users
     *
     * @param int $page
     * @param int $pageSize
     *
     * @return array                first element is total count of users
     *                              second element is user array, which element
     *                              is \Jihe\Entities\User object
     */
    public function listUsers($page, $pageSize, $status = null)
    {
        $users = $this->userRepository->findAllUsers($page, $pageSize, null, null, $status);
        $count = $users[0];
        $users = array_map([$this, 'makeUserData'], $users[1]);

        return [$count, $users];
    }

    private function makeUserData($user)
    {
        return [
            'id'       => $user->id,
            'mobile'   => $user->mobile,
            'name'     => $user->name,
            'business' => $user->business,
            'status'   => $user->status,
        ];
    }

    /**
     *
     * hash user's raw password
     *
     * @param string $password plain text form of user's password
     * @param string $salt     salt
     *
     * @return string             hashed password
     */
    private function hashPassword($password, $salt)
    {
        $ret = $this->hasher->make($password, ['salt' => $salt]);

        return $ret;
    }

    /**
     * generate salt for hashing password
     *
     * @return string
     */
    private function generateSalt()
    {
        return str_random(16);
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
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \Exception('非法请求');
        }

        return $this->userRepository->delete($id);
    }

    public function checkStatus($mobile)
    {
        $user = $this->userRepository->findId($mobile);
        if (!$user) {
            throw new \Exception('非法请求');
        }
        if($user->status == self::STATUS_ACTIVATION){
            return true;
        }else{
            return false;
        }
    }

}
