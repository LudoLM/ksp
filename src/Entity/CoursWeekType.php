<?php

namespace App\Entity;

use App\Entity\Abstracts\Coursbase;
use App\Repository\CoursWeekTypeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CoursWeekTypeRepository::class)]
class CoursWeekType extends Coursbase
{
    #[ORM\Column(nullable: false)]
    #[Groups(['week_type:index'])]
    protected int $daySelected;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: false)]
    #[Groups(['week_type:index'])]
    protected \DateTimeInterface $timeSelected;

    #[ORM\ManyToOne(inversedBy: 'coursWeekTypes')]
    #[ORM\JoinColumn(nullable: false)]
    protected WeekType $weekType;

    public function getDaySelected(): int
    {
        return $this->daySelected;
    }

    public function setDaySelected(int $daySelected): void
    {
        $this->daySelected = $daySelected;
    }

    public function getTimeSelected(): \DateTimeInterface
    {
        return $this->timeSelected;
    }

    public function setTimeSelected(\DateTimeInterface $timeSelected): void
    {
        $this->timeSelected = $timeSelected;
    }

    public function getWeekType(): WeekType
    {
        return $this->weekType;
    }

    public function setWeekType(?WeekType $weekType): void
    {
        $this->weekType = $weekType;
    }
}
