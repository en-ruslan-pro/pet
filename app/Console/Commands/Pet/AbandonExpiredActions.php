<?php

namespace App\Console\Commands\Pet;

use App\Services\PetTelemetryService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pet:abandon-expired-actions')]
#[Description('Abandon pet actions that did not complete before their timeout')]
class AbandonExpiredActions extends Command
{
    public function handle(PetTelemetryService $telemetry): int
    {
        $abandonedActionCount = $telemetry->abandonExpiredActions();

        $this->info("Abandoned {$abandonedActionCount} expired pet action(s).");

        return self::SUCCESS;
    }
}
