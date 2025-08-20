<?php

namespace App\Entity;

use App\Repository\WeekTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: WeekTypeRepository::class)]
class WeekType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['week_type:index'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['week_type:index'])]
    private string $name;

    /**
     * @var Collection<int, CoursWeekType>
     */
    #[ORM\OneToMany(mappedBy: 'weekType', targetEntity: CoursWeekType::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['week_type:index'])]
    private Collection $coursWeekTypes;

    public function __construct()
    {
        $this->coursWeekTypes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, CoursWeekType>
     */
    public function getCoursWeekTypes(): Collection
    {
        return $this->coursWeekTypes;
    }

    public function addCoursWeekType(CoursWeekType $coursWeekType): static
    {
        if (!$this->coursWeekTypes->contains($coursWeekType)) {
            $this->coursWeekTypes->add($coursWeekType);
            $coursWeekType->setWeekType($this);
        }

        return $this;
    }

    public function removeCoursWeekType(CoursWeekType $coursWeekType): static
    {
        // set the owning side to null (unless already changed)
        if ($this->coursWeekTypes->removeElement($coursWeekType) && $coursWeekType->getWeekType() === $this) {
            $coursWeekType->setWeekType(null);
        }

        return $this;
    }
}
