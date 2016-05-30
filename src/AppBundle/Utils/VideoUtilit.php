<?php
    namespace AppBundle\Utils;
    use FFMpeg;

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

        public function __construct($file)
        {
            $this->file = $file;
            $this->initParameters();
        }

        public function getFileName()
        {
            return $this->fileName;
        }

        public function saveVideo($id)
        {
            $this->file->move(self::VIDEO_DIR . 'id/', $this->fileName);
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

        public function getDuration()
        {
            return $this->duration;
        }

        public function getSize()
        {
            return $this->file->getClientSize();
        }

        private function calcDuration()
        {
            $ffprobe = FFMpeg\FFProbe::create();
            echo $this->videoPath;
            return $ffprobe->format($this->videoPath)->get('duration');
        }

        private function initParameters()
        {
            $this->fileName = $this->file->getClientOriginalName();
            $this->videoPath = self::VIDEO_DIR . $this->fileName;
            $this->duration = $this->calcDuration();
        }
    }