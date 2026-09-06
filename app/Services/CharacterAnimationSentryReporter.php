<?php

namespace App\Services;

use App\Models\Character;
use Sentry\Severity;
use Sentry\State\Scope;

use function Sentry\captureMessage;
use function Sentry\withScope;

class CharacterAnimationSentryReporter
{
    /**
     * @param  array<int, string>  $missingActionKeys
     */
    public function report(Character $character, array $missingActionKeys): void
    {
        $character->loadMissing('petModel');

        withScope(static function (Scope $scope) use ($character, $missingActionKeys): void {
            $scope->setTags([
                'pet.character.readiness' => 'invalid',
                'pet.character.id' => (string) $character->getKey(),
            ]);
            $scope->setContext('pet_character_animation_readiness', [
                'character_id' => $character->getKey(),
                'character_name' => $character->name,
                'pet_model_id' => $character->pet_model_id,
                'pet_model_key' => $character->petModel?->key,
                'missing_action_keys' => $missingActionKeys,
            ]);

            captureMessage('Pet character is missing required action animations.', Severity::warning());
        });
    }
}
