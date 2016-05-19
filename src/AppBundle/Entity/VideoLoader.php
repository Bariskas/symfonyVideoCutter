<?php
    namespace AppBundle\Entity;

    class VideoLoader
    {

        protected $video;

        public function getVideo()
        {
            return $this->video;
        }

        public function setVideo($video)
        {
            $this->video = $video;

            return $this;
        }
    }