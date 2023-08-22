<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class DefaultContextService
{

    public function __construct(
        private readonly SerializerInterface $serializer)
    {

    }

    public function getDefaultContext(array $cours): array
    {
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['relationAExclure'],
            // Ajoutez d'autres options ou groupes de sérialisation si nécessaire
        ];

        $serializedCours = $this->serializer->normalize($cours, null, $defaultContext);

        return $serializedCours;
    }

}