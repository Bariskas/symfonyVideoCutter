<?php
// src/AppBundle/Entity/Product.php
    namespace AppBundle\Entity;
    use FFMpeg;
    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    /**
     * @ORM\Entity
     * @ORM\Table(name="UserVideo")
     */
    class UserVideoEntity
    {
        const VIDEO_DIR = 'uploads/videos/';
        /**
         * @ORM\Column(type="integer")
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * @ORM\Column(type="string", length=100)
         */
        private $name;

        /**
         * @ORM\Column(type="decimal", scale=2)
         */
        private $size;

        /**
         * @ORM\Column(type="integer")
         */
        private $duration;

        private $video;
        private $dir;

        public function __construct(UploadedFile $video)
        {
            $this->video = $video;
            $this->name = $this->video->getClientOriginalName();
            $this->size = $this->video->getClientSize();
            $this->duration = $this->calcDuration();
        }

        /**
         * Get id
         *
         * @return integer
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Set name
         *
         * @param string $name
         *
         * @return UserVideoEntity
         */
        public function setName($name)
        {
            $this->name = $name;

            return $this;
        }

        /**
         * Get name
         *
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * Set size
         *
         * @param string $size
         *
         * @return UserVideoEntity
         */
        public function setSize($size)
        {
            $this->size = $size;

            return $this;
        }

        /**
         * Get size
         *
         * @return string
         */
        public function getSize()
        {
            return $this->size;
        }

        /**
         * Set duration
         *
         * @param integer $duration
         *
         * @return UserVideoEntity
         */
        public function setDuration($duration)
        {
            $this->duration = $duration;

            return $this;
        }

        /**
         * Get duration
         *
         * @return integer
         */
        public function getDuration()
        {
            return $this->duration;
        }

        public function getFramePath()
        {
            return self::VIDEO_DIR . "$this->id/" . 'frame.jpeg';
        }

        public function saveVideo()
        {
            $this->dir = self::VIDEO_DIR . "$this->id/";
            $this->video->move($this->dir, $this->name);
        }

        public function createFrame($time = 0)
        {
            $framePath =  self::VIDEO_DIR . "$this->id/" . (($time === 0) ? 'frame' : $time) . '.jpeg';
            $time = ($time === 0) ? round($this->duration / 2) : $time;
            $ffmpeg = FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open(self::VIDEO_DIR . "$this->id/" . $this->name);
            $video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($time))
                ->save($framePath);
            return $framePath;
        }

        private function calcDuration()
        {
            $ffprobe = FFMpeg\FFProbe::create();
            return $ffprobe->format($this->video->getRealPath())->get('duration');
        }
    }
