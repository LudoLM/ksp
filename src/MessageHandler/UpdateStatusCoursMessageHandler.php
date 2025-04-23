<?php

namespace App\MessageHandler;

use App\Message\UpdateStatusCoursMessage;
use App\Repository\CoursRepository;
use App\Service\UpdateCoursService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateStatusCoursMessageHandler
{
    public function __construct(
        private CoursRepository $coursRepository,
        private UpdateCoursService $updateCoursService,
    ) {
    }

    public function __invoke(UpdateStatusCoursMessage $message): void
    {
        $cours = $this->coursRepository->find($message->getCoursId());
        $this->updateCoursService->update($cours);
    }
}
