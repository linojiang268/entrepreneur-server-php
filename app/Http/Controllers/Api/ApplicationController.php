<?php
namespace Entrepreneur\Http\Controllers\Api;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Entrepreneur\Http\Controllers\Controller;
use Entrepreneur\ApplicationServices\ApplicationServices;
use Entrepreneur\Repositories\ApplicationRepository;

class ApplicationController extends Controller
{
    /*
     * get pending applications
     */
    public function pendingApplicationsList(Request $request, ApplicationServices $applicationServices)
    {
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($count, $applications) = $applicationServices->getList([
                ApplicationRepository::PENDING_STATUS,
                ApplicationRepository::APPROVE_STATUS,
                ApplicationRepository::SUCCESS_STATUS,
                ApplicationRepository::FAILURE_STATUS,
            ], $page, $size);

            return $this->json([
                'total'        => intval(ceil($count / $size)),
                'applications' => $applications,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /*
     * get login user applications
     */
    public function myApplicationsList(Request $request, Guard $auth, ApplicationServices $applicationServices)
    {
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($count, $applications) = $applicationServices->getMy($auth->user()->getAuthIdentifier(), $page, $size);

            return $this->json([
                'total'        => $count,
                'applications' => $applications,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /*
     * get one application
     */
    public function getApplicationApiDetail($id, ApplicationServices $applicationServices)
    {
        return $this->getApplicationDetail($id, 'getApiOne', $applicationServices);
    }

    /*
    * backstage get one application
    */
    public function getApplicationBackstageDetail($id, ApplicationServices $applicationServices)
    {
        return $this->getApplicationDetail($id, 'getBackstageOne', $applicationServices);
    }

    private function getApplicationDetail($id, $function, ApplicationServices $applicationServices)
    {
        try {
            $application = $applicationServices->$function(intval($id));

            return $this->json([
                'application' => $application,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    public function createApplication(Request $request, Guard $auth, ApplicationServices $applicationServices)
    {

        $this->validate($request, [
            'req_id'   => 'required|integer',
            'contacts' => 'required|between:2,10',
            'mobile'   => 'required|mobile',
            'intro'    => 'required|between:2,256',
        ], [
            'req_id.required'   => '标题未填写',
            'req_id.between'    => '标题错误',
            'contacts.required' => '联系人未填写',
            'contacts.between'  => '联系人错误',
            'mobile.required'   => '手机号未填写',
            'mobile.between'    => '手机号格式错误',
            'intro.required'    => '需求详情未填写',
            'intro.between'     => '需求详情错误',
        ]);

        $requirement = [
            'user_id'  => $auth->user()->getAuthIdentifier(),
            'req_id'   => $request->get('req_id'),
            'contacts' => $request->get('contacts'),
            'mobile'   => $request->get('mobile'),
            'intro'    => $request->get('intro'),
            'status'   => ApplicationRepository::PENDING_STATUS,
        ];
        try {
            $id = $applicationServices->add($requirement);

            return $this->json([
                'id' => $id,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    public function delete($id, ApplicationServices $applicationServices)
    {
        try {
            $ret = $applicationServices->delete(intval($id));

            return $this->json(['result' => $ret]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    public function approve($id, ApplicationServices $applicationServices)
    {
        return $this->changeStatus($id, ApplicationRepository::APPROVE_STATUS, $applicationServices);
    }

    public function failure($id, ApplicationServices $applicationServices)
    {
        return $this->changeStatus($id, ApplicationRepository::FAILURE_STATUS, $applicationServices);
    }

    public function success($id, ApplicationServices $applicationServices)
    {
        return $this->changeStatus($id, ApplicationRepository::SUCCESS_STATUS, $applicationServices);
    }

    private function changeStatus($id, $status, ApplicationServices $applicationServices)
    {
        //Todo:: add customer service check
        try {
            $ret = $applicationServices->changeStatus(intval($id), $status);

            return $this->json(['result' => $ret]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /*
* backstage get approved requirements
*/
    public function applicationBackstageAuditingList(Request $request, ApplicationServices $applicationServices)
    {
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($count, $applications) = $applicationServices->getList([ApplicationRepository::PENDING_STATUS], $page, $size);

            return $this->json([
                'total'        => intval(ceil($count / $size)),
                'applications' => $applications,
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    public function applicationBackstageListView()
    {
        return view('welcome.applicationlist');
    }
}
