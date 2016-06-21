<?php

    namespace AppBundle\Controller;

    use AppBundle\Utils\FormatUtils;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use AppBundle\Entity\UserVideoEntity;
    use Symfony\Component\HttpFoundation\BinaryFileResponse;
    use AppBundle\Utils\Consts;
    use AppBundle\Utils\VideoUtils;
    use FFMpeg;

    class CutterController extends Controller
    {
        /**
         * @Route("/", name="newVideo")
         * @Method({"GET","POST"})
         */
        public function loadNewVideoAction(Request $request)
        {

            if ($request->getMethod() === 'POST') {
                $videoDir = $this->container->getParameter('video_dir');
                $video = $request->files->get('video');
                $videoEntity = new UserVideoEntity($video);
                $videoUtils = new VideoUtils($videoEntity, $this->container->getParameter('video_dir'));
                $videoEntity->setDuration($videoUtils->calculateDuration());

                $doctrineManager = $this->getDoctrine()->getManager();
                $doctrineManager->persist($videoEntity);
                $doctrineManager->flush();

                $videoUtils->saveVideo();
                $videoUtils->createFrames();

                $data = [
                    'filename' => $videoEntity->getName(),
                    'size' => FormatUtils::formatBytes($videoEntity->getSize()),
                    'videoPath' => $videoDir . '/' .  $videoEntity->getId() . '/' . $videoEntity->getName(),
                    'duration' => round($videoEntity->getDuration(), 3),
                    'id' => $videoEntity->getId(),
                ];

                $responseData['html'] = $this->renderView('cutter/block/videoInformation.html.twig', $data);
                $responseData['duration'] = floor($videoEntity->getDuration());
                return new Response(json_encode($responseData), 200, array('Content-Type'=>'application/json'));
            }
            else
            {
                return $this->render('cutter/videoLoad.html.twig');
            }
        }

        /**
         * @Route("/cut-video", name="cutVideo")
         */
        public function cutAction(Request $request)
        {
            $id = $request->get('videoId');
            $from = $request->request->get('from');
            $to = $request->request->get('to');
            $videoEntity = $this->getDoctrine()->getRepository('AppBundle:UserVideoEntity')
                ->find($id);

            $videoUtil = new VideoUtils($videoEntity, $this->container->getParameter('video_dir'));
            $videoUtil->clip($from, $to);
            $responseData = ['link' => '/upload/' . $videoEntity->getId() . '/' . 'clipped-' . $videoEntity->getName()];
            return new Response(json_encode($responseData), 200, array('Content-Type'=>'application/json'));
        }

        /**
         * @Route("/upload/{id}/{name}", name="downloadVideo")
         */
        public function downloadAction($id, $name)
        {
            $file = Consts::VIDEO_DIR . $id . '/' . $name;
            $content = file_get_contents($file);

            $response = new Response();

            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $name);

            $response->setContent($content);
            return $response;
            //$response = new BinaryFileResponse($file);
            //return $response;
        }

    }
