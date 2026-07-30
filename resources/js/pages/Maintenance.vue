<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, onBeforeUnmount, ref } from 'vue';
import * as THREE from 'three';
import AppLogo from '@/components/AppLogo.vue';

const canvasContainer = ref<HTMLElement | null>(null);

let renderer: THREE.WebGLRenderer;
let scene: THREE.Scene;
let camera: THREE.PerspectiveCamera;
let animationFrameId: number;

onMounted(() => {
    if (!canvasContainer.value) return;

    // Scene setup
    scene = new THREE.Scene();

    // Minimal fog for deep corporate feel
    scene.fog = new THREE.FogExp2(0x050505, 0.001);

    // Camera setup
    camera = new THREE.PerspectiveCamera(
        60,
        window.innerWidth / window.innerHeight,
        0.1,
        1000,
    );
    camera.position.z = 40;

    // Renderer setup
    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(0x050505, 1); // Very dark gray/black corporate bg
    canvasContainer.value.appendChild(renderer.domElement);

    // Corporate 3D Element: Elegant Interlocking Rings
    const ringGroup = new THREE.Group();

    const ringMaterial = new THREE.MeshBasicMaterial({
        color: 0xdc2626, // Red 600
        transparent: true,
        opacity: 0.15,
        wireframe: true,
    });

    const solidRingMaterial = new THREE.MeshBasicMaterial({
        color: 0xef4444, // Red 500
        transparent: true,
        opacity: 0.4,
    });

    // Create 3 rings at different angles
    for (let i = 0; i < 3; i++) {
        const geometry = new THREE.TorusGeometry(12, 0.05, 16, 100);
        const ring = new THREE.Mesh(geometry, solidRingMaterial);

        const wireGeometry = new THREE.TorusGeometry(14 + i * 1.5, 2, 8, 50);
        const wireRing = new THREE.Mesh(wireGeometry, ringMaterial);

        ring.rotation.x = Math.random() * Math.PI;
        ring.rotation.y = Math.random() * Math.PI;

        wireRing.rotation.x = Math.random() * Math.PI;
        wireRing.rotation.y = Math.random() * Math.PI;

        ringGroup.add(ring);
        ringGroup.add(wireRing);
    }

    scene.add(ringGroup);

    // Refined subtle particles
    const particlesGeometry = new THREE.BufferGeometry();
    const particlesCount = 800;

    const posArray = new Float32Array(particlesCount * 3);

    for (let i = 0; i < particlesCount * 3; i++) {
        posArray[i] = (Math.random() - 0.5) * 120;
    }

    particlesGeometry.setAttribute(
        'position',
        new THREE.BufferAttribute(posArray, 3),
    );

    const particlesMaterial = new THREE.PointsMaterial({
        size: 0.1,
        color: 0xffffff,
        transparent: true,
        opacity: 0.3,
        blending: THREE.AdditiveBlending,
    });

    const particlesMesh = new THREE.Points(
        particlesGeometry,
        particlesMaterial,
    );
    scene.add(particlesMesh);

    // Mouse interaction variables
    let mouseX = 0;
    let mouseY = 0;
    let targetX = 0;
    let targetY = 0;

    const windowHalfX = window.innerWidth / 2;
    const windowHalfY = window.innerHeight / 2;

    const onDocumentMouseMove = (event: MouseEvent) => {
        mouseX = (event.clientX - windowHalfX) * 0.05;
        mouseY = (event.clientY - windowHalfY) * 0.05;
    };

    document.addEventListener('mousemove', onDocumentMouseMove);

    // Animation Loop
    const clock = new THREE.Clock();

    const animate = () => {
        animationFrameId = requestAnimationFrame(animate);

        const elapsedTime = clock.getElapsedTime();

        targetX = mouseX * 0.001;
        targetY = mouseY * 0.001;

        // Elegant slow rotation
        ringGroup.rotation.y += 0.001;
        ringGroup.rotation.x += 0.0005;

        ringGroup.children.forEach((child, index) => {
            child.rotation.x += 0.001 * (index % 2 === 0 ? 1 : -1);
            child.rotation.y += 0.0015 * (index % 2 === 0 ? -1 : 1);
        });

        particlesMesh.rotation.y -= 0.0002;

        // Smooth camera follow mouse
        camera.position.x += (mouseX * 0.02 - camera.position.x) * 0.05;
        camera.position.y += (-mouseY * 0.02 - camera.position.y) * 0.05;
        camera.lookAt(scene.position);

        renderer.render(scene, camera);
    };

    animate();

    // Resize Handler
    const handleResize = () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    };

    window.addEventListener('resize', handleResize);

    // Cleanup function
    onBeforeUnmount(() => {
        window.removeEventListener('resize', handleResize);
        document.removeEventListener('mousemove', onDocumentMouseMove);
        cancelAnimationFrame(animationFrameId);

        ringGroup.children.forEach((child) => {
            if (child instanceof THREE.Mesh) {
                child.geometry.dispose();
                child.material.dispose();
            }
        });
        particlesGeometry.dispose();
        particlesMaterial.dispose();

        if (renderer && renderer.domElement && renderer.domElement.parentNode) {
            renderer.domElement.parentNode.removeChild(renderer.domElement);
        }
        renderer.dispose();
    });
});
</script>

<template>
    <Head title="Sistem Bakımı" />

    <!-- Pure CSS Giant Corporate Typography Background -->
    <div
        class="pointer-events-none fixed inset-0 z-0 flex items-center justify-center overflow-hidden bg-[#050505] select-none"
    >
        <h1
            class="animate-pulse-slow font-sans text-[15vw] font-black tracking-tighter whitespace-nowrap text-white/[0.03] uppercase"
        >
            ERLER AVM
        </h1>
    </div>

    <!-- 3D Canvas Background -->
    <div
        ref="canvasContainer"
        class="pointer-events-none fixed inset-0 z-10 overflow-hidden mix-blend-screen"
    ></div>

    <!-- Content Overlay -->
    <div
        class="relative z-20 flex min-h-screen flex-col items-center justify-center p-4 selection:bg-red-500/30"
    >
        <!-- Main Corporate Card -->
        <div class="relative w-full max-w-3xl">
            <div
                class="relative animate-[fadeInUp_1s_ease-out] rounded-2xl border border-white/5 bg-[#0a0a0a]/80 p-10 text-center shadow-[0_0_50px_rgba(0,0,0,0.5)] backdrop-blur-2xl sm:p-16"
            >
                <!-- Logo -->
                <div class="mb-10 flex justify-center">
                    <AppLogo class="h-16 w-auto" />
                </div>

                <div class="space-y-6">
                    <div
                        class="mb-2 inline-flex items-center justify-center space-x-3 rounded-full border border-zinc-800 bg-zinc-900/50 px-5 py-2 text-xs font-semibold tracking-widest text-zinc-300 uppercase sm:text-sm"
                    >
                        <span class="relative flex h-2 w-2">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"
                            ></span>
                            <span
                                class="relative inline-flex h-2 w-2 rounded-full bg-red-500"
                            ></span>
                        </span>
                        <span>Sistem Altyapı Güncellemesi</span>
                    </div>

                    <h1
                        class="text-3xl font-light tracking-tight text-white sm:text-5xl"
                    >
                        Kalitemizi Artırmak İçin<br class="hidden sm:block" />
                        <span class="font-bold">Kısa Bir Ara Veriyoruz.</span>
                    </h1>

                    <p
                        class="mx-auto mt-6 max-w-xl text-base leading-relaxed font-light text-zinc-400 sm:text-lg"
                    >
                        Erler AVM olarak sizlere en kusursuz ve güvenilir
                        alışveriş deneyimini sunmak önceliğimizdir. Altyapı
                        iyileştirmelerimiz tamamlandığında daha güçlü bir
                        sistemle karşınızda olacağız.
                    </p>
                </div>

                <div
                    class="mt-16 flex flex-col items-center justify-between border-t border-white/5 pt-8 text-xs tracking-wide text-zinc-500 uppercase sm:flex-row"
                >
                    <p>&copy; {{ new Date().getFullYear() }} Erler AVM A.Ş.</p>
                    <p class="mt-2 sm:mt-0">Tüm Hakları Saklıdır.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-pulse-slow {
    animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%,
    100% {
        opacity: 0.03;
        transform: scale(1);
    }
    50% {
        opacity: 0.05;
        transform: scale(1.02);
    }
}
</style>
