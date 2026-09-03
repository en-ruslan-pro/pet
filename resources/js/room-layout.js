export const ROOM_LAYOUT = {
    bounds: { minX: -8.2, maxX: 8.2, minZ: -5.9, maxZ: 6.4 },
    catRadius: 0.42,
    gridSize: 0.35,
    assets: [
        { id: 'frames', file: 'frames', position: [-3.4, -6.35], span: 2.8, elevation: 3.7 },
        { id: 'sofa', file: 'couch', position: [3.7, -4.65], rotationX: Math.PI / 2, span: 3.7, width: 3.4, depth: 1.4, interactionPoint: [1.85, -3.05] },
        { id: 'armchair', file: 'armchair', position: [-7.15, 1.05], rotationX: Math.PI / 2, span: 1.55, width: 1.55, depth: 1.55 },
        { id: 'rug', file: 'rug', position: [-1.15, 2.8], rotationX: Math.PI / 2, span: 3.1, width: 3.1, depth: 3.1, catCanEnter: true },
        { id: 'library', file: 'simple_library_A', position: [6.8, -5.35], rotation: -Math.PI / 2, span: 2.15, width: 1.2, depth: 1.85 },
        { id: 'lamp', file: 'lamp', position: [5.8, -2.95], span: 1.25, width: 0.8, depth: 0.8 },
        { id: 'plant', file: 'plant', position: [-6.9, -4.8], rotation: Math.PI / 4, span: 1.45, heightScale: 1.5, width: 0.95, depth: 0.95 },
    ],
    objects: [
        { id: 'foodBowl', position: [2.8, 1.55], width: 0.96, depth: 0.96, catCanEnter: true },
        { id: 'waterBowl', position: [3.85, 1.55], width: 0.96, depth: 0.96, catCanEnter: true },
        { id: 'scratchingPost', position: [-0.5, -5.65], width: 1.24, depth: 1.24, interactionPoint: [-1.75, -4.5], catCanEnter: false },
        { id: 'ball', position: [-0.3, 2.15], width: 0.52, depth: 0.52, catCanEnter: true, allowsItemOverlap: true },
        { id: 'toyMouse', position: [-1.15, 2.8], width: 0.44, depth: 0.44, catCanEnter: true, allowsItemOverlap: true },
    ],
};

const itemsWithFootprints = (layout) => [...layout.assets, ...layout.objects].filter(({ width, depth }) => width !== undefined && depth !== undefined);

const getItemOverlaps = (items) => items.flatMap((item, index) => items.slice(index + 1)
    .filter((other) => Math.abs(item.position[0] - other.position[0]) < (item.width + other.width) / 2
        && Math.abs(item.position[1] - other.position[1]) < (item.depth + other.depth) / 2)
    .map((other) => [item.id, other.id]));

export const getRoomItemOverlaps = (layout = ROOM_LAYOUT) => getItemOverlaps(
    itemsWithFootprints(layout).filter(({ allowsItemOverlap = false }) => !allowsItemOverlap),
);

export const getBlockingItemOverlaps = (layout = ROOM_LAYOUT) => getItemOverlaps(
    itemsWithFootprints(layout).filter(({ catCanEnter = false, allowsItemOverlap = false }) => !catCanEnter && !allowsItemOverlap),
);

export const isWalkablePosition = ([x, z], layout = ROOM_LAYOUT) => x >= layout.bounds.minX + layout.catRadius
    && x <= layout.bounds.maxX - layout.catRadius
    && z >= layout.bounds.minZ + layout.catRadius
    && z <= layout.bounds.maxZ - layout.catRadius
    && itemsWithFootprints(layout)
        .filter(({ catCanEnter = false }) => !catCanEnter)
        .every((item) => Math.abs(x - item.position[0]) >= item.width / 2 + layout.catRadius
            || Math.abs(z - item.position[1]) >= item.depth / 2 + layout.catRadius);

const closestWalkablePoint = (point, layout) => {
    const [x, z] = point;
    const candidate = [
        Math.max(layout.bounds.minX + layout.catRadius, Math.min(x, layout.bounds.maxX - layout.catRadius)),
        Math.max(layout.bounds.minZ + layout.catRadius, Math.min(z, layout.bounds.maxZ - layout.catRadius)),
    ];

    if (isWalkablePosition(candidate, layout)) {
        return candidate;
    }

    for (let radius = layout.gridSize; radius < 4; radius += layout.gridSize) {
        for (const [offsetX, offsetZ] of [[radius, 0], [-radius, 0], [0, radius], [0, -radius], [radius, radius], [radius, -radius], [-radius, radius], [-radius, -radius]]) {
            const nearby = [candidate[0] + offsetX, candidate[1] + offsetZ];

            if (isWalkablePosition(nearby, layout)) {
                return nearby;
            }
        }
    }

    return null;
};

export const findWalkablePath = (from, to, layout = ROOM_LAYOUT) => {
    const start = closestWalkablePoint(from, layout);
    const goal = closestWalkablePoint(to, layout);

    if (start === null || goal === null) {
        return null;
    }

    const keyFor = ([x, z]) => `${x.toFixed(2)}:${z.toFixed(2)}`;
    const open = [start];
    const cameFrom = new Map();
    const distance = new Map([[keyFor(start), 0]]);
    const heuristic = ([x, z]) => {
        const horizontalDistance = Math.abs(goal[0] - x);
        const verticalDistance = Math.abs(goal[1] - z);

        return Math.max(horizontalDistance, verticalDistance) + (Math.SQRT2 - 1) * Math.min(horizontalDistance, verticalDistance);
    };

    while (open.length > 0) {
        open.sort((left, right) => (distance.get(keyFor(left)) + heuristic(left)) - (distance.get(keyFor(right)) + heuristic(right)));
        const current = open.shift();
        const currentKey = keyFor(current);

        if (heuristic(current) < layout.gridSize) {
            const path = [goal];
            let pathKey = currentKey;

            while (cameFrom.has(pathKey)) {
                const previous = cameFrom.get(pathKey);
                path.unshift(previous);
                pathKey = keyFor(previous);
            }

            return path;
        }

        for (const [offsetX, offsetZ] of [[layout.gridSize, 0], [-layout.gridSize, 0], [0, layout.gridSize], [0, -layout.gridSize], [layout.gridSize, layout.gridSize], [layout.gridSize, -layout.gridSize], [-layout.gridSize, layout.gridSize], [-layout.gridSize, -layout.gridSize]]) {
            const next = [current[0] + offsetX, current[1] + offsetZ];
            const nextKey = keyFor(next);

            if (!isWalkablePosition(next, layout)
                || (offsetX !== 0 && offsetZ !== 0 && (!isWalkablePosition([current[0] + offsetX, current[1]], layout)
                    || !isWalkablePosition([current[0], current[1] + offsetZ], layout)))) {
                continue;
            }

            const nextDistance = distance.get(currentKey) + Math.hypot(offsetX, offsetZ);

            if (nextDistance >= (distance.get(nextKey) ?? Number.POSITIVE_INFINITY)) {
                continue;
            }

            cameFrom.set(nextKey, current);
            distance.set(nextKey, nextDistance);

            if (!open.some((point) => keyFor(point) === nextKey)) {
                open.push(next);
            }
        }
    }

    return null;
};
