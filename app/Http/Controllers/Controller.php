<?php

namespace Entrepreneur\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Entrepreneur\Http\Responses\RespondsJson;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, RespondsJson;
    use ValidatesRequests {
        // build validation response on our own
        buildFailedValidationResponse as parentBuildFailedValidationResponse;
    }

    /**
     * override method defined in ValidatesRequests in order to make an
     * application specific validation error response
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        if ($request->ajax() || $request->wantsJson()) {
            // fetch first entry of the errors, which is derived from validation
            // , keyed by field name and valued by error messages (which is an array,
            // as a field can relate to multiple error messages)
            // we only need the first error message here
            $message = current(current($errors));

            return $this->jsonException($message);
        }

        return $this->parentBuildFailedValidationResponse($request, $errors);
    }

    /**
     * find and sane page and size parameters from request (for pagination)
     *
     * @param Request $request
     * @param array   $options configurable options, keys including:
     *                         - defaultSize    (optional) the default page size - if there's no page size or
     *                                          an invalid one is provided, this default value will be applied
     *                                          (default: 10)
     *                         - maxPageSize    (optional) the max acceptable page size (default to the value of
     *                                          the defaultSize option)
     *                         - pageParamName  (optional) request param name for page number/index (default: 'page')
     *                         - sizeParamName  (optional) request param name for size (default: 'size')
     *
     * @return array a two-element array, with the first element being the page number/index
     */
    protected function sanePageAndSize(Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'defaultSize'   => $this->getDefaultPageSize(),
            'maxPageSize'   => $this->getDefaultPageSize(),
            'pageParamName' => 'page',
            'sizeParamName' => 'size'
        ]);

        // find page and size from request and sane them
        $page = $this->sanePage($request->get($options['pageParamName']));

        // sane size
        $size = $request->get($options['sizeParamName'], $options['defaultSize']);
        if (!is_numeric($size)) {
            $size = $options['defaultSize'];
        } else {
            // page size should be with the range of (0, maxPageSize]
            if (!$size || $size <= 0 || $size > $options['maxPageSize']) {
                $size = min($options['defaultSize'], $options['maxPageSize']);
            }
        }

        return [$page, $size];
    }

    protected function sanePage($page) {
        // page number/index should start from 1 (not 0)
        if (!$page || $page <= 0 || !is_numeric($page)) {
            $page = 1;
        }

        return $page;
    }

    protected function getDefaultPageSize() {
        return 10;
    }
}
