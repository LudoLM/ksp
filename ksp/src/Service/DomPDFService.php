<?php

namespace App\Service;

use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class DomPDFService
{
    public function __construct(
        private DompdfWrapperInterface $wrapper
    ) {}

    public function getStreamResponse(string $html, string $filename): StreamedResponse
    {
        return $this->wrapper->getStreamResponse($html, $filename);
    }
}
?>
