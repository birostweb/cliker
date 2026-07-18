<?php
// src/Controller/PlayerController.php
namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    #[Route('/api/players', name: 'api_players', methods: ['GET'])]
    public function getPlayers(PlayerRepository $playerRepository): JsonResponse
    {
        // Récupérer tous les joueurs depuis le repository
        $players = $playerRepository->findAll();

        // Transformer les entités en tableau pour JSON
        $data = array_map(function(Player $player) {
            return [
                'player_name' => $player->getName(),
                'play_time' => 0,
                'rebirths' => $player->getRebirth(),
            ];
        }, $players);

        return $this->json($data);
    }
}

