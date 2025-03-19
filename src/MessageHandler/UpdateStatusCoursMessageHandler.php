<?php

namespace App\MessageHandler;

use App\Message\UpdateStatusCoursMessage;
use App\Repository\CoursRepository;
use App\Service\UpdateStatusCoursService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateStatusCoursMessageHandler
{
    public function __construct(
        private CoursRepository $coursRepository,
        private UpdateStatusCoursService $updateStatusCoursService,
    ) {
    }

    public function __invoke(UpdateStatusCoursMessage $message): void
    {
        $cours = $this->coursRepository->find($message->getCoursId());
        $this->updateStatusCoursService->update($cours);
    }
}
