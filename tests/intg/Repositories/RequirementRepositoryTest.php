<?php
namespace intg\Entrepreneur\Repositories;

use Entrepreneur\Models\User;
use Entrepreneur\Repositories\RequirementRepository;
use intg\Entrepreneur\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Entrepreneur\Models\Requirement;

class RequirementRepositoryTest extends TestCase
{
    use DatabaseTransactions;


    //=========================================
    //            findById
    //=========================================
    public function testFindById()
    {
        $this->createData();
        $requirement = $this->getRepository()->findById(1);
        self::assertEquals(1, $requirement->id);
        self::assertEquals('13800138001', $requirement->mobile);
    }

    //=========================================
    //            findByUser
    //=========================================
    public function testFindByUser()
    {
        $this->createData();
        list($count, $requirements) = $this->getRepository()->findByUser(11, 1, 1);
        self::assertEquals(2, $count);
        self::assertEquals(1, $requirements[0]->id);
        self::assertEquals('13800138001', $requirements[0]->mobile);
    }

    //=========================================
    //            find
    //=========================================
    public function testFind()
    {
        $this->createData();
        list($count, $requirements) = $this->getRepository()->find(1, 1, [RequirementRepository::APPROVE_STATUS], null);
        self::assertEquals(2, $count);
        self::assertEquals(1, $requirements[0]->id);
        self::assertEquals('13800138001', $requirements[0]->mobile);

    }

    //=========================================
    //            multipleAdd
    //=========================================
    public function testMultipleAdd()
    {
        $requirements = [];
        for ($i = 0; $i < 10; $i++) {
            $mobile = '1380013800' . $i;
            $requirement = factory(Requirement::class)->make([
                'mobile'     => $mobile,
                'user_id'    => 11,
                'title'      => '水泥10吨-' . $i,
                'contacts'   => '李二狗-' . $i,
                'intro'      => '装修水泥标号10-' . $i,
                'begin_time' => date('Y-m-d', strtotime('+1 day')),
                'end_time'   => date('Y-m-d', strtotime('+30 day')),
                'status'     => RequirementRepository::APPROVE_STATUS,
            ])->toArrayBackstage();
            unset($requirement['id']);
            unset($requirement['created_at']);
            unset($requirement['creator']);

            $requirements[] = $requirement;
        }
        self::assertGreaterThanOrEqual(true, $this->getRepository()->multipleAdd($requirements));

    }

    //=========================================
    //            add
    //=========================================
    public function testAdd()
    {
        $requirement = factory(Requirement::class)->make([
            'mobile'     => '13800139000',
            'user_id'    => 11,
            'title'      => '水泥10吨-',
            'contacts'   => '李二狗-',
            'intro'      => '装修水泥标号10-',
            'begin_time' => date('Y-m-d', strtotime('+1 day')),
            'end_time'   => date('Y-m-d', strtotime('+30 day')),
            'status'     => RequirementRepository::APPROVE_STATUS,
        ])->toArrayBackstage();
        unset($requirement['id']);
        unset($requirement['created_at']);
        unset($requirement['creator']);

        self::assertGreaterThanOrEqual(1, $this->getRepository()->add($requirement));

    }

    //=========================================
    //            search
    //=========================================
    public function testSearch()
    {
        $this->createData();
        list($count, $requirements) = $this->getRepository()->search('水泥', [RequirementRepository::APPROVE_STATUS], 1, 1);
        self::assertEquals(2, $count);
        self::assertEquals(1, $requirements[0]->id);
        self::assertEquals('13800138001', $requirements[0]->mobile);
    }

    //=========================================
    //            update
    //=========================================
    public function testUpdate()
    {
        $this->createData();
        $rst = $this->getRepository()->update([
            'id' => 1,
            'status' => RequirementRepository::CLOSE_STATUS,
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

        factory(Requirement::class)->create([
            'user_id'    => 11,
            'title'      => '水泥20吨',
            'contacts'   => '李二狗',
            'mobile'     => '13800138001',
            'intro'      => '装修水泥标号10',
            'begin_time' => date('Y-m-d', strtotime('+31 day')),
            'end_time'   => date('Y-m-d', strtotime('+60 day')),
            'status'     => RequirementRepository::APPROVE_STATUS,
        ]);
    }

    /**
     * @return \Entrepreneur\Contracts\Repositories\RequirementRepository
     */
    private function getRepository()
    {
        return $this->app[ \Entrepreneur\Contracts\Repositories\RequirementRepository::class ];
    }
}
