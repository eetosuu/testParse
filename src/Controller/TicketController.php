<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends AbstractController
{
    #[Route('/api/data', name: 'app_ticket')]
    public function index(TicketRepository $ticketRepository): JsonResponse
    {
        $ticketsObj = $ticketRepository->findAll();
        $ticketsArray = [];
        foreach ($ticketsObj as $ticket) {
            $ticketsArray[$ticket->getId()]['sector'] = $ticket->getSector();
            $ticketsArray[$ticket->getId()]['row'] = $ticket->getLine();
            $ticketsArray[$ticket->getId()]['seat'] = $ticket->getSeat();
            $ticketsArray[$ticket->getId()]['price'] = $ticket->getPrice();
        }

        return $this->json([
            'name' => 'some name',
            'site' => 'site',
            'count' => $ticketRepository->count([]),
            'tickets' => $ticketsArray
        ]);
    }
}
