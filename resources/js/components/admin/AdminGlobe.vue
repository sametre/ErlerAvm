<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import * as THREE from 'three';

type TrafficPoint = {
    type: 'order' | 'visitor';
    label: string;
    description: string;
    lat: number;
    lon: number;
    created_at?: string | null;
};

const props = defineProps<{
    activeVisitors: number;
    points: TrafficPoint[];
}>();

const container = ref<HTMLElement | null>(null);

let renderer: THREE.WebGLRenderer;
let scene: THREE.Scene;
let camera: THREE.PerspectiveCamera;
let globeGroup: THREE.Group;
let pointGroup: THREE.Group;
let animationId: number;
let resizeHandler: (() => void) | null = null;

function toPosition(lat: number, lon: number, radius: number): THREE.Vector3 {
    const phi = THREE.MathUtils.degToRad(90 - lat);
    const theta = THREE.MathUtils.degToRad(lon + 180);

    return new THREE.Vector3(
        -(radius * Math.sin(phi) * Math.cos(theta)),
        radius * Math.cos(phi),
        radius * Math.sin(phi) * Math.sin(theta),
    );
}

function updatePoints() {
    if (!pointGroup) return;
    pointGroup.clear();

    const orderMat = new THREE.MeshBasicMaterial({ color: 0xef4444 }); // Red 500
    const visitorMat = new THREE.MeshBasicMaterial({ color: 0x10b981 }); // Emerald 500

    props.points.slice(0, 15).forEach((point) => {
        const isOrder = point.type === 'order';
        const marker = new THREE.Mesh(
            new THREE.SphereGeometry(isOrder ? 0.05 : 0.03, 16, 16),
            isOrder ? orderMat : visitorMat,
        );
        marker.position.copy(toPosition(point.lat, point.lon, 2.05)); // slightly above globe radius 2

        // Add a glowing halo to the marker
        const halo = new THREE.Mesh(
            new THREE.SphereGeometry(isOrder ? 0.08 : 0.06, 16, 16),
            new THREE.MeshBasicMaterial({
                color: isOrder ? 0xef4444 : 0x10b981,
                transparent: true,
                opacity: 0.5,
                blending: THREE.AdditiveBlending,
            }),
        );
        halo.position.copy(marker.position);

        pointGroup.add(marker);
        pointGroup.add(halo);
    });
}

function createDotsTexture(): THREE.CanvasTexture {
    const canvas = document.createElement('canvas');
    canvas.width = 1024;
    canvas.height = 512;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });

    if (!ctx) return new THREE.CanvasTexture(canvas);

    // Fill background black
    ctx.fillStyle = '#000000';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Draw continents solid white
    ctx.fillStyle = '#ffffff';

    const drawBlob = (points: [number, number][]) => {
        ctx.beginPath();
        points.forEach(([lon, lat], index) => {
            const x = ((lon + 180) / 360) * canvas.width;
            const y = ((90 - lat) / 180) * canvas.height;
            if (index === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        });
        ctx.closePath();
        ctx.fill();
    };

    drawBlob([
        [-168, 72],
        [-125, 70],
        [-65, 48],
        [-50, 25],
        [-92, 8],
        [-125, 18],
        [-150, 45],
    ]);
    drawBlob([
        [-83, 13],
        [-50, 8],
        [-35, -12],
        [-52, -55],
        [-72, -45],
        [-80, -10],
    ]);
    drawBlob([
        [-18, 35],
        [25, 37],
        [44, 13],
        [32, -34],
        [12, -35],
        [-8, 4],
    ]);
    drawBlob([
        [-10, 72],
        [38, 70],
        [80, 55],
        [116, 50],
        [146, 28],
        [132, 5],
        [88, 6],
        [64, 24],
        [26, 35],
        [-8, 42],
    ]);
    drawBlob([
        [35, 32],
        [48, 30],
        [54, 22],
        [44, 16],
        [32, 20],
    ]);
    drawBlob([
        [112, -11],
        [154, -23],
        [146, -43],
        [116, -37],
    ]);
    drawBlob([
        [-44, 78],
        [-20, 74],
        [-18, 62],
        [-42, 60],
    ]);

    // Create an empty canvas to hold the dotted result
    const outCanvas = document.createElement('canvas');
    outCanvas.width = 2048;
    outCanvas.height = 1024;
    const outCtx = outCanvas.getContext('2d');
    if (!outCtx) return new THREE.CanvasTexture(canvas);

    outCtx.fillStyle = '#050505'; // Dark background
    outCtx.fillRect(0, 0, outCanvas.width, outCanvas.height);

    // Sample the solid image data
    const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;

    outCtx.fillStyle = '#10b981'; // Emerald dots for digital look
    const rows = 120;
    const cols = 240;

    for (let i = 0; i < rows; i++) {
        for (let j = 0; j < cols; j++) {
            const x = Math.floor((j / cols) * canvas.width);
            const y = Math.floor((i / rows) * canvas.height);
            const idx = (y * canvas.width + x) * 4;
            const r = imgData[idx];

            // If it's land
            if (r > 128) {
                const outX = (j / cols) * outCanvas.width;
                const outY = (i / rows) * outCanvas.height;
                outCtx.beginPath();
                outCtx.arc(outX, outY, 3.5, 0, Math.PI * 2);
                outCtx.fill();
            }
        }
    }

    const texture = new THREE.CanvasTexture(outCanvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.anisotropy = 4;
    return texture;
}

function initGlobe() {
    if (!container.value) return;

    scene = new THREE.Scene();

    // Camera
    camera = new THREE.PerspectiveCamera(
        45,
        container.value.clientWidth / container.value.clientHeight,
        0.1,
        1000,
    );
    camera.position.set(0, 0, 6.5);

    // Renderer
    renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(container.value.clientWidth, container.value.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.value.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);

    const directionalLight = new THREE.DirectionalLight(0xffffff, 1.0);
    directionalLight.position.set(5, 3, 5);
    scene.add(directionalLight);

    globeGroup = new THREE.Group();
    scene.add(globeGroup);

    // Digital Dotted Sphere
    const earthGeo = new THREE.SphereGeometry(2, 64, 64);
    const earthMat = new THREE.MeshStandardMaterial({
        map: createDotsTexture(),
        roughness: 0.9,
        metalness: 0.1,
        transparent: true,
        opacity: 0.9,
    });
    const earth = new THREE.Mesh(earthGeo, earthMat);
    globeGroup.add(earth);

    // Inner Core Glow
    const coreGeo = new THREE.SphereGeometry(1.95, 32, 32);
    const coreMat = new THREE.MeshBasicMaterial({
        color: 0x050505,
    });
    const core = new THREE.Mesh(coreGeo, coreMat);
    globeGroup.add(core);

    // Atmosphere Glow
    const atmosphereGeo = new THREE.SphereGeometry(2.1, 64, 64);
    const atmosphereMat = new THREE.MeshPhongMaterial({
        color: 0x10b981, // Emerald tint for atmosphere
        transparent: true,
        opacity: 0.08,
        blending: THREE.AdditiveBlending,
        side: THREE.BackSide,
    });
    const atmosphere = new THREE.Mesh(atmosphereGeo, atmosphereMat);
    scene.add(atmosphere);

    // Point Group for traffic
    pointGroup = new THREE.Group();
    globeGroup.add(pointGroup);

    // Initial tilt to look better
    globeGroup.rotation.x = 0.2;
    globeGroup.rotation.y = 1.5;

    updatePoints();

    // Animation Loop
    let time = 0;
    const animate = () => {
        animationId = requestAnimationFrame(animate);
        time += 0.01;

        // Otomatik dönüş (Automatic rotation)
        globeGroup.rotation.y += 0.002;

        // Pulse effect for pins
        if (pointGroup) {
            pointGroup.children.forEach((child, idx) => {
                if (
                    (child as THREE.Mesh).material &&
                    ((child as THREE.Mesh).material as THREE.MeshBasicMaterial)
                        .transparent
                ) {
                    const scale = 1 + Math.sin(time * 3 + idx) * 0.3;
                    child.scale.setScalar(scale);
                }
            });
        }

        renderer.render(scene, camera);
    };
    animate();

    // Resize Handler
    resizeHandler = () => {
        if (!container.value) return;
        camera.aspect =
            container.value.clientWidth / container.value.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(
            container.value.clientWidth,
            container.value.clientHeight,
        );
    };
    window.addEventListener('resize', resizeHandler);
}

onMounted(() => {
    // Biraz gecikmeli başlatarak canvas'ın layout'a oturmasını sağlayalım
    setTimeout(() => {
        initGlobe();
    }, 100);
});

onBeforeUnmount(() => {
    cancelAnimationFrame(animationId);
    if (resizeHandler) {
        window.removeEventListener('resize', resizeHandler);
    }
    if (renderer) renderer.dispose();
    if (container.value && renderer) {
        container.value.removeChild(renderer.domElement);
    }
});

watch(
    () => props.points,
    () => {
        updatePoints();
    },
    { deep: true },
);
</script>

<template>
    <div
        class="relative flex h-full min-h-[400px] w-full items-center justify-center overflow-hidden rounded-2xl bg-slate-900"
    >
        <!-- Radial Gradient for digital depth -->
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-emerald-900/20 via-slate-900 to-slate-900"
        ></div>

        <div
            ref="container"
            class="absolute inset-0 z-10 cursor-grab active:cursor-grabbing"
        ></div>

        <div
            class="pointer-events-none absolute top-4 left-4 z-20 rounded-xl border border-white/10 bg-black/60 px-4 py-3 text-white shadow-lg backdrop-blur-md"
        >
            <div class="mb-1 flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span
                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"
                    ></span>
                    <span
                        class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"
                    ></span>
                </span>
                <p
                    class="text-xs font-semibold tracking-wider text-emerald-300 uppercase"
                >
                    Aktif Ziyaretçi
                </p>
            </div>
            <p class="font-mono text-3xl font-black">{{ activeVisitors }}</p>
        </div>
    </div>
</template>
