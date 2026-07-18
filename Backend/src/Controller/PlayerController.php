<?php
// src/Controller/PlayerController.php
namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlayerController extends AbstractController
{
    private const MAX_LIMIT = 100;
    // Sanity bounds to reject obviously bogus submissions (not real anti-cheat).
    private const MAX_REBIRTH = 1_000_000;
    private const MAX_SCORE = PHP_INT_MAX;
    private const MAX_TIME_SECONDS = 24 * 60 * 60;
    private const MAX_TROPHY_COUNT = 1_000;

    #[Route('/api/leaderboard', name: 'api_leaderboard_get', methods: ['GET'])]
    public function getLeaderboard(Request $request, PlayerRepository $playerRepository): JsonResponse
    {
        $limit = min(self::MAX_LIMIT, max(1, $request->query->getInt('limit', 20)));

        $runs = $playerRepository->findTopRuns($limit);

        $data = array_map(static fn (Player $player) => [
            'id' => $player->getId(),
            'name' => $player->getName(),
            'rebirths' => $player->getRebirth(),
            'score' => $player->getScore(),
            'timeSeconds' => $player->getTimeSeconds(),
            'activeSeconds' => $player->getActiveSeconds(),
            'trophies' => $player->getTrophyCount(),
            'createdAt' => $player->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ], $runs);

        return $this->json($data);
    }

    #[Route('/api/leaderboard', name: 'api_leaderboard_post', methods: ['POST'])]
    public function submitRun(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON body'], 400);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $rebirths = (int) ($payload['rebirths'] ?? -1);
        $score = (int) ($payload['score'] ?? -1);
        $timeSeconds = (int) ($payload['timeSeconds'] ?? -1);
        $activeSeconds = (int) ($payload['activeSeconds'] ?? 0);
        $trophies = (int) ($payload['trophies'] ?? 0);

        if (
            $rebirths > self::MAX_REBIRTH
            || $score > self::MAX_SCORE
            || $timeSeconds > self::MAX_TIME_SECONDS
            || $activeSeconds > self::MAX_TIME_SECONDS
            || $trophies > self::MAX_TROPHY_COUNT
        ) {
            return $this->json(['error' => 'Submitted values are out of allowed range'], 422);
        }

        // Active (window-focused) time can never exceed total elapsed time.
        $activeSeconds = min($activeSeconds, max($timeSeconds, 0));

        $player = new Player();
        $player->setName($name);
        $player->setRebirth($rebirths);
        $player->setScore($score);
        $player->setTimeSeconds($timeSeconds);
        $player->setActiveSeconds($activeSeconds);
        $player->setTrophyCount($trophies);

        $errors = $validator->validate($player);
        if (count($errors) > 0) {
            $messages = array_map(
                static fn ($error) => $error->getPropertyPath() . ': ' . $error->getMessage(),
                iterator_to_array($errors)
            );

            return $this->json(['error' => 'Validation failed', 'details' => $messages], 422);
        }

        $entityManager->persist($player);
        $entityManager->flush();

        return $this->json([
            'id' => $player->getId(),
            'name' => $player->getName(),
            'rebirths' => $player->getRebirth(),
            'score' => $player->getScore(),
            'timeSeconds' => $player->getTimeSeconds(),
            'activeSeconds' => $player->getActiveSeconds(),
            'trophies' => $player->getTrophyCount(),
            'createdAt' => $player->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ], 201);
    }
}
