<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\ProductBacklogItem;
use App\Entity\User;
use App\Entity\Estimate;
use App\Entity\SessionCard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class SessionPageController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/session/{sessionKey}', name: 'session_page')]
    public function index(string $sessionKey): Response
    {
        $currentUser = $this->getUser();

        // Falls der Benutzer nicht existiert, leite ihn zur Startseite weiter
        if (!$currentUser) {
            return $this->redirectToRoute('start_page');
        }

        $session = $this->entityManager->getRepository(Session::class)->findOneBy(['sessionKey' => $sessionKey]);

        // Redirect to the start page if the session does not exist
        if (!$session) {
            return $this->redirectToRoute('start_page');
        }

        // Redirect to the start page if the user is not part of the session
        if (!$session->getParticipants()->contains($currentUser)) {
            return $this->redirectToRoute('start_page');
        }

        $isHost = false;
        if($currentUser->getId() === $session->getHost()->getId()){
            $isHost = true;
        }

        $defaultCards = [
            ['value' => '0', 'type' => 'story_points'],
            ['value' => '1', 'type' => 'story_points'],
            ['value' => '2', 'type' => 'story_points'],
            ['value' => '3', 'type' => 'story_points'],
            ['value' => '5', 'type' => 'story_points'],
            ['value' => '8', 'type' => 'story_points'],
            ['value' => '13', 'type' => 'story_points'],
            ['value' => '21', 'type' => 'story_points'],
            ['value' => '100', 'type' => 'story_points'],
            ['value' => '?', 'type' => 'story_points'],
            ['value' => '0', 'type' => 'hours'],
            ['value' => '0.5', 'type' => 'hours'],
            ['value' => '1', 'type' => 'hours'],
            ['value' => '2', 'type' => 'hours'],
            ['value' => '4', 'type' => 'hours'],
            ['value' => '8', 'type' => 'hours'],
            ['value' => '12', 'type' => 'hours'],
            ['value' => '16', 'type' => 'hours'],
            ['value' => '24', 'type' => 'hours'],
            ['value' => '32', 'type' => 'hours'],
            ['value' => '40', 'type' => 'hours'],
        ];

        // Lade benutzerdefinierte Karten für diese Session
        $customCards = $this->entityManager->getRepository(SessionCard::class)->findBySession($session);

        // Wenn benutzerdefinierte Karten vorhanden sind, bereite sie vor
        $formattedCustomCards = [];
        foreach ($customCards as $customCard) {
            $formattedCustomCards[] = [
                'value' => $customCard->getValue(),
                'type' => $customCard->getType(),
            ];
        }

        // Kombiniere vordefinierte und benutzerdefinierte Karten
        $cards = array_filter(
            array_merge($defaultCards, $formattedCustomCards),
            function ($card) use ($session) {
                return $card['type'] === $session->getEstimationType();
            }
        );

        $productBacklogItems = $this->entityManager->getRepository(ProductBacklogItem::class)->findBy(['session' => $session]);

        $currentPbi = $session->getActivePbi();

        $currentEstimate = null;
        if($currentPbi){
            $existingEstimate = $this->entityManager->getRepository(Estimate::class)->findOneBy([
                'participant' => $currentUser,
                'session' => $session,
                'productBacklogItem' => $currentPbi
            ]);

            if($existingEstimate){
                $currentEstimate = $existingEstimate->getValue();
            }            
        }

        return $this->render('session_page/session_page.html.twig', [
            'session' => $session,
            'user' => $currentUser,
            'is_host' => $isHost,
            'username' => $currentUser->getUsername(),
            'cards' => $cards,
            'productBacklogItems' => $productBacklogItems,
            'currentPbi' => $currentPbi,
            'currentEstimate' => $currentEstimate,
        ]);
    }

    #[Route('/session/{sessionKey}/add-pbi', name:'add_pbi', methods:["POST"])]
    public function addPbi(Request $request, string $sessionKey): Response
    {
        $session = $this->entityManager->getRepository(Session::class)->findOneBy(['sessionKey' => $sessionKey]);

        if (!$session) {
            throw $this->createNotFoundException('Session not found.');
        }

        $title = $request->request->get('title');
        $description = $request->request->get('description');

        $pbi = new ProductBacklogItem();
        $pbi->setTitle($title);
        $pbi->setDescription($description);
        $pbi->setSession($session);

        $this->entityManager->persist($pbi);
        $this->entityManager->flush();

         return new JsonResponse([
            'status' => 'success'
         ]);
    }

    #[Route('/session/{sessionKey}/remove-user/{userId}', name:'remove_user', methods:["POST"])]
    public function removeUser(string $sessionKey, int $userId): Response
    {
        $session = $this->entityManager->getRepository(Session::class)->findOneBy(['sessionKey' => $sessionKey]);
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$session || !$user) {
            throw $this->createNotFoundException('Session or user not found.');
        }

        $session->removeParticipant($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('session_page', ['sessionKey' => $sessionKey]);
    }

    #[Route('/session/{sessionKey}/activate-pbi/{pbiId}', name:'activate_pbi', methods:["POST", "GET"])]
    public function activatePbi(string $sessionKey, int $pbiId): Response
    {
        $session = $this->entityManager->getRepository(Session::class)->findOneBy(['sessionKey' => $sessionKey]);
        $pbi = $this->entityManager->getRepository(ProductBacklogItem::class)->find($pbiId);

        if (!$session || !$pbi) {
            throw $this->createNotFoundException('Session or PBI not found.');
        }

        $session->setActivePbi($pbi);
        $this->entityManager->flush();

        return $this->redirectToRoute('session_page', ['sessionKey' => $sessionKey]);
    }

  #[Route('/session/{sessionKey}/select-card', name:'select_card', methods:["POST", "GET"])]
    public function selectCard(Request $request, string $sessionKey): Response
    {
        // Retrieve the session by its session key
        $session = $this->entityManager->getRepository(Session::class)->findOneBy(['sessionKey' => $sessionKey]);

        // Get the data sent in the request (card value and PBI id)
        $data = json_decode($request->getContent(), true);
        $pbiId = $data['pbiId'];
        $cardValue = $data['cardValue'];

        // Retrieve the PBI by ID
        $pbi = $this->entityManager->getRepository(ProductBacklogItem::class)->find($pbiId);

        if (!$session || !$pbi) {
            throw $this->createNotFoundException('Session or Product Backlog Item not found.');
        }

        // Retrieve the current logged-in user
        $currentUser = $this->getUser();

        // Try to find an existing estimate for the current user and the current PBI
        $existingEstimate = $this->entityManager->getRepository(Estimate::class)->findOneBy([
            'participant' => $currentUser,
            'session' => $session,
            'productBacklogItem' => $pbi
        ]);

        // If an estimate already exists, update it; otherwise, create a new one
        if ($existingEstimate) {
            $existingEstimate->setValue($cardValue);
        } else {
            $userEstimate = new Estimate();
            $userEstimate->setValue($cardValue);
            $userEstimate->setParticipant($currentUser);
            $userEstimate->setSession($session);
            $userEstimate->setProductBacklogItem($pbi);
            $userEstimate->setRevealed(false);
            $this->entityManager->persist($userEstimate);
        }

        // Flush the changes to the database
        $this->entityManager->flush();

        // Redirect to the session page
        return new JsonResponse([
            'status' => 'success'
        ]);
    }

    #[Route('/session/{sessionKey}/reveal-estimates', name: 'reveal_estimates', methods: ['POST'])]
    public function revealEstimates(string $sessionKey): Response
    {
        $session = $this->entityManager->getRepository(Session::class)->findOneBy(['sessionKey' => $sessionKey]);

        if (!$session) {
            throw $this->createNotFoundException('Session not found.');
        }

        foreach ($session->getParticipants() as $participant) {
            foreach ($participant->getEstimates() as $estimate) {
                $estimate->setRevealed(true);
            }
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('session_page', ['sessionKey' => $sessionKey]);
    }

    #[Route('/session/{sessionKey}/data', name: 'session_data', methods: ['GET'])]
public function getSessionData(string $sessionKey): JsonResponse
{
    $currentUser = $this->getUser();

    // Falls der Benutzer nicht eingeloggt ist, 401 zurückgeben
    if (!$currentUser) {
        return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }

    $session = $this->entityManager->getRepository(Session::class)->findOneBy(['sessionKey' => $sessionKey]);

    // Falls die Session nicht existiert, 404 zurückgeben
    if (!$session) {
        return $this->json(['error' => 'Session not found'], Response::HTTP_NOT_FOUND);
    }

    // Prüfen, ob der Benutzer noch in der Session ist
    if (!$session->getParticipants()->contains($currentUser)) {
        return $this->json(['error' => 'User not part of the session'], Response::HTTP_FORBIDDEN);
    }

    // Aktives Product Backlog Item
    $currentPbiEnitiy = $session->getActivePbi();
    $currentPbi = [];
    if ($currentPbiEnitiy) {
        $currentPbi = [
            'id' => $currentPbiEnitiy->getId(),
            'title' => $currentPbiEnitiy->getTitle(),
            'description' => $currentPbiEnitiy->getDescription(),
        ];
    }

    // Estimates für Benutzer
    $estimates = [];
    $revealedValues = []; // Liste der aufgedeckten Karten
    foreach ($session->getParticipants() as $participant) {
        $estimate = $this->entityManager->getRepository(Estimate::class)->findOneBy([
            'participant' => $participant,
            'session' => $session,
            'productBacklogItem' => $currentPbiEnitiy,
        ]);
        if ($estimate && $estimate->isRevealed()) {
            $revealedValues[] = $estimate->getValue(); // Werte sammeln, die aufgedeckt sind
        }
        $estimates[] = [
            'username' => $participant->getUsername(),
            'participantId' => $participant->getId(),
            'estimate' => $estimate ? $estimate->getValue() : null,
            'revealed' => $estimate ? $estimate->isRevealed() : false,
        ];
    }

    // Durchschnitt der aufgedeckten Karten berechnen
    $average = !empty($revealedValues) ? array_sum($revealedValues) / count($revealedValues) : null;

    // "Sieger" ermitteln (häufigste Karte)
    $winner = null;
    if (!empty($revealedValues)) {
        $valueCounts = array_count_values($revealedValues);
        arsort($valueCounts); // Sortiere nach Häufigkeit
        $winner = array_key_first($valueCounts); // Nimm den häufigsten Wert
    }

    // Backlogitems
    $productBacklogItems = [];
    foreach ($session->getProductBacklogItems() as $pbi) {
        $productBacklogItems[] = [
            'id' => $pbi->getId(),
            'title' => $pbi->getTitle(),
            'description' => $pbi->getDescription(),
        ];
    }

    return $this->json([
        'currentPbi' => $currentPbi ?? null,
        'estimates' => $estimates,
        'productBacklogItems' => $productBacklogItems,
        'isHost' => $currentUser->getId() === $session->getHost()->getId(),
        'averageRevealed' => $average,
        'winner' => $winner,
    ]);
}


}
