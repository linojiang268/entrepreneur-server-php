<?php
namespace intg\Entrepreneur\Controllers\Api;

use Entrepreneur\Models\Requirement;
use Entrepreneur\Repositories\RequirementRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use intg\Entrepreneur\TestCase;
use Entrepreneur\Models\User;

class RequirementControllerTest extends TestCase
{
    use DatabaseTransactions;

    //=========================================
    //             Create
    //=========================================
    public function testSuccessfulCreateRequirement()
    {
        $this->startSession();
        $user = factory(User::class)->make([
            'id'     => 1,
            'mobile' => '13800138000',
        ]);
        $this->actingAs($user)
            ->ajaxPost('/api/requirement/create', [
                'title'      => '水泥30吨',
                'contacts'   => '李二狗',
                'mobile'     => '13800138001',
                'intro'      => '装修水泥标号10',
                'begin_time' => date('Y-m-d', strtotime('+1 day')),
                'end_time'   => date('Y-m-d', strtotime('+30 day')),
                '_token'     => csrf_token(),
            ]);

        $this->seeJsonContains(['code' => 0]);
        $this->seeInDatabase('requirements', [
            'title'      => '水泥30吨',
            'contacts'   => '李二狗',
            'mobile'     => '13800138001',
            'intro'      => '装修水泥标号10',
            'begin_time' => date('Y-m-d', strtotime('+1 day')),
            'end_time'   => date('Y-m-d', strtotime('+30 day')),
        ]);
    }

    //=========================================
    //             list
    //=========================================
    public function testSuccessfulRequirementList()
    {
        $this->createData();
        $this->ajaxGet('/api/requirement/list');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        self::assertEquals(2, $response['total']);
        self::assertEquals(2, count($response['requirements']));
    }

    public function testSuccessfulRequirementList_NoData()
    {
        $this->ajaxGet('/api/requirement/list');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        self::assertEquals(0, $response['total']);
        self::assertEquals(0, count($response['requirements']));
    }

    public function testSuccessfulRequirementBackstageList()
    {
        $this->createData();
        $this->ajaxGet('/web/requirement/list');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        self::assertEquals(1, $response['total']);
        self::assertEquals(2, count($response['requirements']));
        self::assertEquals('13800138001', $response['requirements'][0]['mobile']);
        self::assertEquals('13800138011', $response['requirements'][0]['creator']['mobile']);
    }

    public function testSuccessfulRequirementBackstageList_NoData()
    {
        $this->ajaxGet('/web/requirement/list');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        self::assertEquals(0, $response['total']);
        self::assertEquals(0, count($response['requirements']));
    }

    //=========================================
    //             MyList
    //=========================================
    public function testSuccessfulMyRequirementList()
    {
        $this->startSession();
        $user = factory(User::class)->make([
            'id'     => 11,
            'mobile' => '13800138011',
        ]);
        $this->createData();
        $this->actingAs($user)
            ->ajaxGet('/api/requirement/mylist');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        self::assertEquals(2, $response['total']);
        self::assertEquals(2, count($response['requirements']));
    }

    public function testSuccessfulMyRequirementList_NoData()
    {
        $this->startSession();
        $user = factory(User::class)->make([
            'id'     => 1,
            'mobile' => '13800138011',
        ]);
        $this->createData();
        $this->actingAs($user)
            ->ajaxGet('/api/requirement/mylist');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        self::assertEquals(0, $response['total']);
        self::assertEquals(0, count($response['requirements']));
    }

    //=========================================
    //          getRequirementDetail
    //=========================================
    public function testSuccessfulGetRequirementDetail()
    {
        $this->createData();
        $this->ajaxGet('api/requirement/1/detail');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $requirement = $response['requirement'];
        self::assertNotEmpty($requirement);
        self::assertEquals(1, $requirement['id']);
        self::assertEquals(11, $requirement['creator']['id']);
    }

    public function testSuccessfulGetRequirementDetail_NoData()
    {
        $this->createData();
        $this->ajaxGet('api/requirement/12/detail');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $requirement = $response['requirement'];
        self::assertEmpty($requirement);
    }

    public function testSuccessfulGetBackstageRequirementDetail()
    {
        $this->createData();
        $this->ajaxGet('web/requirement/1/detail');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $requirement = $response['requirement'];
        self::assertNotEmpty($requirement);
        self::assertEquals(1, $requirement['id']);
        self::assertEquals(11, $requirement['user_id']);
        self::assertEquals('13800138001', $requirement['mobile']);
        self::assertEquals(11, $requirement['creator']['id']);
        self::assertEquals('13800138011', $requirement['creator']['mobile']);
    }

    public function testSuccessfulGetBackstageRequirementDetail_NoData()
    {
        $this->createData();
        $this->ajaxGet('web/requirement/12/detail');
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $requirement = $response['requirement'];
        self::assertEmpty($requirement);
    }

    //=========================================
    //          searchRequirement
    //=========================================
    public function testSuccessfulSearchApiRequirement()
    {
        $this->createData();
        $this->ajaxPost('/api/requirement/search', [
                'keyword'      => '水泥',
                '_token'     => csrf_token(),
            ]);
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $requirements = $response['requirements'];
        self::assertNotEmpty($requirements);
        self::assertEquals(2, $response['total']);
        self::assertEquals(2, count($requirements));
    }

    public function testSuccessfulSearchApiRequirement_Mobile()
    {
        $this->createData();
        $this->ajaxPost('/api/requirement/search', [
            'keyword'      => '13800138001',
            '_token'     => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $requirements = $response['requirements'];
        self::assertNotEmpty($requirements);
        self::assertEquals(2, $response['total']);
        self::assertEquals(2, count($requirements));
    }

    public function testSuccessfulSearchApiRequirement_NoData()
    {
        $this->createData();
        $this->ajaxPost('/api/requirement/search', [
            'keyword'      => '的时代',
            '_token'     => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $requirements = $response['requirements'];
        self::assertEmpty($requirements);
        self::assertEquals(0, $response['total']);
    }

    public function testSuccessfulSearchBackstageRequirement()
    {
        $this->createData();
        $this->ajaxPost('/web/requirement/search', [
            'keyword'      => '水泥',
            '_token'     => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $requirements = $response['requirements'];
        self::assertNotEmpty($requirements);
        self::assertEquals(2, $response['total']);
        self::assertEquals(2, count($requirements));
        self::assertEquals('13800138001', $requirements[0]['mobile']);
        self::assertEquals('13800138011', $requirements[0]['creator']['mobile']);
    }

    public function testSuccessfulSearchBackStageRequirement_Mobile()
    {
        $this->createData();
        $this->ajaxPost('/web/requirement/search', [
            'keyword'      => '13800138001',
            '_token'     => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->getContent(), 1);
        $requirements = $response['requirements'];
        self::assertNotEmpty($requirements);
        self::assertEquals(2, $response['total']);
        self::assertEquals(2, count($requirements));
        self::assertEquals('13800138001', $requirements[0]['mobile']);
        self::assertEquals('13800138011', $requirements[0]['creator']['mobile']);
    }

    //=========================================
    //          changeStatus
    //=========================================
    public function testSuccessfulApprove()
    {
        $this->createData();
        $this->ajaxGet('web/requirement/1/approve');
        $this->seeJsonContains(['code' => 0]);
    }

    public function testSuccessfulApprove_NoData()
    {
        $this->ajaxGet('web/requirement/12/approve');
        $this->seeJsonContains(['code' => 10000]);
    }

    public function testSuccessfulStop()
    {
        $this->createData();
        $this->ajaxGet('web/requirement/1/stop');
        $this->seeJsonContains(['code' => 0]);
    }

    public function testSuccessfulStop_NoData()
    {
        $this->ajaxGet('web/requirement/12/stop');
        $this->seeJsonContains(['code' => 10000]);
    }

    public function testSuccessfulClose()
    {
        $this->createData();
        $this->ajaxGet('web/requirement/1/close');
        $this->seeJsonContains(['code' => 0]);
    }

    public function testSuccessfulClose_NoData()
    {
        $this->ajaxGet('web/requirement/12/close');
        $this->seeJsonContains(['code' => 10000]);
    }

    private function createData()
    {
        factory(User::class)->create([
            'id'     => 11,
            'mobile' => '13800138011',
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

        factory(Requirement::class)->create([
            'user_id'    => 11,
            'title'      => '水泥20吨',
            'contacts'   => '李二狗',
            'mobile'     => '13800138001',
            'intro'      => '装修水泥标号10',
            'begin_time' => date('Y-m-d', strtotime('+31 day')),
            'end_time'   => date('Y-m-d', strtotime('+60 day')),
            'status'     => RequirementRepository::APPROVE_STATUS
        ]);
    }
}
