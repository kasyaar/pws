<?php
include_once dirname(__FILE__).'/../../lib/annotations/Request.class.php';
include_once dirname(__FILE__).'/../../lib/annotations/Response.class.php';
class Controller
{
    /**
     * @Request({username='string'})
     * @Response({greeting='string'})
     */
    public function Hello ($request)
    {
        return 'hello, '.$request;
    }

}
