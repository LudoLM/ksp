<?php

namespace App\DTO;

final readonly class UsersCoursDTO
{
    public function __construct(
        public int $id,
        public bool $isOnWaitingList,
        public LightUserDTO $user,
    ) {
    }
}
