<?php
include_once dirname(__FILE__).'/../../lib/annotations/Message.class.php';
class Controller
{
    /**
     */
    public function Hello ($request)
    {
        return 'hello, '.$request;
    }

}
