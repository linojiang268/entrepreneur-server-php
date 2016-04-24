<?php
namespace Entrepreneur\Http\Controllers\Api;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Entrepreneur\Http\Controllers\Controller;
use Entrepreneur\ApplicationServices\RequirementServices;
use Entrepreneur\Repositories\RequirementRepository;
use Carbon\Carbon;

class RequirementController extends Controller
{
    /*
     * get approved requirements
     */
    public function requirementApiList(Request $request, RequirementServices $requirementServices)
    {
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($count, $requirements) = $requirementServices->getApiList(RequirementRepository::APPROVE_STATUS, $page, $size);

            return $this->json([
                'total'        => $count,
                'requirements' => $requirements,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /*
     * backstage get approved requirements
     */
    public function requirementBackstageList(Request $request, RequirementServices $requirementServices)
    {
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($count, $requirements) = $requirementServices->getBackstageList(null, $page, $size);

            return $this->json([
                'total'        => intval(ceil($count / $size)),
                'requirements' => $requirements,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /*
  * backstage get pending requirements
  */
    public function requirementBackstageAuditingList(Request $request, RequirementServices $requirementServices)
    {
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($count, $requirements) = $requirementServices->getBackstageList(RequirementRepository::PENDING_STATUS, $page, $size);

            return $this->json([
                'total'        => intval(ceil($count / $size)),
                'requirements' => $requirements,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /*
     * get login user requirements
     */
    public function myRequirementList(Request $request, Guard $auth, RequirementServices $requirementServices)
    {
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($count, $requirements) = $requirementServices->getMy($auth->user()->getAuthIdentifier(), $page, $size);

            return $this->json([
                'total'        => $count,
                'requirements' => $requirements,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /*
     * get one requirement
     */
    public function getApiRequirementDetail($id, RequirementServices $requirementServices)
    {
        try {
            $requirement = $requirementServices->getApiOne(intval($id));

            return $this->json([
                'requirement' => $requirement,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }


    /*
     * backstage get one requirement
     */
    public function getBackstageRequirementDetail($id, RequirementServices $requirementServices)
    {
        try {
            $requirement = $requirementServices->getbackstageOne(intval($id));

            return $this->json([
                'requirement' => $requirement,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    public function searchBackstageRequirement(Request $request, RequirementServices $requirementServices)
    {
        $function = 'requirementBackstageSearch';

        return $this->search($request, $requirementServices, $function);
    }

    public function searchApiRequirement(Request $request, RequirementServices $requirementServices)
    {
        $function = 'requirementApiSearch';

        return $this->search($request, $requirementServices, $function);
    }

    private function search($request, RequirementServices $requirementServices, $function)
    {
        $this->validate($request, [
            'keyword' => 'required|between:2,20',
        ], [
            'keyword.required' => '关键词未填写',
            'keyword.between'  => '关键词错误',
        ]);
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($count, $requirements) = $requirementServices->$function(
                $request->get('keyword'),
                [RequirementRepository::APPROVE_STATUS],
                $page,
                $size
            );

            return $this->json([
                'total'        => $count,
                'requirements' => $requirements,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }

    }

    public function approve($id, RequirementServices $requirementServices)
    {
        return $this->changeStatus($id, RequirementRepository::APPROVE_STATUS, $requirementServices);
    }

    public function stop($id, RequirementServices $requirementServices)
    {
        return $this->changeStatus($id, RequirementRepository::CLOSE_STATUS, $requirementServices);
    }

    public function close($id, RequirementServices $requirementServices)
    {
        return $this->changeStatus($id, RequirementRepository::STOP_STATUS, $requirementServices);
    }

    public function recovery($id, RequirementServices $requirementServices)
    {
        return $this->changeStatus($id, RequirementRepository::APPROVE_STATUS, $requirementServices);
    }

    private function changeStatus($id, $status, RequirementServices $requirementServices)
    {
        //Todo:: add customer service check
        try {
            $ret = $requirementServices->changeStatus(intval($id), $status);

            return $this->json(['result' => $ret]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    public function createRequirement(Request $request, Guard $auth, RequirementServices $requirementServices)
    {
        $this->validate($request, [
            'title'      => 'required|between:2,128',
            'contacts'   => 'required|between:2,10',
            'mobile'     => 'required|mobile',
            'intro'      => 'required|between:2,256',
            'begin_time' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
            'end_time'   => 'required|date_format:Y-m-d|after:begin_time',
        ], [
            'title.required'         => '标题未填写',
            'title.between'          => '标题错误',
            'contacts.required'      => '联系人未填写',
            'contacts.between'       => '联系人错误',
            'mobile.required'        => '手机号未填写',
            'mobile.between'         => '手机号格式错误',
            'intro.required'         => '需求详情未填写',
            'intro.between'          => '需求详情错误',
            'begin_time.required'    => '起始时间未填写',
            'begin_time.date_format' => '起始时间格式错误',
            'begin_time.after'       => '起始时间应大于昨天',
            'end_time.required'      => '截至时间未填写',
            'end_time.date_format'   => '截至时间格式错误',
            'end_time.after'         => '截至时间应大于起始时间',
        ]);

        $requirement = [
            'user_id'    => $auth->user()->getAuthIdentifier(),
            'title'      => $request->get('title'),
            'contacts'   => $request->get('contacts'),
            'mobile'     => $request->get('mobile'),
            'intro'      => $request->get('intro'),
            'begin_time' => $request->get('begin_time'),
            'end_time'   => $request->get('end_time'),
            'status'     => RequirementRepository::PENDING_STATUS,
        ];
        try {
            $id = $requirementServices->add($requirement);

            return $this->json([
                'id' => $id,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    public function delete($id, RequirementServices $requirementServices)
    {
        try {
            $ret = $requirementServices->delete(intval($id));

            return $this->json(['result' => $ret]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    public function requirementListPage()
    {
        return view('welcome.index');
    }

    public function requirementBackstageListView()
    {
        return view('welcome.requirementlist');
    }
}
