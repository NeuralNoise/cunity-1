<?php

namespace Gallery\Models;

use \Core\Cunity;
use Skoch_Filter_File_Crop;
use Skoch_Filter_File_Resize;

/**
 * Class Uploader
 * @package Gallery\Models
 */
class Uploader {

    /**
     *
     */
    public function __construct() {
        
    }

    /**
     * @param $filename
     * @return string
     * @throws \Exception
     */
    public function upload($filename) {

        if (empty($_FILES) || $_FILES['file']['error']) {
            die('{"OK": 0, "info": "Failed to move uploaded file."}');
        }

        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
        $filePath = "./$fileName";


// Open temp file
        $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
        if ($out) {
            // Read binary input stream and append it to temp file
            $in = fopen($_FILES['file']['tmp_name'], "rb");

            if ($in) {
                /** @noinspection PhpAssignmentInConditionInspection */
                while ($buff = fread($in, 4096))
                    fwrite($out, $buff);
            } else
                die('{"OK": 0, "info": "Failed to open input stream."}');

            fclose($in);
            fclose($out);

            unlink($_FILES['file']['tmp_name']);
        } else
            die('{"OK": 0, "info": "Failed to open output stream."}');

        if ($chunks == 0 || $chunk == $chunks - 1) {

            $settings = Cunity::get("settings");
            $config = Cunity::get("config");
            $fileinfo = pathinfo($fileName);
            $destinationFile = "../data/uploads/" . $settings->getSetting("core.filesdir") . "/" . $filename . "." . strtolower($fileinfo['extension']);
            $previewFile = "../data/uploads/" . $settings->getSetting("core.filesdir") . "/prev_" . $filename . "." . strtolower($fileinfo['extension']);
            
            rename("{$filePath}.part", $destinationFile);
            copy($destinationFile,$previewFile);

            $resizer = new Skoch_Filter_File_Resize($config->images);
            $preview = new Skoch_Filter_File_Resize($config->previewImages);
            $crop = new Skoch_Filter_File_Crop([
                "thumbwidth" => "thumbnail",
                "directory" => "../data/uploads/" . Cunity::get("settings")->getSetting("core.filesdir"),
                "prefix" => "thumb_"
            ]);            
            $resizer->filter($destinationFile);
            $preview->filter($previewFile);
            $crop->filter($destinationFile);            
            return $filename . "." . strtolower($fileinfo['extension']);
        } else
            exit();
    }

}
