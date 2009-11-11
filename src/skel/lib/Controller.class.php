<?php
include_once '@PWS-LIBS@/lib/annotations/Request.class.php';
include_once '@PWS-LIBS@/lib/annotations/Response.class.php';
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
