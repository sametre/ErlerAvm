<script setup lang="ts">
import { onMounted, ref, onBeforeUnmount } from 'vue';
import * as THREE from 'three';

const container = ref<HTMLElement | null>(null);
const isVisible = ref(false);
const showCampaign = ref(false);

onMounted(() => {
    if (!container.value) return;

    // Check if the user has already seen the surprise box
    const hasSeenSurprise = localStorage.getItem('hasSeenSurpriseBox');

    if (!hasSeenSurprise) {
        isVisible.value = true;
        // Mark as seen for future visits
        localStorage.setItem('hasSeenSurpriseBox', 'true');

        setTimeout(() => {
            init3DScene();
        }, 100);
    }
});

let animationId: number;

function closeAll() {
    isVisible.value = false;
    showCampaign.value = false;
}

function init3DScene() {
    const scene = new THREE.Scene();

    // Add realistic lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);

    const spotLight = new THREE.SpotLight(0xffffff, 3);
    spotLight.position.set(5, 12, 5);
    spotLight.angle = Math.PI / 4;
    spotLight.penumbra = 0.5;
    spotLight.castShadow = true;
    spotLight.shadow.mapSize.width = 1024;
    spotLight.shadow.mapSize.height = 1024;
    scene.add(spotLight);

    const fillLight = new THREE.DirectionalLight(0xffffff, 1);
    fillLight.position.set(-5, 0, 5);
    scene.add(fillLight);

    // Responsive camera setup
    const isMobile = window.innerWidth < 768;
    const cameraZ = isMobile ? 8 : 6;
    const camera = new THREE.PerspectiveCamera(
        60,
        window.innerWidth / window.innerHeight,
        0.1,
        1000,
    );
    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });

    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.2;

    if (container.value) {
        container.value.appendChild(renderer.domElement);
    }

    // Realistic Materials
    const boxMaterial = new THREE.MeshPhysicalMaterial({
        color: 0xcc0000, // Deep Premium Red
        metalness: 0.1,
        roughness: 0.4,
        clearcoat: 1.0,
        clearcoatRoughness: 0.2,
    });

    const ribbonMaterial = new THREE.MeshPhysicalMaterial({
        color: 0xffd700, // Shiny Golden Yellow
        metalness: 0.6,
        roughness: 0.2,
        clearcoat: 1.0,
        clearcoatRoughness: 0.1,
    });

    // Box Group
    const giftGroup = new THREE.Group();
    scene.add(giftGroup);

    // Box Body
    const bodyGeometry = new THREE.BoxGeometry(2, 1.8, 2);
    const body = new THREE.Mesh(bodyGeometry, boxMaterial);
    body.position.y = -0.1;
    body.castShadow = true;
    giftGroup.add(body);

    // Body Ribbons
    const bodyRibbon1 = new THREE.Mesh(
        new THREE.BoxGeometry(2.04, 1.84, 0.4),
        ribbonMaterial,
    );
    bodyRibbon1.position.y = -0.1;
    const bodyRibbon2 = new THREE.Mesh(
        new THREE.BoxGeometry(0.4, 1.84, 2.04),
        ribbonMaterial,
    );
    bodyRibbon2.position.y = -0.1;
    giftGroup.add(bodyRibbon1);
    giftGroup.add(bodyRibbon2);

    // Box Lid Group (will rotate to open)
    const lidGroup = new THREE.Group();
    lidGroup.position.set(0, 0.8, -1); // Hinge position at the back
    giftGroup.add(lidGroup);

    // Lid Mesh
    const lidGeometry = new THREE.BoxGeometry(2.1, 0.4, 2.1);
    const lid = new THREE.Mesh(lidGeometry, boxMaterial);
    lid.position.set(0, 0.2, 1); // Offset relative to hinge
    lid.castShadow = true;
    lidGroup.add(lid);

    // Lid Ribbons
    const lidRibbon1 = new THREE.Mesh(
        new THREE.BoxGeometry(2.14, 0.44, 0.4),
        ribbonMaterial,
    );
    lidRibbon1.position.set(0, 0.2, 1);
    const lidRibbon2 = new THREE.Mesh(
        new THREE.BoxGeometry(0.4, 0.44, 2.14),
        ribbonMaterial,
    );
    lidRibbon2.position.set(0, 0.2, 1);
    lidGroup.add(lidRibbon1);
    lidGroup.add(lidRibbon2);

    // Big Bow on Lid
    const bowGeom = new THREE.TorusGeometry(0.4, 0.12, 16, 48);
    const bow1 = new THREE.Mesh(bowGeom, ribbonMaterial);
    bow1.position.set(0.3, 0.7, 1);
    bow1.rotation.y = Math.PI / 2;
    bow1.rotation.x = Math.PI / 4;
    lidGroup.add(bow1);

    const bow2 = new THREE.Mesh(bowGeom, ribbonMaterial);
    bow2.position.set(-0.3, 0.7, 1);
    bow2.rotation.y = Math.PI / 2;
    bow2.rotation.x = -Math.PI / 4;
    lidGroup.add(bow2);

    // Surprise light inside the box
    const innerLight = new THREE.PointLight(0xfff0b3, 0, 8); // Golden light
    innerLight.position.set(0, 0.5, 0);
    giftGroup.add(innerLight);

    // Particle system for explosion effect when opened
    const particleCount = 100;
    const particleGeom = new THREE.BufferGeometry();
    const posArr = new Float32Array(particleCount * 3);
    const velArr = new Float32Array(particleCount * 3);
    const colArr = new Float32Array(particleCount * 3);

    for (let i = 0; i < particleCount * 3; i += 3) {
        posArr[i] = 0;
        posArr[i + 1] = 0;
        posArr[i + 2] = 0;
        velArr[i] = (Math.random() - 0.5) * 0.4;
        velArr[i + 1] = Math.random() * 0.6 + 0.2; // shoot up
        velArr[i + 2] = (Math.random() - 0.5) * 0.4;

        const colors = [0xffd700, 0xffffff, 0xff4500]; // gold, white, orange
        const c = new THREE.Color(
            colors[Math.floor(Math.random() * colors.length)],
        );
        colArr[i] = c.r;
        colArr[i + 1] = c.g;
        colArr[i + 2] = c.b;
    }
    particleGeom.setAttribute('position', new THREE.BufferAttribute(posArr, 3));
    particleGeom.setAttribute('color', new THREE.BufferAttribute(colArr, 3));

    const particleMat = new THREE.PointsMaterial({
        size: 0.15,
        vertexColors: true,
        transparent: true,
        opacity: 0, // initially hidden
        blending: THREE.AdditiveBlending,
    });
    const particles = new THREE.Points(particleGeom, particleMat);
    scene.add(particles);

    camera.position.z = cameraZ;
    camera.position.y = isMobile ? 4 : 3;
    camera.lookAt(0, 0, 0);

    let scale = 0;
    let rotationSpeed = 0.05;
    giftGroup.scale.set(0, 0, 0);

    let phase = 'intro'; // intro -> spin -> wait -> open -> campaign
    let timer = 0;
    let lidAngle = 0;

    const animate = () => {
        if (!isVisible.value) return;
        animationId = requestAnimationFrame(animate);
        timer += 0.016;

        if (phase === 'intro') {
            scale += (1 - scale) * 0.08;
            giftGroup.scale.set(scale, scale, scale);
            giftGroup.rotation.y += rotationSpeed;
            if (scale > 0.99 && timer > 1.5) {
                phase = 'spin';
                timer = 0;
            }
        } else if (phase === 'spin') {
            giftGroup.rotation.y += rotationSpeed;
            // bounce
            giftGroup.position.y = Math.sin(timer * 12) * 0.15;
            if (timer > 1.2) {
                phase = 'wait';
                timer = 0;
            }
        } else if (phase === 'wait') {
            giftGroup.rotation.y += (0 - giftGroup.rotation.y) * 0.1; // align to front
            giftGroup.position.y += (0 - giftGroup.position.y) * 0.1;
            if (timer > 0.5) {
                phase = 'open';
                timer = 0;
                particleMat.opacity = 1; // start particles
            }
        } else if (phase === 'open') {
            // Open lid backwards
            if (lidAngle > -Math.PI * 0.65) {
                lidAngle -= 0.06;
                lidGroup.rotation.x = lidAngle;
                innerLight.intensity += 0.5; // Glow from inside
            }

            // Animate particles
            const positions = particles.geometry.attributes.position
                .array as Float32Array;
            for (let i = 0; i < particleCount * 3; i += 3) {
                positions[i] += velArr[i];
                positions[i + 1] += velArr[i + 1];
                positions[i + 2] += velArr[i + 2];
                velArr[i + 1] -= 0.015; // gravity
            }
            particles.geometry.attributes.position.needsUpdate = true;
            particleMat.opacity -= 0.01;

            if (timer > 1.5) {
                phase = 'campaign';
            }
        } else if (phase === 'campaign') {
            showCampaign.value = true;
            cancelAnimationFrame(animationId);
            renderer.dispose();
            return;
        }

        renderer.render(scene, camera);
    };

    animate();

    const handleResize = () => {
        const _isMobile = window.innerWidth < 768;
        camera.position.z = _isMobile ? 8 : 6;
        camera.position.y = _isMobile ? 4 : 3;
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    };
    window.addEventListener('resize', handleResize);

    onBeforeUnmount(() => {
        window.removeEventListener('resize', handleResize);
        cancelAnimationFrame(animationId);
        renderer.dispose();
    });
}
</script>

<template>
    <div
        v-if="isVisible"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md transition-opacity duration-1000"
    >
        <!-- WebGL Container -->
        <div
            ref="container"
            class="absolute inset-0"
            :class="
                showCampaign
                    ? 'opacity-0 transition-opacity duration-1000'
                    : 'opacity-100'
            "
        ></div>

        <!-- Text before opening -->
        <div
            v-if="!showCampaign"
            class="pointer-events-none absolute bottom-20 animate-pulse text-xl font-bold tracking-widest text-white/80 drop-shadow-lg"
        >
            SİZE ÖZEL SÜRPRİZİMİZ HAZIRLANIYOR...
        </div>

        <!-- Campaign Modal overlay -->
        <div
            v-if="showCampaign"
            class="relative z-10 mx-4 w-full max-w-lg transform animate-in overflow-hidden rounded-2xl bg-white p-8 text-center shadow-2xl transition-all duration-700 zoom-in-90 fade-in"
        >
            <!-- decorative background -->
            <div
                class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-primary/10 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-24 -left-24 h-48 w-48 rounded-full bg-yellow-400/20 blur-3xl"
            ></div>

            <div class="relative">
                <div
                    class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-yellow-100 shadow-inner"
                >
                    <svg
                        class="h-10 w-10 text-yellow-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"
                        />
                    </svg>
                </div>

                <h2 class="mb-2 text-3xl font-black text-slate-900">
                    Tebrikler! 🎉
                </h2>
                <p class="mb-6 leading-relaxed text-slate-600">
                    İlk ziyaretinize özel, tüm mobilya ve beyaz eşya
                    alışverişlerinizde geçerli
                    <strong>%15 İndirim</strong> kazandınız!
                </p>

                <div
                    class="mb-6 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-100 p-4"
                >
                    <span
                        class="font-mono text-lg font-bold tracking-wider text-slate-800"
                        >HOSGELDIN15</span
                    >
                    <button
                        class="text-sm font-bold text-primary hover:underline"
                    >
                        Kodu Kopyala
                    </button>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="closeAll"
                        class="flex-1 rounded-xl bg-slate-100 px-4 py-3 font-bold text-slate-700 transition-colors hover:bg-slate-200"
                    >
                        Kapat
                    </button>
                    <a
                        href="/arama"
                        @click="closeAll"
                        class="flex-1 rounded-xl bg-primary px-4 py-3 font-bold text-white shadow-lg shadow-primary/30 transition-colors hover:bg-primary/90"
                    >
                        Alışverişe Başla
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
