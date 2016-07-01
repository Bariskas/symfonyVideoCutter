<?php
    namespace AppBundle\Classes;

    use AppBundle\Consts\Consts;

    class FilePathGenerator
    {
        public static function createVideoDirPath($videoID)
        {
            return Consts::ROOT_VIDEO_DIR . DIRECTORY_SEPARATOR . $videoID . DIRECTORY_SEPARATOR;
        }

        public static function createFramesRootPartPath($videoID)
        {
            return Consts::ROOT_VIDEO_DIR . DIRECTORY_SEPARATOR . $videoID . DIRECTORY_SEPARATOR . Consts::FRAME_NAME_PART;
        }

        public static function createRefVideoPath($videoID, $videoName)
        {
            return Consts::ROOT_VIDEO_DIR . DIRECTORY_SEPARATOR . $videoID . DIRECTORY_SEPARATOR . $videoName;
        }

        public static function createFullVideoPath($videoID, $videoName, $prefix = '')
        {
            if ($prefix)
            {
                $videoName = $prefix . $videoName;
            }
            return $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . FilePathGenerator::createRefVideoPath($videoID, $videoName);
        }
    }