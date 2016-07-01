<?php
    namespace AppBundle\Utils;
    use FFMpeg;

    class VideoUtils
    {
        const JPEG_EXT = '.jpeg';
        private $ffprobe;
        private $ffmpeg;

        public function __construct()
        {
            $this->ffmpeg = FFMpeg\FFMpeg::create();
            $this->ffprobe = FFMpeg\FFProbe::create();
        }

        public function createFrames($framesCount, $videoFullPath, $framesRootPartPath, $videoDuration = 0)
        {
            if ($videoDuration == 0)
            {
                $videoDuration = $this->getVideoDuration($videoFullPath);
            }

            $video = $this->ffmpeg->open($videoFullPath);
            $cutFrameInterval = $videoDuration / $framesCount;
            for ($i = 0; $i < $framesCount; $i++)
            {
                $frameSavePath = $framesRootPartPath . $i . self::JPEG_EXT;
                $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($i * $cutFrameInterval))
                    ->save($frameSavePath);
            }
        }

        public function resizeVideo($fullOldPath, $fullNewPath, $newWidth, $newHeight)
        {
            $video = $this->ffmpeg->open($fullOldPath);
            $video->filters()
                ->resize(new FFMpeg\Coordinate\Dimension($newWidth, $newHeight))
                ->synchronize();
            $format = new FFMpeg\Format\Video\X264();
            $format->setAudioCodec('libmp3lame');
            $video->save($format, $fullNewPath);
        }

        public function cutVideo($fullOldPath, $fullNewPath, $timeFrom, $timeTo)
        {
            $command = "ffmpeg -y -i " . "\"" . $fullOldPath . "\"" . " -ss " . $timeFrom . " -t " . ($timeTo - $timeFrom) . " -c copy " . "\"" . $fullNewPath . "\"";
            $output = '';
            $executeStatus = '';
            exec($command, $output, $executeStatus);
            if ($executeStatus) {
                return false;
            }
            return true;
        }

        public function getVideoDuration($videoPath)
        {
            return $this->ffprobe->format($videoPath)->get('duration');
        }

        /**
         * @param string $videoPath
         * @return int
         */
        public function getVideoHeight($videoPath)
        {
            return $this->getDimensionFromVideo($videoPath)
                ->getHeight();
        }

        /**
         * @param string $videoPath
         * @return int
         */
        public function getVideoWidth($videoPath)
        {
            return $this->getDimensionFromVideo($videoPath)
                ->getWidth();
        }

        /**
         * @param string $videoPath
         * @return FFMpeg\Coordinate\Dimension
         */
        private function getDimensionFromVideo($videoPath)
        {
            return $this->ffprobe->streams($videoPath)
                ->videos()
                ->first()
                ->getDimensions();
        }

    }