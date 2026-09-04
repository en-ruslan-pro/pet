const autonomousWeight = (action, baseWeight, needs) => {
    if (action === 'walk') {
        return baseWeight * 3;
    }

    if (action === 'sleep') {
        if (needs.energy <= 25) {
            return baseWeight * 5;
        }

        if (needs.energy <= 45) {
            return baseWeight * 2;
        }

        if (needs.energy <= 60) {
            return baseWeight * 0.6;
        }

        return baseWeight * 0.08;
    }

    if (action === 'eat') {
        return baseWeight * (needs.satiety <= 30 ? 3 : needs.satiety <= 50 ? 1.5 : 0.5);
    }

    if (action === 'play') {
        if (needs.energy <= 25) {
            return baseWeight * 0.25;
        }

        return baseWeight * (needs.happiness <= 35 ? 3 : needs.happiness >= 75 ? 0.25 : 1);
    }

    if (action === 'scratch') {
        return baseWeight * (needs.happiness <= 35 ? 2 : needs.happiness >= 75 ? 0.25 : 1);
    }

    return baseWeight;
};

export const chooseAutonomousAction = (actions, needs, lastAction, random = Math.random) => {
    const autonomousCandidates = Object.entries(actions)
        .filter(([, definition]) => definition.settings?.is_autonomous !== false)
        .map(([action, definition]) => {
            const baseWeight = definition.settings?.autonomous_weight ?? 1;

            return [action, autonomousWeight(action, baseWeight, needs)];
        })
        .filter(([, weight]) => weight > 0);

    const candidates = autonomousCandidates.filter(([action]) => action !== lastAction);

    if (candidates.length === 0) {
        candidates.push(...autonomousCandidates);
    }

    if (candidates.length === 0) {
        return undefined;
    }

    const totalWeight = candidates.reduce((total, [, weight]) => total + weight, 0);
    let cursor = random() * totalWeight;

    for (const [action, weight] of candidates) {
        cursor -= weight;

        if (cursor <= 0) {
            return action;
        }
    }

    return candidates[0][0];
};
