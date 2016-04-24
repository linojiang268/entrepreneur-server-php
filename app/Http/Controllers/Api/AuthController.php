<?php
namespace Entrepreneur\Http\Controllers\Api;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Entrepreneur\Http\Controllers\Controller;
use Entrepreneur\ApplicationServices\UserServices;

class AuthController extends Controller
{
    /**
     * user registration
     */
    public function register(Request $request, UserServices $userService)
    {
        $this->validate($request, [
            'mobile'   => 'required|mobile',
            'password' => 'required|between:6,32',
            'name'     => 'required|between:1,128',
            'business' => 'required|between:1,128',
        ], [
            'mobile.required'   => '手机号未填写',
            'mobile.mobile'     => '手机号格式错误',
            'password.required' => '密码未填写',
            'password.between'  => '密码错误',
            'name.required'     => '公司未填写',
            'name.between'      => '公司格式错误',
            'business.required' => '经营业务未填写',
            'business.between'  => '经营业务格式错误',
        ]);

        try {
            // register the user
            $profile = [
                'name'     => $request->input('name'),
                'business' => $request->input('business'),
            ];
            $userService->register($request->input('mobile'),
                $request->input('password'),
                $profile);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }

        return $this->json('注册成功');
    }

    /**
     * user login
     */
    public function login(Request $request,
                          UserServices $userService,
                          Guard $auth
    )
    {
        $this->validate($request, [
            'mobile'   => 'required|mobile',
            'password' => 'required|between:6,32',
        ], [
            'mobile.required'   => '手机号未填写',
            'mobile.mobile'     => '手机号格式错误',
            'password.required' => '密码未填写',
            'password.between'  => '密码错误',
        ]);

        try {
            if (!$userService->checkStatus($request->input('mobile'))) {
                return $this->jsonException('账号尚未通过审核');
            }
            if (!$userService->login($request->input('mobile'),
                $request->input('password'),
                $request->has('remember'))
            ) {
                // hide the underlying errors so that malicious routines
                // won't know what the exact error is
                return $this->jsonException('密码错误');
            };

            $user = $auth->user();

            return $this->json([
                'name'     => $user->name,
                'mobile'   => $user->mobile,
                'business' => $user->business,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /**
     * user logout
     */
    public function logout(
        Guard $auth,
        UserServices $userService
    )
    {
        if ($auth->guest()) {
            return $this->json();
        }
        $userService->logout();

        return $this->json();
    }


    /**
     * user reset password form
     */
    public function resetPasswordForm()
    {
        return $this->json([
            '_token' => csrf_token(),
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request, Guard $auth,
                                   UserServices $userService
    )
    {
        $this->validate($request, [
            'original_password' => 'required|between:6,32',
            'new_password'      => 'required|between:6,32',
        ], [
            'original_password.required' => '当前密码未填写',
            'original_password.between'  => '当前密码格式错误',
            'new_password.required'      => '新密码未填写',
            'new_password.between'       => '新密码格式错误',
        ]);

        try {
            $userService->changePassword($auth->user()->getAuthIdentifier(),
                $request->input('original_password'),
                $request->input('new_password'));
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }

        return $this->json('修改密码成功');
    }

    /**
     * user reset password
     */
    public function resetPassword(Request $request, UserServices $userService)
    {
        $this->validate($request, [
            'mobile'   => 'required|mobile',   // a 11-digit string
            'password' => 'required|between:6,32',
        ], [
            'mobile.required'   => '手机号未填写',
            'mobile.mobile'     => '手机号格式错误',
            'password.required' => '密码未填写',
            'password.between'  => '密码错误',
        ]);

        try {
            $userService->resetPassword($request->input('mobile'),
                $request->input('password'));
        } catch (\Exception $e) {
            return $this->jsonException($e);
        }

        return $this->json();
    }

    public function resetPasswordBackstage($id, UserServices $userService)
    {
        try {
            $ret = $userService->backstageResetPassword(intval($id));

            return $this->json(['result' => $ret]);
        } catch (\Exception $e) {
            return $this->jsonException($e);
        }
    }

    public function approve($id, UserServices $userService)
    {
        try {
            $userService->changeUserStatus($id, UserServices::STATUS_ACTIVATION);
        } catch (\Exception $e) {
            return $this->jsonException($e);
        }

        return $this->json();
    }

    public function pendingList(Request $request, UserServices $userService)
    {
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($count, $users) = $userService->listUsers($page, $size, UserServices::STATUS_MO_ACTIVATION);

            return $this->json([
                'total' => intval(ceil($count / $size)),
                'users' => $users,
            ]);
        } catch (\Exception $e) {
            return $this->jsonException($e);
        }
    }

    public function backstageList(Request $request, UserServices $userService)
    {
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($count, $users) = $userService->listUsers($page, $size);

            return $this->json([
                'total' => intval(ceil($count / $size)),
                'users' => $users,
            ]);
        } catch (\Exception $e) {
            return $this->jsonException($e);
        }
    }

    public function delete($id, UserServices $userServices)
    {
        try {
            $ret = $userServices->delete(intval($id));

            return $this->json(['result' => $ret]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    public function userBackstageListView()
    {
        return view('welcome.userlist');
    }
}
