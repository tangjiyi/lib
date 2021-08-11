<?php

class DownloadClass {

    function getFile($file, $url){
      $fp = fopen ($file, 'w+');
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL,$url);
      curl_setopt($curl, CURLOPT_TIMEOUT, 1000);
      curl_setopt($curl, CURLOPT_FILE, $fp); // write curl response to file
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_NOPROGRESS, 0);
      curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, array($this, 'progressCallback'));

      $file_content = curl_exec($curl);
	  $info = curl_getinfo($curl);
      
      curl_close($curl);
      fclose($fp);
    }

    function progressCallback($resource, $download_size = 0, $downloaded = 0, $upload_size = 0, $uploaded = 0)
    {
        if (version_compare(PHP_VERSION, '5.5.0') < 0) {
            $uploaded = $upload_size;
            $upload_size = $downloaded;
            $downloaded = $download_size;
            $download_size = $resource;
        }
        static $previousProgress = 0;
        if ( $download_size == 0 ){
            $progress = 0;
        } else {
            $progress = round(($downloaded/$download_size)*100);
        }
        if ( $progress > $previousProgress)
        {
            $previousProgress = $progress;
            $fp = fopen( 'progress.txt', 'a' );
            fputs( $fp, "$progress\n" );
            fclose( $fp );
        }
    }

}

$obj = new DownloadClass();
$obj->getFile('f10003.mp4', 'http://mpvideo.qpic.cn/0b78reaakaaataakfaacxbqvbcodaweqabia.f10003.mp4?dis_k=52bfb75680a0dd1f6d21b1159ef82797&dis_t=1628586869');
