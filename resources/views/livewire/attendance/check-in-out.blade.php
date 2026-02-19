@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #video, #canvas { transform: scaleX(-1); } /* Mirror effect */
    </style>
@endpush

<div>
    <!-- Main Content Container -->
    <div class="max-w-lg mx-auto py-6 px-4 sm:px-6">
        <x-layouts.mobile-header title="{{ __('Attendance') }}" />

        <!-- Header (Consistent with Dashboard) -->
        <div class="hidden sm:flex items-center justify-between mb-6">
            <div>
                <p class="text-sm text-gray-500 font-medium">{{ now()->isoFormat('dddd, D MMM Y') }}</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('Attendance') }}</h1>
            </div>
            
            <!-- User Avatar (Matches Dashboard style) -->
            <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
        </div>
    


    @if ($hasCheckedIn && !$hasCheckedOut)
        <!-- Checked In State -->
        <div class="bg-white shadow rounded-2xl overflow-hidden border border-gray-100">
            <div class="p-6 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-4">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">{{ __('You are Checked In') }}</h3>
                <p class="text-sm text-gray-500 mt-1">Since {{ $checkInTime }}</p>
            </div>
            
            <div class="px-6 pb-6">
                <!-- Optional Work Summary -->
                <!-- Work Summary -->
                <div class="mb-6">
                    <label for="workSummary" class="block text-sm font-semibold text-gray-900 mb-2">{{ __('Daily Report / Summary') }} <span class="text-gray-400 font-normal text-xs">({{ __('Optional') }})</span></label>
                    <textarea wire:model="workSummary" id="workSummary" rows="4" class="block w-full rounded-2xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm bg-gray-50 resize-none p-4" placeholder="{{ __('Briefly describe what you worked on today...') }}"></textarea>
                    @error('workSummary') <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                </div>
                
                <button wire:click="checkOut" type="button" class="w-full flex justify-center items-center rounded-xl bg-red-600 px-4 py-3.5 text-base font-semibold text-white shadow-sm hover:bg-red-500 active:bg-red-700 transition-colors">
                    {{ __('Check Out Now') }}
                </button>
            </div>
        </div>
    
    @elseif ($hasCheckedIn && $hasCheckedOut)
        <!-- Completed State -->
        <div class="bg-white shadow rounded-2xl overflow-hidden border border-gray-100 p-6 text-center">
             <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 mb-4">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
             <h3 class="text-lg font-semibold text-gray-900">{{ __('All Done!') }}</h3>
             <p class="text-sm text-gray-500 mt-1">{{ __('You have checked out for today.') }}</p>
             
             <div class="mt-6 bg-gray-50 rounded-xl p-4 grid grid-cols-2 gap-4 divide-x divide-gray-200">
                 <div>
                     <p class="text-xs text-gray-400 uppercase tracking-wider">In</p>
                     <p class="font-medium text-gray-900">{{ $checkInTime }}</p>
                 </div>
                 <div>
                     <p class="text-xs text-gray-400 uppercase tracking-wider">Out</p>
                     <p class="font-medium text-gray-900">{{ $checkOutTime }}</p>
                 </div>
             </div>
        </div>

    @else
        @if(!$hasEnrolledFace)
            <!-- Unregistered State -->
            <div class="bg-white shadow rounded-2xl p-6 text-center border-t-4 border-yellow-400">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 mb-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Setup Required</h3>
                <p class="mt-2 text-sm text-gray-600 mb-6">
                    We need your face data before you can check in.
                </p>
                <a href="{{ route('profile') }}" class="w-full inline-flex justify-center items-center rounded-xl bg-yellow-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 transition-colors">
                    Enroll Face Data
                </a>
            </div>
        @else
            <!-- Face & Location Verification Section -->
            <div class="space-y-4" wire:ignore>
                <!-- Camera Card (Hero) -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden relative">
                    <!-- Status Overlay -->
                    <div id="face-status" class="absolute top-4 left-0 right-0 mx-auto w-max z-20 bg-gray-900/80 text-white px-4 py-1.5 rounded-full text-xs font-semibold backdrop-blur-md transition-all duration-300 shadow-md">
                        Initializing Camera...
                    </div>

                    <div id="video-wrapper" class="relative bg-black aspect-[4/5] w-full overflow-hidden">
                        <video id="video" class="w-full h-full object-cover" autoplay playsinline muted></video>
                        <canvas id="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
                        
                        <!-- AI Loading Indicator -->
                        <div id="loading-models" class="absolute bottom-4 left-4 z-20 text-[10px] text-white/70 bg-black/40 px-2 py-1 rounded backdrop-blur-sm">
                            Loading AI...
                        </div>
                    </div>
                </div>

                <!-- Map & Location (Collapsible/Secondary) -->
                <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden p-3">
                    <div class="flex items-center justify-between mb-2 px-1">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Location
                        </span>
                        <div id="location-status" class="text-[10px] font-bold text-gray-400">Locating...</div>
                    </div>
                    
                    <div id="map" class="h-32 w-full rounded-xl bg-gray-50 z-0"></div> 
                    
                    <!-- Coordinates (Hidden on mobile primarily, small text) -->
                    <div class="mt-2 grid grid-cols-2 gap-2 text-[10px] text-gray-400 px-1">
                         <div class="flex justify-between">
                             <span>LAT</span>
                             <span class="font-mono text-gray-600" id="disp-lat">-</span>
                         </div>
                         <div class="flex justify-between">
                             <span>LNG</span>
                             <span class="font-mono text-gray-600" id="disp-long">-</span>
                         </div>
                    </div>
                </div>
                
                <!-- Main Action Button (Sticky-ish feel) -->
                <div class="pt-2">
                     <button id="capture-btn" disabled class="disabled:opacity-50 disabled:grayscale disabled:cursor-not-allowed w-full rounded-2xl bg-indigo-600 px-4 py-4 text-lg font-bold text-white shadow-xl hover:bg-indigo-500 active:scale-[0.98] transition-all flex justify-center items-center gap-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.247-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.077.898-.512.898h-2.65c-.958 0-1.838-.49-2.29-1.272a9.1 9.1 0 01-1.082-3.868 2.25 2.25 0 012.25-2.25h1.944a4.49 4.49 0 001.077-.129l2.894-.964" />
                        </svg>
                        Check In
                     </button>
                </div>
            </div>
        @endif
    @endif
</div>
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
        
        if (window.attendanceResizeObserver) {
            window.attendanceResizeObserver.disconnect();
            window.attendanceResizeObserver = null;
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
                
                // Disconnect existing observer if any
                if (window.attendanceResizeObserver) {
                    window.attendanceResizeObserver.disconnect();
                }

                window.attendanceResizeObserver = new ResizeObserver(() => {
                    // Only invalidate if global map reference exists and matches current instance
                    if (window.attendanceMap && window.attendanceMap === map && map.getContainer()) {
                        map.invalidateSize();
                    }
                });
                
                window.attendanceResizeObserver.observe(mapElement);
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
                    loadingDiv.className = "absolute bottom-4 left-4 z-20 text-[10px] text-green-400 bg-black/60 px-2 py-1 rounded backdrop-blur-sm font-bold border border-green-500/30 shadow-sm";
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
                    loadingDiv.className = "absolute bottom-4 left-4 z-20 text-[10px] text-red-500 bg-black/60 px-2 py-1 rounded backdrop-blur-sm font-bold border border-red-500/30 shadow-sm";
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
