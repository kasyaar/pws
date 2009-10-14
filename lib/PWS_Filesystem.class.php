<?php
class PWS_Filesystem
{
    public function copy ($sourceFile, $targetFile)
    {
            copy($sourceFile, $targetFile);
    }
    public function deleteDir ($dir)
    {
        $files = glob( $dir . '*', GLOB_MARK );
        foreach( $files as $file ){
            if(is_dir($file))
                $this->deleteDir( $file );
            else
                unlink( $file );
        }

        if (is_dir($dir)) 
            rmdir( $dir ); 

    }
    
}
