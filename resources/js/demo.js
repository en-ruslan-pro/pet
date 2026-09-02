import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

const container = document.querySelector('#pet-demo');
const actionLabel = document.querySelector('#pet-action');

if (container === null || actionLabel === null) {
    throw new Error('The Virtual Pet TV demo container is missing.');
}

if (!window.WebGLRenderingContext) {
    container.innerHTML = '<div class="demo-error">Для запуска демонстрации нужен браузер с поддержкой WebGL.</div>';
} else {
    const scene = new THREE.Scene();
    scene.background = new THREE.Color('#211f1b');
    scene.fog = new THREE.Fog('#211f1b', 10, 24);

    const camera = new THREE.PerspectiveCamera(42, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.set(8.5, 5.8, 9.5);
    camera.lookAt(1.2, 1.3, -0.3);

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
        { file: 'shelf', position: [1.3, -6.28], span: 1.7, elevation: 3.1 },
        { file: 'shelf', position: [4.2, -6.28], span: 1.7, elevation: 3.1 },
        { file: 'simple_desk_A', position: [-7.25, -2.3], rotation: Math.PI / 2, span: 2.6 },
        { file: 'simple_chair', position: [-5.7, -2.3], rotation: -Math.PI / 2, span: 1.15 },
        { file: 'couch', position: [3.7, -4.65], rotationX: Math.PI / 2, span: 3.7 },
        { file: 'armchair', position: [-7.15, 1.05], rotationZ: Math.PI / 2, span: 1.55 },
        { file: 'simple_library_A', position: [6.8, -5.35], rotation: -Math.PI / 2, span: 2.15 },
        { file: 'lamp', position: [5.8, -2.95], span: 1.25 },
        { file: 'plant', position: [-6.9, -4.8], rotation: Math.PI / 4, span: 1.45 },
    ];

    const roomLoader = new GLTFLoader();

    const addRoomAsset = ({ file, position, rotation = 0, rotationX = 0, rotationZ = 0, span, elevation = 0 }) => {
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

    const warmLight = new THREE.PointLight('#ffb85f', 95, 15, 2);
    warmLight.position.set(-3.5, 5.5, -3.5);
    warmLight.castShadow = true;
    scene.add(warmLight);
    scene.add(new THREE.HemisphereLight('#f5d6aa', '#30251e', 2.35));

    const actionNames = { idle: 'Отдыхает', walk: 'Гуляет', sit: 'Наблюдает' };
    const actionDurations = { idle: [5, 15], walk: [4, 7], sit: [5, 10] };
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

    const setAction = (nextAction, clips) => {
        currentAction = nextAction;
        actionElapsed = 0;
        actionDuration = randomDuration(actionDurations[nextAction]);
        actionLabel.textContent = actionNames[nextAction];

        const defaultClip = clips[0];
        const idleClip = findAnimationClip(clips, ['idle', 'stand']) ?? defaultClip;
        const actionClips = {
            idle: idleClip,
            walk: findAnimationClip(clips, ['walk']) ?? defaultClip,
            sit: findAnimationClip(clips, ['sit', 'rest', 'look']) ?? idleClip,
        };
        playAnimation(actionClips[nextAction]);

        if (nextAction === 'walk' && cat !== undefined) {
            walkStart = cat.position.clone();
            walkTarget = walkTargets[Math.floor(Math.random() * walkTargets.length)].clone();
            const direction = walkTarget.clone().sub(walkStart).setY(0);
            cat.rotation.y = Math.atan2(direction.x, direction.z);
        }
    };

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
            setAction('idle', gltf.animations);
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
            actionElapsed += delta;

            if (currentAction === 'walk' && walkStart !== undefined && walkTarget !== undefined) {
                cat.position.lerpVectors(walkStart, walkTarget, Math.min(actionElapsed / actionDuration, 1));
            }

            if (actionElapsed >= actionDuration) {
                const availableActions = ['idle', 'walk', 'sit'].filter((action) => action !== currentAction);
                setAction(availableActions[Math.floor(Math.random() * availableActions.length)], cat.userData.animationClips);
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
