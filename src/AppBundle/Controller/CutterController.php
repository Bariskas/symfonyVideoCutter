<?php

    namespace AppBundle\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\FileType;
    use AppBundle\Entity\VideoLoader;
    use AppBundle\Utils\VideoUtilit;
    use FFMpeg;

    class CutterController extends Controller
    {
        /**
         * @Route("/", name="index")
         */
        public function indexAction(Request $request)
        {
            $authorization = new VideoLoader();

            $authorizationForm = $this->createFormBuilder($authorization)
                ->add('video', FileType::class, array('label' => 'video file'))
                ->add('save', SubmitType::class, array('label' => 'Load video'))
                ->getForm();

            $authorizationForm->handleRequest($request);

            if ($authorizationForm->isSubmitted() && $authorizationForm->isValid())
            {
                $file = $authorization->getVideo();
                $videoHandler = new VideoUtilit($file);

                $data = [
                    'filename' => $videoHandler->getFileName(),
                    'size' => $videoHandler->getSize(),
                    'frame' => $videoHandler->getFrame(),
                    'duration' => $videoHandler->getDuration(),
                ];

                return $this->render('cutter/video.html.twig', $data);
                //return $this->redirectToRoute('index');
            }

            return $this->render('cutter/index.html.twig', [
                'form' => $authorizationForm->createView(),
            ]);
        }

        /**
         * @Route("/phpinfo", name="phpinfo")
         */
        public function phpInfoAction()
        {
            return phpinfo();
        }

        /**
         * @Route("/video/{videoname}/{second}", name="phpinfo")
         */
        public function videoAction($videoname, $second)
        {
            $videoPath = 'uploads\videos\\' . $videoname;
            $ffmpeg = FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open($videoPath);
            $video
                ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($second))
                ->save('frames\frame.jpg');
            $file =    file_get_contents('frames\frame.jpg');
            $headers = array(
                'Content-Type'     => 'image/png',
                'Content-Disposition' => 'inline; filename="frame.jpg"');
            return new Response($file, 200, $headers);
            //return $kek;
            //return $this->render('cutter/video.html.twig', [
            //    'framePath' =>  'D:\science\Projects\PhpStorm\symfonyVideoCutter\web\frame.jpg'//$this->container->getParameter('kernel.root_dir').'../web/uploads/frame.jpg'
            //]);
        }
    }
