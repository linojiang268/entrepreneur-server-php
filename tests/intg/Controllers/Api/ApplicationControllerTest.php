<?php
namespace intg\Entrepreneur\Controllers\Api;

use Entrepreneur\Models\Application;
use Entrepreneur\Models\Requirement;
use Entrepreneur\Repositories\ApplicationRepository;
use Entrepreneur\Repositories\RequirementRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use intg\Entrepreneur\TestCase;
use Entrepreneur\Models\User;

class ApplicationControllerTest extends TestCase
{
    use DatabaseTransactions;

    //=========================================
    //             list
    //=========================================
    public function testSuccessfulPendingApplicationsList()
    {
        $this->createData();
        $this->ajaxGet('web/application/list');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        self::assertEquals(1, $response['total']);
        self::assertEquals(4, count($response['applications']));
    }

    public function testSuccessfulRequirementList_NoData()
    {
        $this->ajaxGet('/api/requirement/list');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        self::assertEquals(0, $response['total']);
        self::assertEquals(0, count($response['requirements']));
    }

    //=========================================
    //             MyList
    //=========================================
    public function testSuccessfulMyApplicationList()
    {
        $this->startSession();
        $user = factory(User::class)->make([
            'id'     => 11,
            'mobile' => '13800138011',
        ]);
        $this->createData();
        $this->actingAs($user)
            ->ajaxGet('/api/application/mylist');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        self::assertEquals(1, $response['total']);
        self::assertEquals(1, count($response['applications']));
    }

    public function testSuccessfulMyApplication_NoData()
    {
        $this->startSession();
        $user = factory(User::class)->make([
            'id'     => 1,
            'mobile' => '13800138011',
        ]);
        $this->createData();
        $this->actingAs($user)
            ->ajaxGet('/api/application/mylist');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        self::assertEquals(0, $response['total']);
        self::assertEquals(0, count($response['applications']));
    }

    //=========================================
    //          getDetail
    //=========================================
    public function testSuccessfulGetApplicationApiDetail()
    {
        $this->createData();
        $this->ajaxGet('api/application/1/detail');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $application = $response['application'];
        self::assertNotEmpty($application);
        self::assertEquals(1, $application['id']);
        self::assertEquals(11, $application['creator']['id']);
    }

    public function testSuccessfulGetApplicationApiDetail_NoData()
    {
        $this->createData();
        $this->ajaxGet('api/application/2/detail');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $application = $response['application'];
        self::assertEmpty($application);
    }

    public function testSuccessfulGetBackstageApplicationDetail()
    {
        $this->createData();
        $this->ajaxGet('web/application/1/detail');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $application = $response['application'];
        self::assertNotEmpty($application);
        self::assertEquals(1, $application['id']);
        self::assertEquals(11, $application['user_id']);
        self::assertEquals('13800139001', $application['mobile']);
        self::assertEquals(11, $application['creator']['id']);
        self::assertEquals('13800138011', $application['creator']['mobile']);
    }

    public function testSuccessfulGetBackstageApplicationDetail_NoData()
    {
        $this->createData();
        $this->ajaxGet('web/application/12/detail');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $application = $response['application'];
        self::assertEmpty($application);
    }
    //=========================================
    //          changeStatus
    //=========================================
    public function testSuccessfulApprove()
    {
        $this->createData();
        $this->ajaxGet('web/application/1/approve');
        $this->seeJsonContains(['code' => 0]);
    }

    public function testSuccessfulApprove_NoData()
    {
        $this->ajaxGet('web/application/12/approve');
        $this->seeJsonContains(['code' => 10000]);
    }

    public function testSuccessfulFailure()
    {
        $this->createData();
        $this->ajaxGet('web/application/1/failure');
        $this->seeJsonContains(['code' => 0]);
    }

    public function testSuccessfulFailure_NoData()
    {
        $this->ajaxGet('web/application/12/failure');
        $this->seeJsonContains(['code' => 10000]);
    }

    public function testSuccessfulSuccess()
    {
        $this->createData();
        $this->ajaxGet('web/application/1/success');
        $this->seeJsonContains(['code' => 0]);
    }

    public function testSuccessfulSuccess_NoData()
    {
        $this->ajaxGet('web/application/12/success');
        $this->seeJsonContains(['code' => 10000]);
    }


    //=========================================
    //             Create
    //=========================================
    public function testSuccessfulCreateApplication()
    {
        $this->startSession();
        $user = factory(User::class)->make([
            'id'     => 1,
            'mobile' => '13800138000',
        ]);
        $this->actingAs($user)
            ->ajaxPost('/web/application/create', [
                'req_id'     => 12,
                'contacts'   => '李二狗',
                'mobile'     => '13800138001',
                'intro'      => '装修水泥标号10',
                '_token'     => csrf_token(),
            ]);

        $this->seeJsonContains(['code' => 0]);
        $this->seeInDatabase('applications', [
            'req_id'     => 12,
            'contacts'   => '李二狗',
            'mobile'     => '13800138001',
            'intro'      => '装修水泥标号10',
        ]);
    }

    private function createData()
    {
        factory(User::class)->create([
            'id'     => 11,
            'mobile' => '13800138011',
        ]);
        factory(User::class)->create([
            'id'     => 12,
            'mobile' => '13800138012',
        ]);
        factory(User::class)->create([
            'id'     => 13,
            'mobile' => '13800138013',
        ]);
        factory(User::class)->create([
            'id'     => 14,
            'mobile' => '13800138014',
        ]);

        factory(Requirement::class)->create([
            'id'         => 1,
            'user_id'    => 11,
            'title'      => '水泥10吨',
            'contacts'   => '李二狗',
            'mobile'     => '13800138001',
            'intro'      => '装修水泥标号10',
            'begin_time' => date('Y-m-d', strtotime('+1 day')),
            'end_time'   => date('Y-m-d', strtotime('+30 day')),
            'status'     => RequirementRepository::APPROVE_STATUS
        ]);

        factory(Application::class)->create([
            'id'         => 1,
            'user_id'    => 11,
            'req_id'     => 1,
            'contacts'   => '王铁蛋',
            'mobile'     => '13800139001',
            'intro'      => '没有问题',
            'status'     => ApplicationRepository::APPROVE_STATUS
        ]);

        factory(Application::class)->create([
            'user_id'    => 12,
            'req_id'     => 1,
            'contacts'   => '赵二丫',
            'mobile'     => '13800139009',
            'intro'      => '没有问题',
            'status'     => ApplicationRepository::APPROVE_STATUS
        ]);

        factory(Application::class)->create([
            'user_id'    => 13,
            'req_id'     => 1,
            'contacts'   => '李铁锤',
            'mobile'     => '13800139010',
            'intro'      => '没有问题',
            'status'     => ApplicationRepository::PENDING_STATUS
        ]);

        factory(Application::class)->create([
            'user_id'    => 14,
            'req_id'     => 1,
            'contacts'   => '张狗剩',
            'mobile'     => '13800139011',
            'intro'      => '没有问题',
            'status'     => ApplicationRepository::PENDING_STATUS
        ]);

    }
}
