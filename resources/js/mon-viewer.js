// ─────────────────────────────────────────────────────────────
// Viewer 3D cho TỪNG MÓN — modal, LAZY: chỉ khởi tạo Three.js &
// tải model khi khách bấm "Xem 3D". Tải .glb từ CDN (R2).
// Dùng: window.viewMon3D(url, tenMon)
// ─────────────────────────────────────────────────────────────
import * as THREE from 'three';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { DRACOLoader } from 'three/examples/jsm/loaders/DRACOLoader.js';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';

let modal, canvas, titleEl, loadingEl;
let renderer, scene, camera, controls, loader;
let currentModel = null, running = false, rafId = null;

function buildModal() {
    modal = document.createElement('div');
    modal.style.cssText = 'position:fixed;inset:0;z-index:80;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.55);padding:16px';
    modal.innerHTML = `
      <div style="position:relative;width:100%;max-width:520px;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3)">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #eee">
          <h3 data-title style="margin:0;font-size:16px;font-weight:700;color:#1A1A1A"></h3>
          <button data-close style="border:0;background:#F2F2F2;width:32px;height:32px;border-radius:999px;font-size:18px;cursor:pointer;color:#522C25">&times;</button>
        </div>
        <div style="position:relative;background:#F6F3F2">
          <div data-canvas style="width:100%;height:62vh;max-height:460px"></div>
          <div data-loading style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#8B5A2B;font-size:14px">Đang tải mô hình 3D…</div>
        </div>
        <p style="margin:0;padding:10px 18px;font-size:12px;color:#522C25;background:#FCFAFA">Kéo để xoay · cuộn để phóng to · chạm 2 ngón để di chuyển.</p>
      </div>`;
    document.body.appendChild(modal);
    canvas = modal.querySelector('[data-canvas]');
    titleEl = modal.querySelector('[data-title]');
    loadingEl = modal.querySelector('[data-loading]');
    modal.querySelector('[data-close]').addEventListener('click', close);
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
}

function initScene() {
    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    canvas.appendChild(renderer.domElement);

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(45, 1, 0.01, 1000);

    scene.add(new THREE.HemisphereLight(0xffffff, 0x666666, 1.1));
    const dir = new THREE.DirectionalLight(0xffffff, 1.0);
    dir.position.set(3, 6, 4);
    scene.add(dir);

    controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 1.2;

    const draco = new DRACOLoader();
    draco.setDecoderPath('/draco/');
    loader = new GLTFLoader();
    loader.setDRACOLoader(draco);

    window.addEventListener('resize', onResize);
}

function onResize() {
    if (!renderer || modal.style.display === 'none') return;
    const w = canvas.clientWidth, h = canvas.clientHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
}

function clearModel() {
    if (!currentModel) return;
    scene.remove(currentModel);
    currentModel.traverse((o) => {
        if (o.geometry) o.geometry.dispose();
        if (o.material) (Array.isArray(o.material) ? o.material : [o.material]).forEach((m) => m.dispose());
    });
    currentModel = null;
}

function loadModel(url) {
    loadingEl.style.display = 'flex';
    loadingEl.textContent = 'Đang tải mô hình 3D…';
    loader.load(url, (gltf) => {
        clearModel();
        currentModel = gltf.scene;
        scene.add(currentModel);

        // Canh giữa + fit camera
        const box = new THREE.Box3().setFromObject(currentModel);
        const size = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());
        currentModel.position.sub(center);
        const r = Math.max(size.x, size.y, size.z) || 1;
        camera.position.set(r * 1.1, r * 0.8, r * 1.4);
        camera.near = r / 100; camera.far = r * 50; camera.updateProjectionMatrix();
        controls.target.set(0, 0, 0); controls.update();

        loadingEl.style.display = 'none';
    }, undefined, (err) => {
        console.error('Lỗi tải model món:', err);
        loadingEl.textContent = 'Không tải được mô hình 3D.';
    });
}

function animate() {
    if (!running) return;
    rafId = requestAnimationFrame(animate);
    controls.update();
    renderer.render(scene, camera);
}

function close() {
    if (!modal) return;
    modal.style.display = 'none';
    running = false;
    if (rafId) cancelAnimationFrame(rafId);
}

window.viewMon3D = function (url, name) {
    if (!url) return;
    if (!modal) buildModal();
    if (!renderer) initScene();
    titleEl.textContent = name || 'Mô hình 3D';
    modal.style.display = 'flex';
    requestAnimationFrame(() => {
        onResize();
        loadModel(url);
        running = true;
        animate();
    });
};
