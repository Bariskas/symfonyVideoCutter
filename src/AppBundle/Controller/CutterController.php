<?php

    namespace AppBundle\Controller;

    use /** @noinspection PhpUnusedAliasInspection */
        Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use /** @noinspection PhpUnusedAliasInspection */
        Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use AppBundle\Entity\UserVideoEntity;
    use AppBundle\Classes\FilePathGenerator;
    use AppBundle\Classes\FormatUtils;
    use AppBundle\Consts;
    use AppBundle\Utils\VideoUtils;
    use FFMpeg;

    class CutterController extends Controller
    {
        /**
         * @Route("/", name="newVideo")
         * @Method({"GET","POST"})
         * @param Request $request
         * @return Response
         */
        public function loadNewVideoAction(Request $request)
        {
            if ($request->getMethod() === Consts\HTTPRequest::TYPE_POST)
            {
                $video = $request->files->get('video');
                if (!$video)
                {
                    return $this->render('cutter/videoLoad.html.twig');
                }

                $videoPath = $video->getRealPath();
                $videoInformationEntity = new UserVideoEntity($video);
                $videoUtils = new VideoUtils();

                $this->initVideoEntityParams($videoUtils, $videoInformationEntity, $videoPath);
                $this->saveVideoInformationToDB($videoInformationEntity);
                $videoId = $videoInformationEntity->getId();
                $this->saveVideo($video, $videoId);
                $this->createFramesForVideo($videoInformationEntity, $videoUtils);

                $data = [
                    'filename' => $videoInformationEntity->getName(),
                    'size' => FormatUtils::formatBytes($videoInformationEntity->getSize()),
                    'videoPath' => FilePathGenerator::createRefVideoPath($videoId, $videoInformationEntity->getName()),
                    'duration' => FormatUtils::formatTime($videoInformationEntity->getDuration()),
                    'height' => $videoInformationEntity->getHeight(),
                    'width' => $videoInformationEntity->getWidth(),
                    'id' => $videoInformationEntity->getId(),
                ];

                $responseData['html'] = $this->renderView('cutter/block/videoInformation.html.twig', $data);
                $responseData['duration'] = floor($videoInformationEntity->getDuration());
                return new Response(json_encode($responseData), Consts\HTTPRequest::STATUS_OK, array('Content-Type'=>'application/json'));
            }
            return $this->render('cutter/videoLoad.html.twig');
        }

        /**
         * @Route("/cut-video", name="cutVideo")
         * @param Request $request
         * @return Response
         */
        public function cutAction(Request $request)
        {
            $id = $request->get('videoId');
            $timeFrom = $request->get('from');
            $timeTo = $request->get('to');
            $newWidth = $request->get('newWidth', 0);
            $newHeight = $request->get('newHeight', 0);
            $videoEntity = $this->getDoctrine()->getRepository('AppBundle:UserVideoEntity')
                ->find($id);
            $videoId = $videoEntity->getId();
            $videoName = $videoEntity->getName();
            $fullOldPath = FilePathGenerator::createFullVideoPath($videoId, $videoName);
            $fullNewPath = FilePathGenerator::createFullVideoPath($videoId, $videoName, 'cutted-');

            $videoUtil = new VideoUtils($videoEntity, $this->container->getParameter('video_dir'));
            $videoUtil->cutVideo($fullOldPath, $fullNewPath, $timeFrom, $timeTo);
            $prefix = 'cutted-';

            if ($newWidth != 0 && $newHeight != 0)
            {
                $prefix = 'new-';
                $fullOldPath = $fullNewPath;
                $fullNewPath = FilePathGenerator::createFullVideoPath($videoId, $videoName, 'new-');
                $videoUtil->resizeVideo($fullOldPath, $fullNewPath, $newWidth, $newHeight);
            }

            $responseData = ['link' => '/upload/' . $videoEntity->getId() . '/' . $prefix . $videoName];

            return new Response(json_encode($responseData), Consts\HTTPRequest::STATUS_OK, array('Content-Type'=>'application/json'));
        }

        /**
         * @Route("/upload/{videoId}/{name}", name="downloadVideo")
         * @param $videoId
         * @param $name
         * @return Response
         */
        public function downloadAction($videoId, $name)
        {
            $filePath = FilePathGenerator::createRefVideoPath($videoId, $name);
            $content = file_get_contents($filePath);

            $response = $this->createOctetStreamResponse($name);
            $response->setContent($content);
            return $response;
        }

        /**
         * @param VideoUtils $videoUtils
         * @param UserVideoEntity $videoEntity
         * @param string $videoPath path to video from entity
         */
        private function initVideoEntityParams(VideoUtils $videoUtils, UserVideoEntity $videoEntity, $videoPath)
        {
            $videoEntity->setDuration($videoUtils->getVideoDuration($videoPath));
            $videoEntity->setWidth($videoUtils->getVideoWidth($videoPath));
            $videoEntity->setHeight($videoUtils->getVideoHeight($videoPath));
        }

        /**
         * @param $videoInformationEntity
         */
        private function saveVideoInformationToDB($videoInformationEntity)
        {
            $doctrineManager = $this->getDoctrine()->getManager();
            $doctrineManager->persist($videoInformationEntity);
            $doctrineManager->flush();
        }

        /**
         * @param UploadedFile $video
         * @param $id
         */
        private function saveVideo(UploadedFile $video, $id)
        {
            $saveDir = FilePathGenerator::createVideoDirPath($id);
            $video->move($saveDir, $video->getClientOriginalName());
        }

        /**
         * @param UserVideoEntity $videoInformationEntity
         * @param VideoUtils $videoUtils
         */
        private function createFramesForVideo(UserVideoEntity $videoInformationEntity, VideoUtils $videoUtils)
        {
            $videoDuration =  $videoInformationEntity->getDuration();
            $videoId = $videoInformationEntity->getId();
            $framesRootPartPath = FilePathGenerator::createFramesRootPartPath($videoId);
            $videoRefPath = FilePathGenerator::createRefVideoPath($videoId, $videoInformationEntity->getName());
            $videoUtils->createFrames(Consts\Consts::FRAMES_COUNT, $videoRefPath, $framesRootPartPath, $videoDuration);
        }

        /**
         * @param $fileName
         * @return Response
         */
        private function createOctetStreamResponse($fileName)
        {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName);
            return $response;
        }

    }
