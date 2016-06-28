<?php
// src/AppBundle/Entity/Product.php
    namespace AppBundle\Entity;
    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use AppBundle\Utils\Consts;

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

        /**
         * @ORM\Column(type="integer")
         */
        private $height;
        /**
         * @ORM\Column(type="integer")
         */
        private $width;
        /**
         * @var UploadedFile
         */
        private $video;


        public function __construct(UploadedFile $video)
        {
            $this->video = $video;
            $this->name = $this->video->getClientOriginalName();
            $this->size = $this->video->getClientSize();
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


        public function getVideo()
        {
            return $this->video;
        }


        public function getFramePath()
        {
            return Consts::VIDEO_DIR . "$this->id/" . 'frame.jpeg';
        }

    
    /**
     * Set height
     *
     * @param integer $height
     *
     * @return UserVideoEntity
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set width
     *
     * @param integer $width
     *
     * @return UserVideoEntity
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }
}
