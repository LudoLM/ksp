<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;

final readonly class SubmitCoursWishesFormResult
{
    private function __construct(
        private ?CoursWishesForm $form,
        public bool $accountAlreadyExists,
    ) {
    }

    public static function accountAlreadyExists(): self
    {
        return new self(null, true);
    }

    public static function submitted(CoursWishesForm $form): self
    {
        return new self($form, false);
    }

    public function form(): CoursWishesForm
    {
        return $this->form ?? throw new \LogicException('Pas de form quand accountAlreadyExists est true.');
    }
}
