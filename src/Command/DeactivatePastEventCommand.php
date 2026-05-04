<?php

namespace Pixel\EventBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Pixel\EventBundle\Entity\Event;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: "deactivate:past:events",
    description: "Deactivate past events after a given time"
)]
class DeactivatePastEventCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private KernelInterface $kernel;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setHelp("Deactivate past events after a given time")
            ->addArgument("nbDays", InputArgument::OPTIONAL, "Number of days after which a past events must be deactivated", 3);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new Logger("eventbundle-deactivate-past-events");
        $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . "/events/deactivate-past-events.log", 12));

        $output->writeln("Looking for past events to deactivate");
        $logger->info("Looking for past events to deactivate");

        try {
            $pastEvents = $this->entityManager->getRepository(Event::class)->findPastEvents($input->getArgument("nbDays"));

            if (!empty($pastEvents)) {
                $output->writeln("There are " . count($pastEvents) . " events to deactivate");
                $logger->info("There are " . count($pastEvents) . " events to deactivate");

                foreach ($pastEvents as $pastEvent) {
                    $output->writeln("Deactivating past event: " . $pastEvent->getName());
                    $logger->info("Deactivating past event: " . $pastEvent->getName());
                    $pastEvent->setEnabled(false);
                    $this->entityManager->persist($pastEvent);
                }

                $this->entityManager->flush();
            } else {
                $output->writeln("No past events found");
                $logger->info("No past events found");
            }

            $output->writeln("End of processing");
            $logger->info("End of processing");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Error while deactivating past events: " . $e->getMessage() . "</error>");
            $logger->error("Error while deactivating past events: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
