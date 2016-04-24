<?php
namespace intg\Entrepreneur\Repositories;

use Entrepreneur\Models\User;
use Entrepreneur\Repositories\ApplicationRepository;
use Entrepreneur\Repositories\RequirementRepository;
use intg\Entrepreneur\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Entrepreneur\Models\Requirement;
use Entrepreneur\Models\Application;

class ApplicationRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    //=========================================
    //            findById
    //=========================================
    public function testFindById()
    {
        $this->createData();
        $application = $this->getRepository()->findById(1);
        self::assertEquals(1, $application->id);
        self::assertEquals('13800139001', $application->mobile);
    }

    //=========================================
    //            findByUser
    //=========================================
    public function testFindByUser()
    {
        $this->createData();
        list($count, $applications) = $this->getRepository()->findByUser(16, 1, 1);
        self::assertEquals(1, $count);
        self::assertEquals(1, $applications[0]->id);
        self::assertEquals('13800139001', $applications[0]->mobile);
    }

    //=========================================
    //            findByRequirement
    //=========================================
    public function testFindByRequirement()
    {
        $this->createData();
        list($count, $applications) = $this->getRepository()->findByRequirement(1, 1, 1);
        self::assertEquals(2, $count);
        self::assertEquals(2, $applications[0]->id);
        self::assertEquals('13800139009', $applications[0]->mobile);
    }

    //=========================================
    //            find
    //=========================================
    public function testFind()
    {
        $this->createData();
        list($count, $applications) = $this->getRepository()->find(1, 1, [ApplicationRepository::APPROVE_STATUS]);
        self::assertEquals(2, $count);
        self::assertEquals(1, $applications[0]->id);
        self::assertEquals('13800139001', $applications[0]->mobile);

    }

    //=========================================
    //            multipleAdd
    //=========================================
    public function testMultipleAdd()
    {
        $applications = [];
        for ($i = 0; $i < 10; $i++) {
            $mobile = '1380013800' . $i;
            $application = factory(Application::class)->make([
                'mobile'   => $mobile,
                'user_id'  => $i + 1,
                'req_id'   => 11,
                'contacts' => '李二狗-' . $i,
                'intro'    => '装修水泥标号10-' . $i,
                'status'   => ApplicationRepository::APPROVE_STATUS,
            ])->toArray();
            unset($application['id']);
            unset($application['created_at']);
            unset($application['creator']);
            unset($application['requirement']);
            $applications[] = $application;
        }
        self::assertGreaterThanOrEqual(true, $this->getRepository()->multipleAdd($applications));

    }

    //=========================================
    //            add
    //=========================================
    public function testAdd()
    {
        $application = factory(Application::class)->make([
            'mobile'   => '13800139000',
            'user_id'  => 11,
            'contacts' => '李二狗-',
            'intro'    => '装修水泥标号10-',
            'status'   => ApplicationRepository::APPROVE_STATUS,
        ])->toArray();
        unset($application['id']);
        unset($application['created_at']);
        unset($application['creator']);
        unset($application['requirement']);
        self::assertGreaterThanOrEqual(1, $this->getRepository()->add($application));
    }

    //=========================================
    //            search
    //=========================================
    public function testSearch()
    {
        $this->createData();
        list($count, $applications) = $this->getRepository()->search('二丫', [RequirementRepository::APPROVE_STATUS], 1, 1);
        self::assertEquals(1, $count);
        self::assertEquals(2, $applications[0]->id);
        self::assertEquals('13800139009', $applications[0]->mobile);
    }

    //=========================================
    //            update
    //=========================================
    public function testUpdate()
    {
        $this->createData();
        $rst = $this->getRepository()->update([
            'id'     => 1,
            'status' => ApplicationRepository::SUCCESS_STATUS,
        ]);
        self::assertTrue($rst);
    }

    //=========================================
    //            delete
    //=========================================
    public function testDelete()
    {
        $this->createData();
        $rst = $this->getRepository()->delete(1);
        self::assertTrue($rst);
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
            'status'     => RequirementRepository::APPROVE_STATUS,
        ]);

        factory(Application::class)->create([
            'id'       => 1,
            'user_id'  => 16,
            'req_id'   => 1,
            'contacts' => '王铁蛋',
            'mobile'   => '13800139001',
            'intro'    => '没有问题',
            'status'   => ApplicationRepository::APPROVE_STATUS,
        ]);

        factory(Application::class)->create([
            'id'       => 2,
            'user_id'  => 13,
            'req_id'   => 1,
            'contacts' => '赵二丫',
            'mobile'   => '13800139009',
            'intro'    => '没有问题',
            'status'   => ApplicationRepository::APPROVE_STATUS,
        ]);

    }

    /**
     * @return \Entrepreneur\Contracts\Repositories\ApplicationRepository
     */
    private function getRepository()
    {
        return $this->app[ \Entrepreneur\Contracts\Repositories\ApplicationRepository::class ];
    }
}
