<?php
require_once 'PHPUnit/Framework.php';
require_once '../lib/PWS_Filesystem.class.php';
class PWS_FilesystemTest extends PHPUnit_Framework_TestCase
{
    private 
        $filename = 'PWS_Dir_Test';
    public function setUp ()
    {
        $this->fileSystem = new PWS_Filesystem();

    }
    /**
     * tests that system copy file from existed source to unexisted target
     */
    /*public function testCopyUnexistedSource ()
    {
        $fp = fopen($this->filename, 'w');
        fclose($fp);
        $this->fileSystem->copy($this->filename, $this->filename.'1');
        $this->assertTrue(file_exists($this->filename.'1'));
    }*/
    public function testDeleteDir ()
    {
        $dir = 'PWS_test';
        mkdir($dir);
        fopen($dir.DIRECTORY_SEPARATOR.'test', 'w');
        mkdir($dir.DIRECTORY_SEPARATOR.'subdir');
        $this->fileSystem->deleteDir($dir);
        $this->assertFalse(file_exists($dir));
        
    }
    
    
}
