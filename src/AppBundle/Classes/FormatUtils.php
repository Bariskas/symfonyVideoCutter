<?php
    namespace AppBundle\Classes;
    use FFMpeg;
    use AppBundle\Entity\UserVideoEntity;

    class FormatUtils
    {
        const SECONDS_IN_MINUTE = 60;

        public static function formatBytes($bytes, $precision = 2)
        {
            $units = array('B', 'KB', 'MB', 'GB', 'TB');

            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);

            $bytes /= pow(1024, $pow);

            return round($bytes, $precision) . ' ' . $units[$pow];
        }

        public static function formatTime($seconds)
        {
            $seconds = floor($seconds);
            $minutes = floor($seconds / self::SECONDS_IN_MINUTE);
            $seconds = $seconds % self::SECONDS_IN_MINUTE;
            return $minutes . ' м ' . $seconds . ' с';
        }

    }