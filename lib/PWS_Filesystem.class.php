<?php
class PWS_Filesystem
{
    public function copy ($sourceFile, $targetFile)
    {
            copy($sourceFile, $targetFile);
    }
    public function deleteDir ($dir)
    {
        foreach (glob($dir) as $file) {
            if (is_dir($file)) {
                $this->deleteDir("$file/*");
                rmdir($file);
            } else {
                unlink($file);
            }
        }

    }
    
}
