<?php

namespace App\Command;

use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

#[AsCommand(name: 'app:parse')]
class ParsingCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::REQUIRED, 'Тип данных');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $html = '<!DOCTYPE html>
<html>
<head>
<title>Ticket Scheme Example - HTML</title>
</head>
<body>
<div class="ticket">
<h3>Ticket 1</h3>
<p>Sector: A | Row: 3 | Seat: 14 | Price: $50</p>
</div>

<div class="ticket">
<h3>Ticket 2</h3>
<p>Sector: B | Row: 7 | Seat: 22 | Price: $35</p>
</div>

<div class="ticket">
<h3>Ticket 3</h3>
<p>Sector: C | Row: 5 | Seat: 8 | Price: $40</p>
</div>
</body>
</html>';
        $svg = '<svg width="400" height="200" xmlns="http://www.w3.org/2000/svg">
<rect x="50" y="20" width="100" height="50" fill="lightblue" />
<text x="60" y="45" fill="black">Sector: A | Row: 3 | Seat: 14 | Price: $50</text>

<rect x="50" y="80" width="100" height="50" fill="lightgreen" />
<text x="60" y="105" fill="black">Sector: B | Row: 7 | Seat: 22 | Price: $35</text>

<rect x="50" y="140" width="100" height="50" fill="lightyellow" />
<text x="60" y="165" fill="black">Sector: C | Row: 5 | Seat: 8 | Price: $40</text>
</svg>';
        $json = '{
  "tickets": [
    {
      "sector": "A",
      "row": 3,
      "seat": 14,
      "price": 50
    },
    {
      "sector": "B",
      "row": 7,
      "seat": 22,
      "price": 35
    },
    {
      "sector": "C",
      "row": 5,
      "seat": 8,
      "price": 40
    }
  ]
}';
        $ticketsParse = [];

        if ($input->getArgument('type') === 'json') {
            $data = json_decode($json, true);

            foreach ($data as $tickets) {
                $ticketsParse = $tickets;
            }
        } elseif ($input->getArgument('type') === 'html' || $input->getArgument('type') === 'svg') {
            if ($input->getArgument('type') === 'html') {
                $crawler = new Crawler($html);
                $crawler = $crawler->filter('.ticket')->children('p');
            }

            if ($input->getArgument('type') === 'svg') {
                $crawler = new Crawler($svg);
                $crawler = $crawler->filter('text');
            }

            $tickets = [];
            foreach ($crawler as $domEl) {
                $tickets[] = $domEl->textContent;
            }

            foreach ($tickets as $ticket) {
                $params = explode("|", $ticket);
                $ticketParse = [];
                foreach ($params as $param) {
                    $param = explode(':', $param);
                    $ticketParse[mb_strtolower(trim($param[0]))] = trim(str_replace('$', '', $param[1]));
                }
                $ticketsParse[] = $ticketParse;
            }
        } else {
            $output->writeln("Такого типа нет");
            return Command::FAILURE;
        }

        foreach ($ticketsParse as $ticketParse) {
            $ticket = new Ticket();
            $ticket->setSector($ticketParse['sector'])
                ->setLine($ticketParse['row'])
                ->setSeat($ticketParse['seat'])
                ->setPrice($ticketParse['price']);

            $this->entityManager->persist($ticket);
            $this->entityManager->flush();
        }

        $countTickets = count($ticketsParse);

        $output->writeln("Создано билетов: $countTickets");
        return Command::SUCCESS;
    }
}