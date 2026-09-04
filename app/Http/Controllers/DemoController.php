<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\View\View;

class DemoController extends Controller
{
    public function show(): View
    {
        return view('demo', [
            'characters' => Character::query()
                ->with([
                    'petModel.animationSteps.animationStep',
                    'petModel.animationSteps.clips',
                    'petModel.petModelActions.petAction',
                    'petModel.petModelActions.steps.animationStep',
                ])
                ->orderBy('name')
                ->get(),
        ]);
    }
}
