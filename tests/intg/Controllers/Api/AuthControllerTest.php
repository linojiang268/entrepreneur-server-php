<?php
namespace intg\Entrepreneur\Controllers\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use intg\Entrepreneur\TestCase;
use Entrepreneur\Models\User;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    //=========================================
    //             Registration
    //=========================================
    public function testSuccessfulRegistration()
    {
        $this->startSession();
        $this->ajaxPost('api/register', [
            'mobile'   => '13800138000',
            'password' => '*******',
            'name'     => '腾讯',
            'business' => '水果',
            '_token'   => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 0]);
        $this->seeInDatabase('users', [
            'mobile'   => '13800138000',
            'name'     => '腾讯',
            'business' => '水果',
        ]);
    }

    public function testRegistrationWithInvalidMobile()
    {
        $this->startSession();
        $this->ajaxPost('api/register', [
            'mobile'   => '1380013800',
            'password' => '*******',
            'name'     => '腾讯',
            'business' => '水果',
            '_token'   => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 10000]);

        $response = json_decode($this->response->getContent());
        self::assertEquals('手机号格式错误', $response->message);
      }

    public function testRegistrationUserExists()
    {
        factory(User::class)->create([
            'mobile' => '13800138000',
        ]);
        $this->startSession();
        $this->ajaxPost('api/register', [
            'mobile'   => '13800138000',
            'password' => '*******',
            'name'     => '腾讯',
            'business' => '水果',
            '_token'   => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 10000]);
        $response = json_decode($this->response->getContent());
        self::assertEquals('该手机号已注册', $response->message);
    }

    //=========================================
    //             Login
    //=========================================
    public function testSuccessfulLogin()
    {
        factory(User::class)->create([
            'mobile'         => '13800138000',
            'salt'           => 'ptrjb30aOvqWJ4mG',
            'name'           => 'victory',
            'password'       => '7907C7ED5F7F4E4872E24CAB8292464F',  // raw password is '*******'
            'remember_token' => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);

        $this->startSession();
        $this->ajaxPost('api/login', [
            'mobile'   => '13800138000',
            'password' => '*******',
            '_token'   => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 0]);
        $cookies = $this->response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('XSRF-TOKEN', $cookies[0]->getName());
    }

    //=========================================
    //          logout
    //=========================================
    public function testSuccessfullyLogout()
    {
        $user = factory(User::class)->create([
            'id'             => 1,
            'mobile'         => '13800138000',
            'remember_token' => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);
        $this->startSession();
        $this->actingAs($user)
            ->ajaxGet('api/logout');
        $this->seeJsonContains(['code' => 0]);
        $this->seeInDatabase('users', [
            'mobile'         => '13800138000',
            'remember_token' => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);

    }

    //=========================================
    //          changePassword
    //=========================================
    public function testChangePassword()
    {
        $user = factory(User::class)->create([
            'mobile'         => '13800138000',
            'salt'           => 'ptrjb30aOvqWJ4mG',
            'name'           => 'victory',
            'password'       => '7907C7ED5F7F4E4872E24CAB8292464F',  // raw password is '*******'
            'remember_token' => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);
        $this->startSession();
        $this->actingAs($user)
            ->ajaxPost('api/password/change', [
                'original_password' => '*******',
                'new_password'      => '123123',
                '_token'            => csrf_token(),
            ]);
        $this->seeJsonContains(['code' => 0]);
        $this->ajaxPost('api/login', [
            'mobile'   => '13800138000',
            'password' => '123123',
            '_token'   => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 0]);
        $cookies = $this->response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('XSRF-TOKEN', $cookies[0]->getName());
    }

    public function testSuccessfullyLogout_SessionAreadyExpired()
    {
        $this->ajaxGet('api/logout')->seeJsonContains(['code' => 0]);
    }

}
