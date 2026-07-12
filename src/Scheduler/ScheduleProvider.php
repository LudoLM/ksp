<?php

namespace App\Scheduler;

use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule]
final class ScheduleProvider implements ScheduleProviderInterface
{
    private ?Schedule $schedule = null;

    public function __construct(
        private readonly string $cronExpression,
    ) {
    }

    public function getSchedule(): Schedule
    {
        return $this->schedule ??= new Schedule()
            ->add(RecurringMessage::cron($this->cronExpression, new RemoveExpiredRefreshTokens()))
            ->add(RecurringMessage::cron('30 2 * * *', new RunCommandMessage('app:archive-inactive-users')))
            ->add(RecurringMessage::cron('0 3 * * *', new RunCommandMessage('app:anonymise-old-archived-users')))
            // ->add(RecurringMessage::cron('0 18 * * 7', new RunCommandMessage('app:send-to-users-cours-availability')))
        ;
    }
}
