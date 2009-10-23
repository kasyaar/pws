<?php
include_once dirname(__FILE__).'/../../lib/annotations/Request.class.php';
include_once dirname(__FILE__).'/../../lib/annotations/Response.class.php';
class Controller
{
    /**
     * @Request({username={type='string'}})
     * @Response({greeting={type='string'}})
     */
    public function Hello ($request)
    {
        return 'hello, '.$request;
    }

}
