<?php
    namespace AppBundle\Utils;
    use FFMpeg;
    use AppBundle\Entity\UserVideoEntity;

    class VideoUtilit
    {
        const WIDTH = 320;
        const HEIGHT = 240;
        const VIDEO_DIR = 'uploads/videos/';
        const FRAMES_DIR = 'frames/';

        public $file;
        private $fileName;
        private $videoPath = '';
        private $duration = 0;

        /**
         * @var UserVideoEntity
         */
        private $videoEntity;

        public function __construct(UserVideoEntity $videoEntity)
        {
            $this->videoEntity = $videoEntity;
        }

        public function getFrame($time = 0)
        {
            $framePath = self::FRAMES_DIR.$this->fileName . '.jpeg';
            $time = ($time === 0) ? round($this->duration / 2) : $time;
            $time = 2;
            $ffmpeg = FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open($this->videoPath);
            $video
                ->filters()
                ->resize(new FFMpeg\Coordinate\Dimension(self::WIDTH, self::HEIGHT))
                ->synchronize();

            //$video = $this->ffmpeg->open($this->videoPath);
            $video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($time))
                ->save($framePath);
            return $framePath;
        }

        public function saveVideo()
        {
            $dir = self::VIDEO_DIR . $this->videoEntity->getId() . "/";
            $this->videoEntity->getVideo()->move($dir, $this->videoEntity->getName());
        }

        public function createFrame($time = 0)
        {
            $framePath =  self::VIDEO_DIR . $this->videoEntity->getId() . "/" . (($time === 0) ? 'frame' : $time) . '.jpeg';
            $time = ($time === 0) ? round($this->videoEntity->getDuration() / 2) : $time;
            $ffmpeg = FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open(self::VIDEO_DIR . $this->videoEntity->getId() . "/" . $this->videoEntity->getName());
            $video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($time))
                ->save($framePath);
            return $framePath;
        }

        public function calculateDuration()
        {
            $ffprobe = FFMpeg\FFProbe::create();
            return $ffprobe->format($this->videoEntity->getVideo()->getRealPath())->get('duration');
        }
    }