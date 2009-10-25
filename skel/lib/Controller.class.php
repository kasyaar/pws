<?php
include_once dirname(__FILE__).'/../../lib/annotations/Request.class.php';
include_once dirname(__FILE__).'/../../lib/annotations/Response.class.php';
class Controller
{
    /**
     * @Request({user={type='User'}})
     * @Response({greeting={type='string'}})
     */
    public function Hello ($request)
    {
        $response = new StdClass;
        $response->greeting = 'hello, '.$request->user->name;
        return $response;
    }
}
