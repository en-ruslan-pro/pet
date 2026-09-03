import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

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
const cameraLightDistanceControl = document.querySelector('#demo-camera-light-distance');
const cameraLightDistanceValue = document.querySelector('#demo-camera-light-distance-value');
const cameraLightStrengthControl = document.querySelector('#demo-camera-light-strength');
const cameraLightStrengthValue = document.querySelector('#demo-camera-light-strength-value');
const animationControl = document.querySelector('#demo-animation');

if (container === null || actionLabel === null || lightingControl === null || lightingValue === null || cameraPositionValue === null || lightPositionValue === null || lightDistanceControl === null || lightDistanceValue === null || cameraLightDistanceControl === null || cameraLightDistanceValue === null || cameraLightStrengthControl === null || cameraLightStrengthValue === null || animationControl === null) {
    throw new Error('The Virtual Pet TV demo container is missing.');
}

if (!window.WebGLRenderingContext) {
    container.innerHTML = '<div class="demo-error">Для запуска демонстрации нужен браузер с поддержкой WebGL.</div>';
} else {
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
    renderer.shadowMap.enabled = true;
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

    const roomAssets = [
        { file: 'frames', position: [-3.4, -6.35], span: 2.8, elevation: 3.7 },
        { file: 'couch', position: [3.7, -4.65], rotationX: Math.PI / 2, span: 3.7 },
        { file: 'armchair', position: [-7.15, 1.05], rotationX: Math.PI / 2, span: 1.55 },
        { file: 'simple_library_A', position: [6.8, -5.35], rotation: -Math.PI / 2, span: 2.15 },
        { file: 'lamp', position: [5.8, -2.95], span: 1.25 },
        { file: 'plant', position: [-6.9, -4.8], rotation: Math.PI / 4, span: 1.45, heightScale: 1.5 },
    ];

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

    roomAssets.forEach(addRoomAsset);

    const interestPoints = {
        bowl: new THREE.Vector3(2.8, 0, 1.55),
        bed: new THREE.Vector3(-2.75, 0, -2.65),
        playArea: new THREE.Vector3(-1.15, 0, 2.8),
        window: new THREE.Vector3(4.6, 0, -4.75),
        scratchingPost: new THREE.Vector3(5.85, 0, 1.9),
        sofa: new THREE.Vector3(2.25, 0, -3.55),
    };
    const interactionProps = [
        { point: 'bowl', geometry: new THREE.CylinderGeometry(0.48, 0.36, 0.18, 24), color: '#dca34d', elevation: 0.09 },
        { point: 'bed', geometry: new THREE.BoxGeometry(1.85, 0.24, 1.25), color: '#9e6c58', elevation: 0.12 },
        { point: 'playArea', geometry: new THREE.SphereGeometry(0.24, 20, 16), color: '#db6752', elevation: 0.24 },
        { point: 'window', geometry: new THREE.BoxGeometry(1.7, 1.25, 0.08), color: '#9fc5d6', elevation: 1.75 },
        { point: 'scratchingPost', geometry: new THREE.CylinderGeometry(0.18, 0.26, 1.35, 16), color: '#b98a5c', elevation: 0.675 },
    ];

    interactionProps.forEach(({ point, geometry, color, elevation }) => {
        const prop = new THREE.Mesh(geometry, new THREE.MeshStandardMaterial({ color, roughness: 0.7 }));
        prop.position.copy(interestPoints[point]);
        prop.position.y = elevation;
        prop.castShadow = true;
        prop.receiveShadow = true;
        room.add(prop);
    });

    const roomLights = [
        { color: '#ffb85f', intensity: 95, distance: 15, position: [0, 7, 0], castsShadow: true },
        { color: '#ffc878', intensity: 58, distance: 9, position: [-7.15, 7.25, -5.15] },
    ].map(({ color, intensity, distance, position, castsShadow = false }) => {
        const light = new THREE.PointLight(color, intensity, distance, 2);
        light.position.set(...position);
        light.castShadow = castsShadow;
        scene.add(light);

        return { light, intensity };
    });
    const [{ light: roomLight }] = roomLights;
    const cameraLight = new THREE.SpotLight('#ffe5bd', 0.9, 18, 0.8, 0.45, 2);
    cameraLight.position.copy(camera.position);
    cameraLight.target.position.copy(cameraTarget);
    scene.add(cameraLight);
    scene.add(cameraLight.target);
    const hemisphereLight = new THREE.HemisphereLight('#f5d6aa', '#30251e', 2.35);
    scene.add(hemisphereLight);

    const lightingDefaults = {
        exposure: 1.12,
        hemisphereIntensity: 2.35,
    };
    let lightingLevel = 1;

    const updateLighting = () => {
        lightingLevel = Number(lightingControl.value);

        renderer.toneMappingExposure = lightingDefaults.exposure * lightingLevel;
        roomLights.forEach(({ light, intensity }) => {
            light.intensity = intensity * lightingLevel;
        });
        hemisphereLight.intensity = lightingDefaults.hemisphereIntensity * lightingLevel;
        lightingValue.textContent = `${lightingLevel.toFixed(2)}×`;
        updateCameraLight();
    };

    const updateCameraPosition = () => {
        camera.lookAt(cameraTarget);
        cameraPositionValue.textContent = `X ${camera.position.x.toFixed(2)} · Y ${camera.position.y.toFixed(2)} · Z ${camera.position.z.toFixed(2)}`;
    };

    const updateLightPosition = () => {
        lightPositionValue.textContent = `X ${roomLight.position.x.toFixed(2)} · Y ${roomLight.position.y.toFixed(2)} · Z ${roomLight.position.z.toFixed(2)}`;
    };

    const updateLightDistance = () => {
        roomLight.distance = Number(lightDistanceControl.value);
        lightDistanceValue.textContent = roomLight.distance.toFixed(1);
    };

    const updateCameraLight = () => {
        cameraLight.distance = Number(cameraLightDistanceControl.value);
        cameraLight.intensity = Number(cameraLightStrengthControl.value) * lightingLevel;
        cameraLightDistanceValue.textContent = cameraLight.distance.toFixed(1);
        cameraLightStrengthValue.textContent = cameraLightStrengthControl.value;
    };

    lightingControl.addEventListener('input', updateLighting);
    lightDistanceControl.addEventListener('input', updateLightDistance);
    cameraLightDistanceControl.addEventListener('input', updateCameraLight);
    cameraLightStrengthControl.addEventListener('input', updateCameraLight);
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
    updateLightDistance();
    updateCameraLight();

    const actionNames = {
        idle: 'Отдыхает',
        walk: 'Идёт',
        sit: 'Наблюдает',
        eat: 'Ест',
        sleep: 'Спит',
        play: 'Играет',
        lookWindow: 'Смотрит в окно',
        scratch: 'Точит когти',
    };
    const actionDurations = {
        idle: [5, 15],
        walk: [4, 7],
        sit: [5, 10],
        eat: [5, 7],
        sleep: [9, 13],
        play: [6, 9],
        lookWindow: [7, 12],
        scratch: [5, 8],
    };
    const walkTargets = [
        new THREE.Vector3(-2.1, 0, -0.25),
        new THREE.Vector3(1.85, 0, 0.95),
        new THREE.Vector3(-1.25, 0, 2.1),
        new THREE.Vector3(2.25, 0, -1.75),
    ];

    let cat;
    let mixer;
    let activeAnimation;
    let currentAction = 'idle';
    let actionElapsed = 0;
    let actionDuration = 8;
    let walkStart;
    let walkTarget;
    let animationClips = [];
    let selectedAnimation;
    let queuedAction;
    let pendingRemoteAction;

    const behaviorTargets = {
        eat: 'bowl',
        sleep: 'bed',
        play: 'playArea',
        lookWindow: 'window',
        scratch: 'scratchingPost',
        sit: 'sofa',
    };

    class PetBrain {
        constructor(needs = {}) {
            this.needs = { hunger: 20, energy: 80, happiness: 80 };
            this.lastAction = 'idle';
            this.setNeeds(needs);
        }

        setNeeds(needs) {
            ['hunger', 'energy', 'happiness'].forEach((need) => {
                if (Number.isFinite(needs[need])) {
                    this.needs[need] = THREE.MathUtils.clamp(needs[need], 0, 100);
                }
            });
        }

        advance(delta) {
            this.needs.hunger = Math.min(100, this.needs.hunger + delta / 300);
            this.needs.energy = Math.max(0, this.needs.energy - delta / 600);
            this.needs.happiness = Math.max(0, this.needs.happiness - delta / 900);
        }

        chooseAction() {
            const candidates = [
                ['eat', Math.max(1, (this.needs.hunger - 50) * 2)],
                ['sleep', Math.max(1, (45 - this.needs.energy) * 2)],
                ['play', Math.max(2, (45 - this.needs.happiness) * 2)],
                ['idle', 4],
                ['walk', 4],
                ['sit', 3],
                ['lookWindow', 3],
                ['scratch', 2],
            ].map(([action, weight]) => [action, action === this.lastAction ? weight * 0.35 : weight]);
            const totalWeight = candidates.reduce((total, [, weight]) => total + weight, 0);
            let cursor = Math.random() * totalWeight;

            for (const [action, weight] of candidates) {
                cursor -= weight;

                if (cursor <= 0) {
                    return action;
                }
            }

            return 'idle';
        }

        completeAction(action) {
            this.lastAction = action;

            if (action === 'eat') {
                this.needs.hunger = Math.max(0, this.needs.hunger - 25);
                this.needs.happiness = Math.min(100, this.needs.happiness + 3);
            }

            if (action === 'sleep') {
                this.needs.energy = Math.min(100, this.needs.energy + 25);
                this.needs.hunger = Math.min(100, this.needs.hunger + 3);
            }

            if (action === 'play') {
                this.needs.energy = Math.max(0, this.needs.energy - 12);
                this.needs.happiness = Math.min(100, this.needs.happiness + 15);
                this.needs.hunger = Math.min(100, this.needs.hunger + 4);
            }

            if (action === 'scratch') {
                this.needs.happiness = Math.min(100, this.needs.happiness + 6);
            }
        }
    }

    const petBrain = new PetBrain();

    const randomDuration = ([minimum, maximum]) => minimum + Math.random() * (maximum - minimum);
    const findAnimationClip = (clips, candidates) => clips.find((clip) => candidates.some((candidate) => clip.name.toLowerCase().includes(candidate)));

    const playAnimation = (clip) => {
        if (clip === undefined || mixer === undefined) {
            return;
        }

        const nextAnimation = mixer.clipAction(clip);

        if (nextAnimation === activeAnimation) {
            return;
        }

        nextAnimation.reset().setEffectiveTimeScale(1).setEffectiveWeight(1).fadeIn(0.35).play();
        activeAnimation?.fadeOut(0.35);
        activeAnimation = nextAnimation;
    };

    const setAction = (nextAction, clips, options = {}) => {
        currentAction = nextAction;
        actionElapsed = 0;
        actionDuration = options.duration ?? randomDuration(actionDurations[nextAction]);
        actionLabel.textContent = actionNames[nextAction];

        const defaultClip = clips[0];
        const idleClip = findAnimationClip(clips, ['idle', 'stand']) ?? defaultClip;
        const actionClips = {
            idle: idleClip,
            walk: findAnimationClip(clips, ['walk']) ?? defaultClip,
            sit: findAnimationClip(clips, ['sit', 'rest', 'look']) ?? idleClip,
            eat: findAnimationClip(clips, ['eat', 'feed']) ?? idleClip,
            sleep: findAnimationClip(clips, ['sleep', 'rest']) ?? idleClip,
            play: findAnimationClip(clips, ['play', 'jump']) ?? idleClip,
            lookWindow: findAnimationClip(clips, ['look', 'sit', 'rest']) ?? idleClip,
            scratch: findAnimationClip(clips, ['scratch', 'stand']) ?? idleClip,
        };
        playAnimation(actionClips[nextAction]);

        if (nextAction === 'walk' && cat !== undefined) {
            walkStart = cat.position.clone();
            walkTarget = options.target?.clone() ?? walkTargets[Math.floor(Math.random() * walkTargets.length)].clone();
            const direction = walkTarget.clone().sub(walkStart).setY(0);
            cat.rotation.y = Math.atan2(direction.x, direction.z);
        }
    };

    const beginBehavior = (action) => {
        if (cat === undefined || mixer === undefined) {
            return;
        }

        const point = behaviorTargets[action];

        if (point === undefined) {
            setAction(action, animationClips);

            return;
        }

        queuedAction = action;
        const target = interestPoints[point];
        const distance = cat.position.distanceTo(target);
        setAction('walk', animationClips, { target, duration: Math.max(2.5, distance / 1.15) });
    };

    const startAutonomousBehavior = () => beginBehavior(petBrain.chooseAction());

    const performRemoteAction = (action, needs) => {
        if (needs !== undefined) {
            petBrain.setNeeds(needs);
        }

        const behavior = { feed: 'eat', play: 'play', sleep: 'sleep' }[action];

        if (behavior === undefined) {
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
        actionLabel.textContent = `Анимация: ${selectedAnimation.name}`;
    });

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) {
            return;
        }

        if (event.data?.action === 'meow') {
            actionLabel.textContent = `${event.data.petName ?? 'Мурка'} мяукает`;

            return;
        }

        if (event.data?.action === 'sync-needs') {
            petBrain.setNeeds(event.data.needs ?? {});

            return;
        }

        performRemoteAction(event.data?.action, event.data?.needs);
    });

    new GLTFLoader().load(
        '/models/stripe-the-cat.glb',
        (gltf) => {
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
            cat.userData.animationClips = gltf.animations;
            animationClips = gltf.animations;
            animationClips.forEach((clip, index) => {
                const option = new Option(clip.name || `Анимация ${index + 1}`, String(index));
                animationControl.add(option);
            });
            animationControl.disabled = animationClips.length === 0;
            setAction('idle', gltf.animations);

            if (pendingRemoteAction !== undefined) {
                performRemoteAction(pendingRemoteAction);
                pendingRemoteAction = undefined;
            }
        },
        undefined,
        () => {
            actionLabel.textContent = 'Не удалось загрузить модель';
        },
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
                cat.position.lerpVectors(walkStart, walkTarget, Math.min(actionElapsed / actionDuration, 1));
            }

            if (selectedAnimation === undefined && actionElapsed >= actionDuration && currentAction === 'walk' && queuedAction !== undefined) {
                const nextAction = queuedAction;
                queuedAction = undefined;
                setAction(nextAction, cat.userData.animationClips);
            } else if (selectedAnimation === undefined && actionElapsed >= actionDuration) {
                petBrain.completeAction(currentAction);
                startAutonomousBehavior();
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
