<?php
    namespace AppBundle\Utils;
    use FFMpeg;
    use AppBundle\Entity\UserVideoEntity;

    class VideoUtils
    {
        const FRAMES_COUNT = 5;
        const JPEG_EXT = '.jpeg';
        /**
         * @var UserVideoEntity
         */
        private $videoEntity;
        private $videoDir = "";

        public function __construct(UserVideoEntity $videoEntity = null, $videoDir)
        {
            $this->videoEntity = $videoEntity;
            $this->videoDir = $videoDir;
        }

        public function saveVideo()
        {
            $dir = $this->videoDir . $this->videoEntity->getId() . "/";
            $this->videoEntity->getVideo()->move($dir, $this->videoEntity->getName());
        }

        public function createFrame($time = 0)
        {
            $framePath =  $this->videoDir . $this->videoEntity->getId() . "/" . (($time === 0) ? 'frame' : $time) . self::JPEG_EXT;
            $time = ($time === 0) ? round($this->videoEntity->getDuration() / 2) : $time;
            $ffmpeg = FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open($this->videoDir . $this->videoEntity->getId() . "/" . $this->videoEntity->getName());
            $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($time))
                ->save($framePath);
            return $framePath;
        }

        public function createFrames()
        {
            $framesPath =  $this->videoDir . $this->videoEntity->getId() . "/frame";
            $duration = $this->videoEntity->getDuration();
            $ffmpeg = FFMpeg\FFMpeg::create();

            $video = $ffmpeg->open($this->videoDir . $this->videoEntity->getId() . "/" . $this->videoEntity->getName());
            $step = $duration / self::FRAMES_COUNT;
            for ($i = 0; $i < self::FRAMES_COUNT; $i++)
            {
                $path = $framesPath . $i . self::JPEG_EXT;
                $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($i*$step))
                    ->save($path);
            }
        }

        public function clip($from, $to)
        {
            $pathToVideo = $this->videoDir . $this->videoEntity->getId() . "/";
            $fullOldPath = $_SERVER["DOCUMENT_ROOT"] . '/' . $pathToVideo . $this->videoEntity->getName();
            $fullNewPath = $_SERVER["DOCUMENT_ROOT"] . '/' .$pathToVideo . 'clipped-' . $this->videoEntity->getName();

            $command = "ffmpeg -y -i " . "\"" . $fullOldPath . "\"" . " -ss " . $from . " -t " . ($to - $from) . " -c copy " . "\"" . $fullNewPath . "\"";
            $output = '';
            $status = '';
            exec($command, $output, $status);
        }

        public function calculateDuration()
        {
            $ffprobe = FFMpeg\FFProbe::create();
            return $ffprobe->format($this->videoEntity->getVideo()->getRealPath())->get('duration');
        }
    }