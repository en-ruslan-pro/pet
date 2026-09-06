<?php

namespace App\Console\Commands\Pet;

use App\Models\Character;
use App\Models\PetAction;
use App\Services\CharacterAnimationSentryReporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pet:check-character-animations')]
#[Description('Report characters without complete base action animations')]
class CheckCharacterAnimations extends Command
{
    public function handle(CharacterAnimationSentryReporter $reporter): int
    {
        $actionKeys = PetAction::baseActionKeys();
        $invalidCharacterCount = 0;

        Character::query()
            ->with([
                'petModel.animationSteps.animationStep',
                'petModel.animationSteps.clips',
                'petModel.petModelActions.petAction',
                'petModel.petModelActions.steps.animationStep',
            ])
            ->lazyById()
            ->each(function (Character $character) use ($actionKeys, $reporter, &$invalidCharacterCount): void {
                $missingActionKeys = $character->missingBaseActionAnimations($actionKeys);

                if ($missingActionKeys === []) {
                    return;
                }

                $reporter->report($character, $missingActionKeys);
                $this->error("{$character->name}: ".implode(', ', $missingActionKeys));
                $invalidCharacterCount++;
            });

        return $invalidCharacterCount === 0 ? self::SUCCESS : self::FAILURE;
    }
}
