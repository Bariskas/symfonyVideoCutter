<?php

    namespace AppBundle\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\FileType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\RangeType;
    use AppBundle\Entity\UserVideoEntity;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
    use AppBundle\Utils\VideoUtilit;
    use FFMpeg;

    class CutterController extends Controller
    {
        const VIDEO_DIR = 'uploads/videos/';
        /**
         * @Route("/", name="home")
         */
        public function indexAction(Request $request)
        {
            return $this->render('cutter/index.html.twig');
        }

        /**
         * @Route("/load-new-video-beta", name="newVideoBeta")
         */
        public function loadNewVideoBetaAction(Request $request)
        {
            $videoFormEntity = new VideoFormEntity();

            $videoForm = $this->createFormBuilder($videoFormEntity)
                ->add('video', FileType::class, array('label' => 'Форма для загрузки видео'))
                ->add('save', SubmitType::class, array('label' => 'Load video'))
                ->getForm();

            $videoForm->handleRequest($request);
            if ($videoForm->isSubmitted() && $videoForm->isValid())
            {
                $video = $videoFormEntity->getVideo();
                $videoHandler = new UserVideoEntity($video);

                $doctrineManager = $this->getDoctrine()->getManager();
                $doctrineManager->persist($videoHandler);
                $doctrineManager->flush();

                $videoHandler->saveVideo();
                $framePath = $videoHandler->createFrame();
                $data = [
                    'filename' => $videoHandler->getName(),
                    'size' => $videoHandler->getSize(),
                    'framePath' => $framePath,
                    'duration' => $videoHandler->getDuration(),
                    'id' => $videoHandler->getId(),
                ];

                return $this->render('cutter/videoLoaded.html.twig', $data);
            }

            return $this->render('cutter/videoLoad.html.twig', [
                'form' => $videoForm->createView(),
            ]);
        }

        /**
         * @Route("/load-new-video", name="newVideo")
         * @Method({"GET","POST"})
         */
        public function loadNewVideoAction(Request $request)
        {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $video = $request->files->get('video');
                $videoEntity = new UserVideoEntity($video);
                $videoUtilit = new VideoUtilit($videoEntity);
                $videoEntity->setDuration($videoUtilit->calculateDuration());

                $doctrineManager = $this->getDoctrine()->getManager();
                $doctrineManager->persist($videoEntity);
                $doctrineManager->flush();

                $videoUtilit->saveVideo();
                $framePath = $videoUtilit->createFrame();

                $data = [
                    'filename' => $videoEntity->getName(),
                    'size' => $videoEntity->getSize(),
                    'framePath' => $framePath,
                    'duration' => $videoEntity->getDuration(),
                    'id' => $videoEntity->getId(),
                ];

                return $this->render('cutter/videoLoaded.html.twig', $data);
            }
            else
            {
                return $this->render('cutter/videoLoad.html.twig');
            }
        }

        /**
         * @Route("/phpinfo", name="phpinfo")
         */
        public function phpInfoAction()
        {
            return phpinfo();
        }

        /**
         * @Route("/cut/{videoid}", name="cut")
         */
        public function videoAction(Request $request, $videoid)//, $videoid = 0, $second = 1)
        {
            $data = array('message' => 'Type your message here');

            $repository = $this->getDoctrine()
                ->getRepository('AppBundle:UserVideoEntity');
            $video = $repository->find($videoid);

            $form = $this->createFormBuilder($data)
                ->add('name', TextType::class, array(
                    'label' => 'Имя видео',
                    'data' => $video->getName()
                ))
                ->add('id', TextType::class, array(
                    'label' => 'ID видео',
                    'data' => $video->getId()
                ))
                ->add('duration', TextType::class, array(
                    'label' => 'Длительность видео',
                    'data' => $video->getDuration()
                ))
                ->add('time', RangeType::class, array(
                    'label' => 'момент из видео',
                    'attr' => array(
                        'min' => 1,
                        'max' => $video->getDuration()
                    )))
                ->add('save', SubmitType::class, array('label' => 'Получить кадр'))
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid())
            {
                $data = $form->getData();
                return $this->render('cutter/videoCut.html.twig', [
                    'form' => $form->createView(),
                    'framePath' => '../' . $video->createFrame($data['time'])
                ]);
            }

            return $this->render('cutter/videoCut.html.twig', [
                'form' => $form->createView(),
                'framePath' => ''
            ]);
        }

        /**
         * @Route("/video-list", name="videoList")
         */
        public function showVideosAction()
        {
            $repository = $this->getDoctrine()
                ->getRepository('AppBundle:UserVideoEntity');
            $videos = $repository->findAll();
            return $this->render('cutter/videoList.html.twig', ['videos' => $videos]);
        }
    }
