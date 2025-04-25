<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Form\StartPageFormType;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StartPageController extends AbstractController
{
    #[Route('/', name: 'start_page')]
    public function index(Request $request, SessionRepository $sessionRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        // Create a form to get the username
        $form = $this->createForm(StartPageFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('username')->getData();
            $sessionCode = $form->get('sessionCode')->getData();

             $user = $userRepository->findOneBy(['username' => $username]);
             if (!$user) {
                 // If the user doesn't exist, create the user first (if necessary)
                 $user = new User();
                 $user->setUsername($username);
                 $user->setHost(true);
                 $user->setRoles([]);
                 $user->setPassword('notneeded');
                 $entityManager->persist($user);
             }

            // Authentifiziere den Benutzer programmatisch
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $tokenStorage->setToken($token);
            $request->getSession()->set('_security_main', serialize($token));

            if ($sessionCode) {
                // Try to find an existing session by code
                $session = $sessionRepository->findOneBy(['sessionKey' => $sessionCode]);
                if ($session) {
                    $session->addParticipant($user);
                    $entityManager->persist($session);
                    $entityManager->flush(); 
                    // Redirect to the session page (implement this page logic separately)
                    return $this->redirectToRoute('session_page', ['sessionKey' => $session->getSessionKey()]);
                } else {
                    $this->addFlash('error', 'Session nicht gefunden');
                }
            } else {
                // Create a new session
                $revealMode = $form->get('revealMode')->getData() ? 'immediate' : 'after_all';
                $session = new Session();
                $session->setHost($user);
                $session->setSessionKey(uniqid());
                $session->setEstimationType('story_points');
                $session->setRevealMode($revealMode);
                $session->addParticipant($user);

                $entityManager->persist($session);
                $entityManager->flush();
                
                // Redirect to the new session page
                return $this->redirectToRoute('session_page', ['sessionKey' => $session->getSessionKey()]);
            }
        }

        return $this->render('start_page/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}