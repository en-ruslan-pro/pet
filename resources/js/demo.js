import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { chooseAutonomousAction } from './pet-brain';
import { findWalkablePath, getBoundaryTurnTarget, ROOM_LAYOUT, simplifyWalkPath, smoothAngle } from './room-layout';

const container = document.querySelector('#pet-demo');
const actionLabel = document.querySelector('#pet-action');
const lightingControl = document.querySelector('#demo-lighting');
const lightingValue = document.querySelector('#demo-lighting-value');
const cameraPositionValue = document.querySelector('#demo-camera-position');
const cameraControls = document.querySelectorAll('[data-camera-axis]');
const lightPositionValue = document.querySelector('#demo-light-position');
const lightControls = document.querySelectorAll('[data-light-axis]');
const lightDistanceControl = document.querySelector('#demo-light-distance');
const lightDistanceValue = document.querySelector('#demo-light-distance-value');
const lightStrengthControl = document.querySelector('#demo-light-strength');
const lightStrengthValue = document.querySelector('#demo-light-strength-value');
const plantLightDistanceControl = document.querySelector('#demo-plant-light-distance');
const plantLightDistanceValue = document.querySelector('#demo-plant-light-distance-value');
const plantLightStrengthControl = document.querySelector('#demo-plant-light-strength');
const plantLightStrengthValue = document.querySelector('#demo-plant-light-strength-value');
const cameraLightDistanceControl = document.querySelector('#demo-camera-light-distance');
const cameraLightDistanceValue = document.querySelector('#demo-camera-light-distance-value');
const cameraLightStrengthControl = document.querySelector('#demo-camera-light-strength');
const cameraLightStrengthValue = document.querySelector('#demo-camera-light-strength-value');
const hemisphereLightStrengthControl = document.querySelector('#demo-hemisphere-light-strength');
const hemisphereLightStrengthValue = document.querySelector('#demo-hemisphere-light-strength-value');
const characterControl = document.querySelector('#demo-character');
const animationControl = document.querySelector('#demo-animation');
const petName = document.querySelector('#pet-name');
const petNeedMeters = Object.fromEntries(['satiety', 'energy', 'happiness'].map((need) => [need, document.querySelector(`[data-pet-need="${need}"]`)]));
const petNeedValues = Object.fromEntries(['satiety', 'energy', 'happiness'].map((need) => [need, document.querySelector(`[data-pet-need-value="${need}"]`)]));
const sceneParameters = new URLSearchParams(window.location.search);
const isTvMode = sceneParameters.has('tv');
const initialCharacter = (() => {
    const encodedCharacter = sceneParameters.get('character');

    if (encodedCharacter === null) {
        return null;
    }

    try {
        const character = JSON.parse(atob(encodedCharacter.replaceAll('-', '+').replaceAll('_', '/')));

        return typeof character?.assetPath === 'string' && character.assetPath.startsWith('/models/')
            ? character
            : null;
    } catch {
        return null;
    }
})();

if (container === null || lightingControl === null || lightingValue === null || cameraPositionValue === null || lightPositionValue === null || lightDistanceControl === null || lightDistanceValue === null || lightStrengthControl === null || lightStrengthValue === null || plantLightDistanceControl === null || plantLightDistanceValue === null || plantLightStrengthControl === null || plantLightStrengthValue === null || cameraLightDistanceControl === null || cameraLightDistanceValue === null || cameraLightStrengthControl === null || cameraLightStrengthValue === null || hemisphereLightStrengthControl === null || hemisphereLightStrengthValue === null || characterControl === null || animationControl === null) {
    throw new Error('The Virtual Pet TV demo container is missing.');
}

if (!window.WebGLRenderingContext) {
    container.innerHTML = '<div class="demo-error">Для запуска демонстрации нужен браузер с поддержкой WebGL.</div>';
} else {
    const updatePetStatus = (needs) => {
        Object.entries(needs).forEach(([need, value]) => {
            const meter = petNeedMeters[need];
            const valueLabel = petNeedValues[need];
            const roundedValue = Math.round(value);

            if (meter !== null) {
                meter.setAttribute('aria-valuenow', String(roundedValue));
                meter.firstElementChild.style.width = `${roundedValue}%`;
            }

            if (valueLabel !== null) {
                valueLabel.value = String(roundedValue);
            }
        });
    };

    const updatePetName = (name) => {
        if (petName !== null) {
            petName.textContent = name ?? 'Питомец';
        }
    };

    const updateActionLabel = (label) => {
        if (actionLabel !== null) {
            actionLabel.textContent = label;
        }
    };

    const scene = new THREE.Scene();
    scene.background = new THREE.Color('#211f1b');
    scene.fog = new THREE.Fog('#211f1b', 10, 24);

    const camera = new THREE.PerspectiveCamera(42, window.innerWidth / window.innerHeight, 0.1, 100);
    const cameraTarget = new THREE.Vector3(1.2, 1.3, -0.3);
    camera.position.set(7.25, 3.8, 9.75);
    camera.lookAt(cameraTarget);

    const renderer = new THREE.WebGLRenderer({ antialias: true, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.shadowMap.enabled = !isTvMode;
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.12;
    container.append(renderer.domElement);

    const room = new THREE.Group();
    scene.add(room);

    const floor = new THREE.Mesh(
        new THREE.PlaneGeometry(18, 14),
        new THREE.MeshStandardMaterial({ color: '#6c4e37', roughness: 0.78 }),
    );
    floor.rotation.x = -Math.PI / 2;
    floor.receiveShadow = true;
    room.add(floor);

    const backWall = new THREE.Mesh(
        new THREE.PlaneGeometry(18, 9),
        new THREE.MeshStandardMaterial({ color: '#d6c2a4', roughness: 1 }),
    );
    backWall.position.set(0, 4.5, -6.5);
    backWall.receiveShadow = true;
    room.add(backWall);

    const sideWall = new THREE.Mesh(
        new THREE.PlaneGeometry(14, 9),
        new THREE.MeshStandardMaterial({ color: '#cdb796', roughness: 1 }),
    );
    sideWall.rotation.y = Math.PI / 2;
    sideWall.position.set(-8.8, 4.5, 0);
    room.add(sideWall);

    const roomLoader = new GLTFLoader();

    const addRoomAsset = ({ file, position, rotation = 0, rotationX = 0, rotationZ = 0, span, elevation = 0, heightScale = 1 }) => {
        roomLoader.load(`/models/room/${file}.glb`, (gltf) => {
            const asset = gltf.scene;
            asset.rotation.y = rotation;
            asset.rotation.x = rotationX;
            asset.rotation.z = rotationZ;
            asset.updateMatrixWorld(true);

            const initialBounds = new THREE.Box3().setFromObject(asset);
            const initialSize = initialBounds.getSize(new THREE.Vector3());
            const footprint = Math.max(initialSize.x, initialSize.z);

            if (footprint === 0) {
                return;
            }

            asset.scale.setScalar(span / footprint);
            asset.scale.y *= heightScale;
            asset.updateMatrixWorld(true);

            const scaledBounds = new THREE.Box3().setFromObject(asset);
            asset.position.set(position[0], -scaledBounds.min.y + elevation, position[1]);
            asset.traverse((object) => {
                if (object.isMesh) {
                    object.castShadow = true;
                    object.receiveShadow = true;
                }
            });
            room.add(asset);
        });
    };

    ROOM_LAYOUT.assets.forEach(addRoomAsset);

    const toSceneVector = ([x, z]) => new THREE.Vector3(x, 0, z);
    const roomObjects = Object.fromEntries([...ROOM_LAYOUT.assets, ...ROOM_LAYOUT.objects]
        .filter(({ position }) => position !== undefined)
        .map(({ id, interactionPoint, position }) => [id, { interactionPoint: toSceneVector(interactionPoint ?? position) }]));
    const interestPoints = Object.fromEntries(
        Object.entries(roomObjects).map(([name, { interactionPoint }]) => [name, interactionPoint]),
    );
    const addInteractionMesh = (mesh, point, elevation = 0) => {
        mesh.position.copy(interestPoints[point]);
        mesh.position.y += elevation;
        mesh.traverse((object) => {
            if (object.isMesh) {
                object.castShadow = true;
                object.receiveShadow = true;
            }
        });
        room.add(mesh);

        return mesh;
    };
    const createBowl = (point, outerColor, contentsColor) => {
        const bowl = new THREE.Group();
        const outer = new THREE.Mesh(
            new THREE.CylinderGeometry(0.48, 0.36, 0.2, 24),
            new THREE.MeshStandardMaterial({ color: outerColor, roughness: 0.38, metalness: 0.12 }),
        );
        const contents = new THREE.Mesh(
            new THREE.CylinderGeometry(0.34, 0.34, 0.03, 24),
            new THREE.MeshStandardMaterial({ color: contentsColor, roughness: 0.55 }),
        );
        contents.position.y = 0.115;
        bowl.add(outer, contents);
        addInteractionMesh(bowl, point, 0.1);
    };

    createBowl('foodBowl', '#dca34d', '#8b5431');
    createBowl('waterBowl', '#78a8b9', '#9dd9ee');

    const scratchingPost = new THREE.Group();
    scratchingPost.add(
        new THREE.Mesh(new THREE.CylinderGeometry(0.52, 0.62, 0.14, 20), new THREE.MeshStandardMaterial({ color: '#5f4030', roughness: 0.82 })),
        new THREE.Mesh(new THREE.CylinderGeometry(0.2, 0.25, 1.45, 18), new THREE.MeshStandardMaterial({ color: '#b98a5c', roughness: 0.9 })),
    );
    scratchingPost.children[1].position.y = 0.76;
    addInteractionMesh(scratchingPost, 'scratchingPost', 0.07);

    const ball = new THREE.Mesh(
        new THREE.SphereGeometry(0.26, 20, 16),
        new THREE.MeshStandardMaterial({ color: '#db6752', roughness: 0.44 }),
    );
    addInteractionMesh(ball, 'ball', 0.26);

    const toyMouse = new THREE.Group();
    const mouseMaterial = new THREE.MeshStandardMaterial({ color: '#b7a69b', roughness: 0.8 });
    toyMouse.add(
        new THREE.Mesh(new THREE.SphereGeometry(0.22, 16, 12), mouseMaterial),
        new THREE.Mesh(new THREE.ConeGeometry(0.1, 0.16, 12), mouseMaterial),
        new THREE.Mesh(new THREE.ConeGeometry(0.1, 0.16, 12), mouseMaterial),
    );
    toyMouse.children[1].position.set(-0.09, 0.18, 0.02);
    toyMouse.children[2].position.set(0.09, 0.18, 0.02);
    toyMouse.children[1].rotation.z = -0.2;
    toyMouse.children[2].rotation.z = 0.2;
    addInteractionMesh(toyMouse, 'toyMouse', 0.22);

    const roomLights = [
        { color: '#ffb85f', intensity: 95, distance: 15, position: [0, 7, 0], castsShadow: !isTvMode },
        { color: '#ffc878', intensity: 110, distance: 9, position: [-7.15, 7.25, -5.15] },
    ].map(({ color, intensity, distance, position, castsShadow = false }) => {
        const light = new THREE.PointLight(color, intensity, distance, 2);
        light.position.set(...position);
        light.castShadow = castsShadow;
        scene.add(light);

        return { light, intensity };
    });
    const [{ light: roomLight }, { light: plantCornerLight }] = roomLights;
    const cameraLight = new THREE.SpotLight('#ffe5bd', 0.9, 18, 0.8, 0.45, 2);
    cameraLight.position.copy(camera.position);
    cameraLight.target.position.copy(cameraTarget);
    scene.add(cameraLight);
    scene.add(cameraLight.target);
    const hemisphereLight = new THREE.HemisphereLight('#f5d6aa', '#30251e', 2.35);
    scene.add(hemisphereLight);

    if (isTvMode) {
        plantCornerLight.visible = false;
        cameraLight.visible = false;
    }

    const lightingDefaults = {
        exposure: 1.12,
    };
    let lightingLevel = 1;

    const updateLighting = () => {
        lightingLevel = Number(lightingControl.value);

        renderer.toneMappingExposure = lightingDefaults.exposure * lightingLevel;
        lightingValue.textContent = `${lightingLevel.toFixed(2)}×`;
        updateRoomLight();
        updatePlantCornerLight();
        updateCameraLight();
        updateHemisphereLight();
    };

    const updateCameraPosition = () => {
        camera.lookAt(cameraTarget);
        cameraPositionValue.textContent = `X ${camera.position.x.toFixed(2)} · Y ${camera.position.y.toFixed(2)} · Z ${camera.position.z.toFixed(2)}`;
    };

    const updateLightPosition = () => {
        lightPositionValue.textContent = `X ${roomLight.position.x.toFixed(2)} · Y ${roomLight.position.y.toFixed(2)} · Z ${roomLight.position.z.toFixed(2)}`;
    };

    const updateRoomLight = () => {
        roomLight.distance = Number(lightDistanceControl.value);
        roomLight.intensity = Number(lightStrengthControl.value) * lightingLevel;
        lightDistanceValue.textContent = roomLight.distance.toFixed(1);
        lightStrengthValue.textContent = lightStrengthControl.value;
    };

    const updatePlantCornerLight = () => {
        const strength = Number(plantLightStrengthControl.value);

        plantCornerLight.distance = Number(plantLightDistanceControl.value);
        plantCornerLight.intensity = strength * lightingLevel;
        plantLightDistanceValue.textContent = plantCornerLight.distance.toFixed(1);
        plantLightStrengthValue.textContent = strength.toFixed(0);
    };

    const updateCameraLight = () => {
        cameraLight.distance = Number(cameraLightDistanceControl.value);
        cameraLight.intensity = Number(cameraLightStrengthControl.value) * lightingLevel;
        cameraLightDistanceValue.textContent = cameraLight.distance.toFixed(1);
        cameraLightStrengthValue.textContent = cameraLightStrengthControl.value;
    };

    const updateHemisphereLight = () => {
        hemisphereLight.intensity = Number(hemisphereLightStrengthControl.value) * lightingLevel;
        hemisphereLightStrengthValue.textContent = hemisphereLightStrengthControl.value;
    };

    lightingControl.addEventListener('input', updateLighting);
    lightDistanceControl.addEventListener('input', updateRoomLight);
    lightStrengthControl.addEventListener('input', updateRoomLight);
    plantLightDistanceControl.addEventListener('input', updatePlantCornerLight);
    plantLightStrengthControl.addEventListener('input', updatePlantCornerLight);
    cameraLightDistanceControl.addEventListener('input', updateCameraLight);
    cameraLightStrengthControl.addEventListener('input', updateCameraLight);
    hemisphereLightStrengthControl.addEventListener('input', updateHemisphereLight);
    cameraControls.forEach((control) => {
        control.addEventListener('click', () => {
            const axis = control.dataset.cameraAxis;
            const direction = Number(control.dataset.cameraDirection);

            if (axis === undefined || !Number.isFinite(direction)) {
                return;
            }

            camera.position[axis] += direction * 0.25;
            cameraLight.position.copy(camera.position);
            updateCameraPosition();
        });
    });
    lightControls.forEach((control) => {
        control.addEventListener('click', () => {
            const axis = control.dataset.lightAxis;
            const direction = Number(control.dataset.lightDirection);

            if (axis === undefined || !Number.isFinite(direction)) {
                return;
            }

            roomLight.position[axis] += direction * 0.25;
            updateLightPosition();
        });
    });

    updateLighting();
    updateCameraPosition();
    updateLightPosition();
    updateRoomLight();
    updateCameraLight();
    updateHemisphereLight();

    const walkTargets = [[-2.1, -0.25], [1.85, 0.95], [-1.25, 2.1], [2.25, -1.75]];

    let cat;
    let mixer;
    let activeAnimation;
    let currentAction = 'idle';
    let actionElapsed = 0;
    let actionDuration = 8;
    let walkStart;
    let walkTarget;
    let walkPath = [];
    let walkPathDistance = 0;
    let animationClips = [];
    let animationConfiguration = {};
    let actionSequence = [];
    let actionSequenceIndex = 0;
    let selectedAnimation;
    let queuedAction;
    let pendingRemoteAction;
    let requestedCharacterSignature;

    class PetBrain {
        constructor(needs = {}) {
            this.needs = { satiety: 80, energy: 80, happiness: 80 };
            this.lastAction = 'idle';
            this.setNeeds(needs);
        }

        setNeeds(needs) {
            ['satiety', 'energy', 'happiness'].forEach((need) => {
                if (Number.isFinite(needs[need])) {
                    this.needs[need] = THREE.MathUtils.clamp(needs[need], 0, 100);
                }
            });

            updatePetStatus(this.needs);
        }

        advance(delta) {
            this.needs.satiety = Math.max(0, this.needs.satiety - delta / 300);
            this.needs.energy = Math.max(0, this.needs.energy - delta / 600);
            this.needs.happiness = Math.max(0, this.needs.happiness - delta / 900);
            updatePetStatus(this.needs);
        }

        chooseAction(actions) {
            return chooseAutonomousAction(actions, this.needs, this.lastAction);
        }

        completeAction(action) {
            this.lastAction = action;
            const effects = animationConfiguration[action]?.settings?.need_effects ?? {};
            Object.entries(effects).forEach(([need, effect]) => {
                if (Number.isFinite(effect) && need in this.needs) {
                    this.needs[need] = THREE.MathUtils.clamp(this.needs[need] + effect, 0, 100);
                }
            });
            updatePetStatus(this.needs);
        }
    }

    const petBrain = new PetBrain();

    const randomDuration = ([minimum, maximum]) => minimum + Math.random() * (maximum - minimum);
    const chooseWeightedClip = (step) => {
        const clipsByName = new Map(animationClips.map((clip) => [clip.name, clip]));
        const choices = (step?.clips ?? [])
            .map((definition) => ({ definition, clip: clipsByName.get(definition.name) }))
            .filter(({ clip }) => clip !== undefined);
        const totalWeight = choices.reduce((total, { definition }) => total + Math.max(1, definition.weight ?? 1), 0);

        if (totalWeight === 0) {
            return undefined;
        }

        let threshold = Math.random() * totalWeight;

        return choices.find(({ definition }) => {
            threshold -= Math.max(1, definition.weight ?? 1);

            return threshold <= 0;
        }) ?? choices.at(-1);
    };

    const playAnimation = (selection) => {
        if (selection === undefined || mixer === undefined) {
            return;
        }

        const clip = selection.clip ?? selection;
        const playbackRate = selection.definition?.playbackRate ?? 1;
        const isLooping = selection.definition?.isLooping ?? true;
        const nextAnimation = mixer.clipAction(clip);

        if (nextAnimation === activeAnimation) {
            return;
        }

        nextAnimation
            .reset()
            .setEffectiveTimeScale(playbackRate)
            .setEffectiveWeight(1)
            .setLoop(isLooping ? THREE.LoopRepeat : THREE.LoopOnce, isLooping ? Infinity : 1)
            .clampWhenFinished = !isLooping;
        nextAnimation.fadeIn(0.35).play();
        activeAnimation?.fadeOut(0.35);
        activeAnimation = nextAnimation;
    };

    const setAction = (nextAction, clips, options = {}) => {
        currentAction = nextAction;
        actionElapsed = 0;
        const actionDefinition = animationConfiguration[nextAction] ?? {};
        const duration = actionDefinition.settings?.duration_seconds ?? [5, 10];
        actionDuration = options.duration ?? randomDuration(duration);
        updateActionLabel(actionDefinition.settings?.name ?? nextAction);

        actionSequence = animationConfiguration[nextAction]?.steps ?? [];
        actionSequenceIndex = 0;
        const sequenceStep = actionSequence[0];
        const selection = chooseWeightedClip(sequenceStep) ?? (clips[0] === undefined ? undefined : { clip: clips[0], definition: {} });
        playAnimation(selection);
        actionDuration = sequenceStep?.durationSeconds
            ?? (!selection?.definition?.isLooping && selection !== undefined
                ? selection.clip.duration / (selection.definition.playbackRate ?? 1)
                : options.duration ?? randomDuration(duration));

        if (nextAction === 'walk' && cat !== undefined) {
            walkStart = cat.position.clone();
            const randomTarget = walkTargets[Math.floor(Math.random() * walkTargets.length)];
            const boundaryTurnTarget = options.target === undefined
                ? getBoundaryTurnTarget([walkStart.x, walkStart.z])
                : null;
            const target = options.target ?? toSceneVector(boundaryTurnTarget ?? randomTarget);
            const path = findWalkablePath([walkStart.x, walkStart.z], [target.x, target.z]);
            walkPath = simplifyWalkPath(path ?? []).map(([x, z]) => new THREE.Vector3(x, 0, z));
            walkTarget = walkPath.at(-1) ?? walkStart;
            walkPathDistance = walkPath.reduce((total, point, index) => total + (index === 0 ? walkStart : walkPath[index - 1]).distanceTo(point), 0);
            actionDuration = Math.max(2.5, walkPathDistance / 1.15);
        }
    };

    const advanceActionSequence = () => {
        actionSequenceIndex += 1;
        const sequenceStep = actionSequence[actionSequenceIndex];

        if (sequenceStep === undefined) {
            return false;
        }

        const selection = chooseWeightedClip(sequenceStep);

        if (selection === undefined) {
            return false;
        }

        actionElapsed = 0;
        actionDuration = sequenceStep.durationSeconds ?? selection.clip.duration / (selection.definition.playbackRate ?? 1);
        playAnimation(selection);

        return true;
    };

    const beginBehavior = (action) => {
        if (cat === undefined || mixer === undefined || action === undefined || animationConfiguration[action] === undefined) {
            return;
        }

        const point = animationConfiguration[action].settings?.targetRoomItemKey?.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());

        if (point === undefined) {
            setAction(action, animationClips);

            return;
        }

        queuedAction = action;
        const target = interestPoints[point];

        if (target === undefined) {
            setAction(action, animationClips);

            return;
        }

        const distance = cat.position.distanceTo(target);
        setAction('walk', animationClips, { target, duration: Math.max(2.5, distance / 1.15) });
    };

    const startAutonomousBehavior = () => beginBehavior(petBrain.chooseAction(animationConfiguration));

    const performRemoteAction = (action, needs) => {
        if (needs !== undefined) {
            petBrain.setNeeds(needs);
        }

        const behavior = { feed: 'eat', play: 'play', sleep: 'sleep' }[action];

        if (behavior === undefined || animationConfiguration[behavior] === undefined) {
            return;
        }

        if (cat === undefined || mixer === undefined) {
            pendingRemoteAction = action;

            return;
        }

        selectedAnimation = undefined;
        beginBehavior(behavior);
    };

    animationControl.addEventListener('change', () => {
        const selectedIndex = Number(animationControl.value);

        if (animationControl.value === '' || !Number.isInteger(selectedIndex) || animationClips[selectedIndex] === undefined) {
            selectedAnimation = undefined;
            setAction(currentAction, animationClips);

            return;
        }

        selectedAnimation = animationClips[selectedIndex];
        playAnimation(selectedAnimation);
        updateActionLabel(`Анимация: ${selectedAnimation.name}`);
    });

    const selectedCharacter = () => {
        const serializedCharacter = characterControl.selectedOptions[0]?.dataset.character;

        if (serializedCharacter === undefined) {
            return null;
        }

        try {
            const character = JSON.parse(serializedCharacter);

            return typeof character?.assetPath === 'string' && character.assetPath.startsWith('/models/')
                ? character
                : null;
        } catch {
            return null;
        }
    };

    characterControl.addEventListener('change', () => {
        const character = selectedCharacter();

        if (character !== null) {
            loadCharacter(character.assetPath, character.animationConfiguration, character.name);
        }
    });

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) {
            return;
        }

        if (event.data?.action === 'meow') {
            updateActionLabel(`${event.data.petName ?? 'Мурка'} мяукает`);

            return;
        }

        if (event.data?.action === 'sync-needs') {
            petBrain.setNeeds(event.data.needs ?? {});

            return;
        }

        if (event.data?.action === 'sync-character' && event.data.character?.assetPath) {
            loadCharacter(event.data.character.assetPath, event.data.character.animationConfiguration, event.data.character.name);

            return;
        }

        performRemoteAction(event.data?.action, event.data?.needs);
    });

    const loadCharacter = (assetPath, nextAnimationConfiguration = {}, name = undefined) => {
        const characterSignature = JSON.stringify([assetPath, nextAnimationConfiguration]);

        updatePetName(name);

        if (requestedCharacterSignature === characterSignature) {
            return;
        }

        requestedCharacterSignature = characterSignature;
        new GLTFLoader().load(
            assetPath,
        (gltf) => {
            if (requestedCharacterSignature !== characterSignature) {
                return;
            }

            if (cat !== undefined) {
                room.remove(cat);
                mixer?.stopAllAction();
            }

            cat = gltf.scene;
            const bounds = new THREE.Box3().setFromObject(cat);
            const size = bounds.getSize(new THREE.Vector3());
            const scale = 2.5 / size.y;

            cat.scale.setScalar(scale);
            cat.position.set(0, -bounds.min.y * scale, 0.35);
            cat.traverse((object) => {
                if (object.isMesh) {
                    object.castShadow = true;
                    object.receiveShadow = true;
                }
            });
            room.add(cat);

            mixer = new THREE.AnimationMixer(cat);
            animationConfiguration = nextAnimationConfiguration ?? {};
            animationClips = gltf.animations;
            cat.userData.animationClips = animationClips;
            animationControl.options.length = 1;
            animationClips.forEach((clip, index) => {
                const option = new Option(clip.name || `Анимация ${index + 1}`, String(index));
                animationControl.add(option);
            });
            animationControl.disabled = animationClips.length === 0;
            setAction('idle', animationClips);

            if (pendingRemoteAction !== undefined) {
                performRemoteAction(pendingRemoteAction);
                pendingRemoteAction = undefined;
            }
        },
        undefined,
        () => {
            updateActionLabel('Не удалось загрузить модель');
        },
        );
    };

    const defaultCharacter = isTvMode ? null : selectedCharacter();

    loadCharacter(
        initialCharacter?.assetPath ?? defaultCharacter?.assetPath ?? '/models/stripe-the-cat.glb',
        initialCharacter?.animationConfiguration ?? defaultCharacter?.animationConfiguration ?? {},
        initialCharacter?.name ?? defaultCharacter?.name,
    );

    const timer = new THREE.Timer();
    timer.connect(document);

    const animate = () => {
        timer.update();
        const delta = Math.min(timer.getDelta(), 0.05);
        mixer?.update(delta);

        if (cat !== undefined) {
            petBrain.advance(delta);
            actionElapsed += delta;

            if (currentAction === 'walk' && walkStart !== undefined && walkTarget !== undefined) {
                let distanceTravelled = Math.min(actionElapsed / actionDuration, 1) * walkPathDistance;
                let previousPoint = walkStart;

                for (const point of walkPath) {
                    const segmentLength = previousPoint.distanceTo(point);

                    if (distanceTravelled <= segmentLength) {
                        cat.position.lerpVectors(previousPoint, point, segmentLength === 0 ? 1 : distanceTravelled / segmentLength);
                        const direction = point.clone().sub(previousPoint).setY(0);
                        const targetRotation = Math.atan2(direction.x, direction.z);
                        cat.rotation.y = smoothAngle(cat.rotation.y, targetRotation, delta);

                        break;
                    }

                    distanceTravelled -= segmentLength;
                    previousPoint = point;
                }
            }

            if (selectedAnimation === undefined && actionElapsed >= actionDuration && currentAction === 'walk' && queuedAction !== undefined) {
                const nextAction = queuedAction;
                queuedAction = undefined;
                petBrain.completeAction(currentAction);
                setAction(nextAction, cat.userData.animationClips);
            } else if (selectedAnimation === undefined && actionElapsed >= actionDuration) {
                if (!advanceActionSequence()) {
                    petBrain.completeAction(currentAction);
                    startAutonomousBehavior();
                }
            }
        }

        renderer.render(scene, camera);
    };

    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    renderer.setAnimationLoop(animate);
}
