<?php

namespace Modules\Settings\Services;

class PlatformIntegrationsService
{
    public function __construct(
        private readonly IntegrationConfigResolver $resolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function status(?int $agencyId = null): array
    {
        return $this->resolver->status($agencyId);
    }
}
