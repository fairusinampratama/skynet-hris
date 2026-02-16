@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #video, #canvas { transform: scaleX(-1); } /* Mirror effect */
    </style>
@endpush

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Daily Attendance
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Verify your identity and location to check in or out.
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
             <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                {{ now()->toFormattedDateString() }}
            </span>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 p-4 mb-6 border-l-4 border-green-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($error_message)
        <div class="rounded-md bg-red-50 p-4 mb-6 border-l-4 border-red-400">
            <div class="flex">
                <div class="flex-shrink-0">
                   <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ $error_message }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($hasCheckedIn && !$hasCheckedOut)
        <!-- Check Out State -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900">You are currently Checked In</h3>
                <div class="mt-2 text-sm text-gray-500">
                    <p>Check-in time: <span class="font-medium text-gray-900">{{ $checkInTime }}</span></p>
                </div>
                
                <!-- Work Summary (Optional for everyone) -->
                <div class="mt-4">
                    <label for="workSummary" class="block text-sm font-medium text-gray-700">Work Summary <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <div class="mt-1">
                        <textarea wire:model="workSummary" id="workSummary" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Briefly describe what you did today..."></textarea>
                    </div>
                    @error('workSummary') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                
                <div class="mt-5">
                    <button wire:click="checkOut" type="button" class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                        Check Out
                    </button>
                </div>
            </div>
        </div>
    
    @elseif ($hasCheckedIn && $hasCheckedOut)
        <!-- Completed State -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                 <h3 class="text-base font-semibold leading-6 text-gray-900">Attendance Completed for Today</h3>
                 <div class="mt-4 border-t border-gray-100 pt-4 dl-horizontal">
                     <p class="text-sm text-gray-500">Check In: <span class="font-medium text-gray-900">{{ $checkInTime }}</span></p>
                     <p class="text-sm text-gray-500">Check Out: <span class="font-medium text-gray-900">{{ $checkOutTime }}</span></p>
                 </div>
            </div>
        </div>

    @else
        @if(!$hasEnrolledFace)
            <!-- Unregistered State -->
            <div class="rounded-md bg-yellow-50 p-6 border-l-4 border-yellow-400">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-yellow-800">Face Registration Required</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>You must register your face data before you can check in. Please go to your profile to complete the enrollment.</p>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('profile') }}" class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-600">
                                Go to Profile Registration
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Check In Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2" wire:ignore>
                <!-- Camera Column -->
                <div class="bg-white shadow rounded-lg p-6 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">1. Face Verification</h3>
                        <div id="loading-models" class="text-xs text-yellow-600 font-medium">Loading AI Models...</div>
                    </div>
                    
                    <div id="video-wrapper" class="relative bg-black rounded-lg overflow-hidden aspect-[4/3] w-full">
                        <video id="video" class="w-full h-full object-cover" autoplay playsinline muted></video>
                        <canvas id="canvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
                        
                        <!-- Overlays -->
                        <div id="face-status" class="absolute top-4 right-4 bg-gray-800/80 text-white px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-sm transition-all duration-300">
                            Camera Starting...
                        </div>
                    </div>
                    
                    <div class="mt-4 text-xs text-center text-gray-500">
                        <p>Position your face within the frame. Ensure good lighting.</p>
                    </div>
                </div>

                <!-- Map Column -->
                <div class="bg-white shadow rounded-lg p-6 flex flex-col">
                     <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">2. Location Verification</h3>
                        <div id="location-status" class="text-xs text-gray-500 font-medium">Locating...</div>
                    </div>
                    
                    <div id="map" class="h-64 sm:h-80 w-full rounded-lg bg-gray-100 z-0 border border-gray-200"></div> 
                    
                    <div class="mt-4 grid grid-cols-2 gap-4 text-xs text-gray-600">
                         <div class="bg-gray-50 p-2 rounded">
                             <span class="block text-gray-400 uppercase tracking-wider text-[10px]">Latitude</span>
                             <span class="font-mono font-medium" id="disp-lat">-</span>
                         </div>
                         <div class="bg-gray-50 p-2 rounded">
                             <span class="block text-gray-400 uppercase tracking-wider text-[10px]">Longitude</span>
                             <span class="font-mono font-medium" id="disp-long">-</span>
                         </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Action Button -->
            <div class="mt-8 flex justify-end">
                 <button id="capture-btn" disabled class="disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto inline-flex justify-center items-center rounded-md bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">
                    <svg class="mr-2 -ml-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.247-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.077.898-.512.898h-2.65c-.958 0-1.838-.49-2.29-1.272a9.1 9.1 0 01-1.082-3.868 2.25 2.25 0 012.25-2.25h1.944a4.49 4.49 0 001.077-.129l2.894-.964" />
                    </svg>
                    Authenticate & Check In
                 </button>
            </div>
        @endif
    @endif
</div>


@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    if (typeof faceapi === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js';
        script.defer = true;
        script.onload = () => {
            // Dispatch event when loaded so initAttendance can proceed if it was waiting
            document.dispatchEvent(new Event('face-api-loaded'));
        };
        document.head.appendChild(script);
    }
</script>

<script>
    document.addEventListener('livewire:navigating', cleanupAttendance);
    document.addEventListener('livewire:navigated', initAttendance);
    document.addEventListener('DOMContentLoaded', initAttendance);

    function cleanupAttendance() {
        if (window.attendanceWatchId) {
            navigator.geolocation.clearWatch(window.attendanceWatchId);
            window.attendanceWatchId = null;
        }
        if (window.attendanceInterval) {
            clearInterval(window.attendanceInterval);
            window.attendanceInterval = null;
        }

        // Check if Leaflet is defined before using L
        if (typeof L !== 'undefined' && window.attendanceMap) {
            try {
                window.attendanceMap.remove();
            } catch (e) {
                console.warn('Map cleanup error:', e);
            }
            window.attendanceMap = null;
        }

        // Cleanup DOM reference reset - only if L is defined
        if (typeof L !== 'undefined') {
            const container = L.DomUtil.get('map');
            if (container) {
                container._leaflet_id = null;
            }
        }
    }

    function initAttendance() {
        const video = document.getElementById('video');
        const mapEl = document.getElementById('map');
        
        if (!video && !mapEl) return;

        // Cleanup any lingering state before starting
        cleanupAttendance();

        // If we are on the attendance page, ensure video/map elements are handled
        if (!video) return;

        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('capture-btn');
        const statusDiv = document.getElementById('face-status');
        const loadingDiv = document.getElementById('loading-models');
        const locationStatus = document.getElementById('location-status');
        
        let map, marker, circle;
        let isModelLoaded = false;
        let isCameraReady = false;
        let isLocationReady = false;

        // 1. Initialize Map
        function initMap() {
            if (typeof L === 'undefined') {
                // Wait for Leaflet to load
                setTimeout(initMap, 100);
                return;
            }

            try {
                const container = document.getElementById('map');
                if (!container) return;

                map = L.map('map').setView([-6.200000, 106.816666], 13);
                window.attendanceMap = map;

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; contributors'
                }).addTo(map);

                // Handle size issues during Livewire transitions
                setTimeout(() => {
                    if (window.attendanceMap) {
                        window.attendanceMap.invalidateSize();
                    }
                }, 100);

                // Re-check size on interactions to prevent renderer crashes
                map.on('moversize', () => {
                   map.invalidateSize();
                });
                
                // Fix for the 'x' undefined error on drag
                // This usually happens when the map container is hidden/shown dynamically
                const mapElement = map.getContainer();
                const resizeObserver = new ResizeObserver(() => {
                    if (map && map.getContainer()) {
                        map.invalidateSize();
                    }
                });
                resizeObserver.observe(mapElement);
            } catch (e) {
                console.error('Leaflet init error:', e);
            }
        }

        // 2. Get Geolocation
        function startLocation() {
            if (!navigator.geolocation) {
                if (locationStatus) locationStatus.innerText = "GeoAPI not supported";
                return;
            }

            window.attendanceWatchId = navigator.geolocation.watchPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const long = position.coords.longitude;
                    const acc = position.coords.accuracy;
                    
                    // Extra safety: check if we are still on a page that needs this
                    const latEl = document.getElementById('disp-lat');
                    if (!latEl) {
                        navigator.geolocation.clearWatch(window.attendanceWatchId);
                        return;
                    }

                    const longEl = document.getElementById('disp-long');
                    if (latEl) latEl.innerText = lat.toFixed(6);
                    if (longEl) longEl.innerText = long.toFixed(6);
                    
                    if (typeof L !== 'undefined' && window.attendanceMap) {
                        try {
                            const map = window.attendanceMap;
                            const container = map.getContainer();
                            
                            // Essential: Check if map is visible and sized
                            if (container && container.offsetParent !== null) {
                                map.invalidateSize(); // Ensure size is correct
                                
                                if (marker) map.removeLayer(marker);
                                if (circle) map.removeLayer(circle);
                                
                                // Validate numbers
                                if (isFinite(lat) && isFinite(long)) {
                                    marker = L.marker([lat, long]).addTo(map);
                                    if (isFinite(acc) && acc > 0) {
                                        circle = L.circle([lat, long], { radius: acc }).addTo(map);
                                    }
                                    map.setView([lat, long], 16);
                                }
                            }
                        } catch (e) {
                            // Suppress map errors
                        }
                    }
                    
                    // Update Livewire
                    @this.set('latitude', lat, true);
                    @this.set('longitude', long, true);
                    @this.set('accuracy', acc, true);
                    
                    isLocationReady = true;
                    if (locationStatus) {
                        locationStatus.innerText = "GPS Active"; // Simplified
                        locationStatus.className = "text-green-600 text-xs font-bold";
                    }
                    checkReady();
                },
                (err) => {
                    if (locationStatus) {
                        locationStatus.innerText = "Error: " + err.message;
                        locationStatus.className = "text-red-500 text-xs font-bold";
                    }
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        }

        // Simplified Verification Mode
        window.isCapturing = false;

        // 3. Start Camera & Face API
        async function startSystem() {
            try {
                if (typeof faceapi === 'undefined') {
                    await new Promise(resolve => {
                        if (typeof faceapi !== 'undefined') return resolve();
                        document.addEventListener('face-api-loaded', resolve, { once: true });
                        setTimeout(resolve, 3000); 
                    });
                }

                if (typeof faceapi === 'undefined') return;

                const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL), 
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                    // faceExpressionNet removed as liveness check is disabled
                ]);
                
                isModelLoaded = true;
                if (loadingDiv) {
                    loadingDiv.innerText = "AI Ready";
                    loadingDiv.className = "text-xs text-green-600 font-bold";
                }

                const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                if (video) video.srcObject = stream;
                
                video.addEventListener('play', () => {
                    isCameraReady = true;
                    
                    window.attendanceInterval = setInterval(async () => {
                        if (!isModelLoaded || !video || video.paused || video.ended) return;
                        
                        // UPGRADE: Use SsdMobilenetv1Options for better detection matching Enrollment
                        const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 });
                        
                        const detections = await faceapi.detectSingleFace(video, options)
                            .withFaceLandmarks()
                            .withFaceDescriptor(); // Get descriptor here to reuse
                        
                        // Canvas Drawing ... (omitted for brevity, assume keeps existing drawing logic but adapted if needed)
                         const canvas = document.getElementById('canvas');
                        if (canvas) {
                            const displaySize = { width: video.videoWidth, height: video.videoHeight };
                            faceapi.matchDimensions(canvas, displaySize);
                            
                            if (detections) {
                                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                                // faceapi.draw.drawDetections(canvas, resizedDetections); // REMOVED default drawing
                                
                                const quality = validateFaceQuality(detections, video.videoWidth, video.videoHeight);
                                drawCustomViewfinder(canvas, resizedDetections.detection.box, quality.valid);
                            } else {
                                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                            }
                        }

                        if (!statusDiv) return;

                        if (detections) {
                             const quality = validateFaceQuality(detections, video.videoWidth, video.videoHeight);
                             
                             if (quality.valid) {
                                 // Manual Mode: Enable Button, Show "Ready"
                                 statusDiv.innerText = "Ready to Check In";
                                 statusDiv.className = "absolute top-4 right-4 bg-green-600 text-white px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-sm shadow-lg animate-pulse";
                                 captureBtn.disabled = false;
                                 captureBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                             } else {
                                 // Too far/bad light? Show reason but maybe still allow button if close enough
                                 statusDiv.innerText = quality.reason; 
                                 statusDiv.className = "absolute top-4 right-4 bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-sm shadow-lg";
                                 
                                 // Allow capture anyway if face is found (User Request: "No need to be so accurate")
                                 // Only block if NO Face or Extremely Bad
                                 if (quality.reason !== 'Face Unclear') {
                                     captureBtn.disabled = false;
                                 }
                             }
                        } else {
                             statusDiv.innerText = "No Face Found";
                             statusDiv.className = "absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-sm";
                             captureBtn.disabled = true;
                        }
                    }, 500);
                });

            } catch (err) {
                if (loadingDiv) {
                    loadingDiv.innerText = "Error Loading AI";
                    loadingDiv.className = "text-xs text-red-600 font-bold";
                }
            }
        }

        // Removed handleLivenessChallenge - Simplified Flow

        function validateFaceQuality(detection, videoWidth, videoHeight) {
            const { box } = detection.detection;
            const score = detection.detection.score;
            
            // 1. Size Check (Relaxed)
            if (box.width < 50 || box.height < 50) return { valid: false, reason: 'Move Closer' }; // Was 100
            if (score < 0.40) return { valid: false, reason: 'Face Unclear' }; // Was 0.50

            // 2. Centering Check (Significantly Relaxed)
            // User can be anywhere in the frame mostly
            const centerX = box.x + (box.width / 2);
            const centerY = box.y + (box.height / 2);
            
            // 3. Lighting Check (Relaxed)
            // Just warn, don't block
            // const brightness = checkBrightness();
            // if (brightness < 20) return { valid: false, reason: 'Too Dark' }; 

            return { valid: true, reason: 'Ready' };
        }

        function checkBrightness() {
            const video = document.getElementById('video');
            if (!video) return 128;
            
            const canvas = document.createElement('canvas');
            canvas.width = 50; 
            canvas.height = 50;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, 50, 50);
            
            const imageData = ctx.getImageData(0, 0, 50, 50);
            const data = imageData.data;
            let r, g, b, avg;
            let colorSum = 0;

            for (let x = 0, len = data.length; x < len; x += 4) {
                r = data[x];
                g = data[x + 1];
                b = data[x + 2];
                avg = Math.floor((r + g + b) / 3);
                colorSum += avg;
            }

            return Math.floor(colorSum / (50 * 50));
        }

        function checkReady() {
            if (isLocationReady && isModelLoaded) {
                captureBtn.disabled = false;
            }
        }

        // 4. Capture & Submit (Manual Trigger Backup)
        captureBtn.addEventListener('click', async () => {
             if (captureBtn.disabled || window.isCapturing) return;
             
             window.isCapturing = true;
             captureBtn.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...`;
             statusDiv.innerText = "Capturing...";
             statusDiv.className = "absolute top-4 right-4 bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-sm shadow-lg animate-pulse";
             performCapture(null);
        });

        async function performCapture(existingDetection) {
             try {
                 let detection = existingDetection;

                 // Only detect if NOT provided (Manual click case)
                 if (!detection) {
                     detection = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                 }
                 
                 if (!detection) {
                     alert("Lost face during capture. Please try again.");
                     resetCaptureBtn();
                     return;
                 }
                 
                 // Draw to canvas
                 const context = canvas.getContext('2d');
                 canvas.width = video.videoWidth;
                 canvas.height = video.videoHeight;
                 context.drawImage(video, 0, 0, canvas.width, canvas.height);
                 const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                 
                // Send to Livewire
                 const descriptor = Array.from(detection.descriptor);
                 
                 // Safe Livewire Call
                 const component = @this;
                 if (component) {
                     component.set('photo', dataUrl);
                     component.set('faceDescriptor', descriptor);
                     
                     try {
                         await component.call('checkIn');
                     } catch (e) {
                         console.error("Livewire error:", e);
                         alert("An error occurred during check-in. Please try again.");
                     } finally {
                         resetCaptureBtn();
                     }
                 } else {
                     console.error("Livewire component not found");
                     alert("Connection lost. Please refresh the page.");
                     resetCaptureBtn();
                 }                 
             } catch (e) {
                 console.error(e);
                 alert("Face verification error: " + e.message);
                 resetCaptureBtn();
             }
        }

        function resetCaptureBtn() {
            captureBtn.disabled = false;
            captureBtn.innerText = "Authenticate & Check In";
            window.isCapturing = false;
        }

        // 5. Custom UI Drawing
        function drawCustomViewfinder(canvas, box, isValid) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            const x = box.x;
            const y = box.y;
            const w = box.width;
            const h = box.height;
            const cornerLength = Math.min(w, h) * 0.2;
            const color = isValid ? '#16a34a' : '#eab308'; // Green-600 or Yellow-500
            const lineWidth = 4;

            ctx.strokeStyle = color;
            ctx.lineWidth = lineWidth;
            ctx.lineCap = 'round';

            // Top-Left
            ctx.beginPath();
            ctx.moveTo(x, y + cornerLength);
            ctx.lineTo(x, y);
            ctx.lineTo(x + cornerLength, y);
            ctx.stroke();

            // Top-Right
            ctx.beginPath();
            ctx.moveTo(x + w - cornerLength, y);
            ctx.lineTo(x + w, y);
            ctx.lineTo(x + w, y + cornerLength);
            ctx.stroke();

            // Bottom-Right
            ctx.beginPath();
            ctx.moveTo(x + w, y + h - cornerLength);
            ctx.lineTo(x + w, y + h);
            ctx.lineTo(x + w - cornerLength, y + h);
            ctx.stroke();

            // Bottom-Left
            ctx.beginPath();
            ctx.moveTo(x + cornerLength, y + h);
            ctx.lineTo(x, y + h);
            ctx.lineTo(x, y + h - cornerLength);
            ctx.stroke();
            
            // Optional: Draw a subtle center rect to guide user
            // ctx.globalAlpha = 0.1;
            // ctx.fillStyle = color;
            // ctx.fillRect(x, y, w, h);
            // ctx.globalAlpha = 1.0;
        }

        // Run
        initMap();
        startLocation();
        startSystem();
    }
</script>
@endpush
