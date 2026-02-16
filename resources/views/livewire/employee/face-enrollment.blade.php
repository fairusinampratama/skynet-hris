<div class="max-w-xl mx-auto" 
     x-data="faceEnrollmentData()"
     x-init="init()"
     x-on:livewire:navigating.document="destroy()" 
>
    <!-- Template Content -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Register Face ID</h2>
        
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('message') }}
            </div>
        @endif

        @if($hasEnrolledFace)
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm leading-5 font-medium text-blue-800">
                            Face ID Registered
                        </h3>
                        <div class="mt-2 text-sm leading-5 text-blue-700">
                            <p>
                                Your Face ID is currently active. To update or reset your face data, please contact the HR department.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else

        <div class="relative rounded-2xl overflow-hidden bg-gray-900 w-full h-[400px] mb-4 shadow-inner group">
            <!-- Video Element -->
            <video x-ref="video" class="w-full h-full object-cover transform scale-x-[-1]" autoplay playsinline muted></video>
            
            <!-- Visual Guide Mask -->
            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                <div class="w-64 h-80 border-4 border-white/30 rounded-[50%] shadow-[0_0_1000px_0_rgba(0,0,0,0.5)]"></div>
            </div>

            <!-- Canvas Overlay for Face Tracking -->
            <canvas x-ref="canvas" class="absolute inset-0 w-full h-full transform scale-x-[-1] pointer-events-none"></canvas>
            
            <!-- Loading Overlay -->
            <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center bg-gray-900 z-20">
                <div class="text-center">
                    <svg class="animate-spin h-8 w-8 text-white mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="text-white text-sm font-medium">Starting Camera & AI...</span>
                </div>
            </div>

            <!-- Error Overlay -->
            <div x-show="error" class="absolute inset-0 flex items-center justify-center bg-gray-900 z-30" style="display: none;">
                <div class="text-center px-4">
                    <span class="block text-red-500 text-3xl mb-2">⚠️</span>
                    <p class="text-white text-sm" x-text="error"></p>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="absolute top-4 right-4 px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-sm z-10 transition-colors duration-300"
                 :class="{
                    'bg-green-500 text-white': isFaceDetected,
                    'bg-yellow-500 text-white': !isFaceDetected && status !== 'No Face Found' && !isLoading,
                    'bg-red-500 text-white': status === 'No Face Found' && !isLoading,
                    'hidden': isLoading
                 }"
                 x-text="status">
            </div>
        </div>

        <button @click="capture()" 
                :disabled="!isFaceDetected || isProcessing"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl focus:outline-none focus:shadow-outline transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
            <span x-show="!isProcessing">Capture & Register</span>
            <span x-show="isProcessing" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </span>
        </button>
        @endif
    </div>

    <!-- Alpine Component Definition -->
    <script>
        function faceEnrollmentData() {
            return {
                video: null,
                stream: null,
                status: 'Initializing...',
                isLoading: true,
                error: null,
                isFaceDetected: false,
                modelLoaded: false,
                isProcessing: false,
                descriptor: null, // Initialize descriptor

                async init() {
                    this.$nextTick(() => this.startSystem());
                },

                loadScript(src) {
                    return new Promise((resolve, reject) => {
                        if (typeof faceapi !== 'undefined') {
                            resolve();
                            return;
                        }

                        if (document.querySelector(`script[src='${src}']`)) {
                            // Script is in DOM but faceapi not yet ready - wait for it
                            const interval = setInterval(() => {
                                if (typeof faceapi !== 'undefined') {
                                    clearInterval(interval);
                                    resolve();
                                }
                            }, 50);
                            // Also listen for custom event for faster response
                            document.addEventListener('face-api-loaded', () => {
                                clearInterval(interval);
                                resolve();
                            }, { once: true });
                            // Safety timeout
                            setTimeout(() => clearInterval(interval), 10000);
                            return;
                        }

                        const script = document.createElement('script');
                        script.src = src;
                        script.onload = () => {
                            document.dispatchEvent(new Event('face-api-loaded'));
                            resolve();
                        };
                        script.onerror = reject;
                        document.head.appendChild(script);
                    });
                },

                async startSystem() {
                    this.video = this.$refs.video; // Ensure video ref is set
                    this.isLoading = true;
                    this.status = 'Loading AI Models...';

                    try {
                        // 1. Load Face API script if not already loaded
                        if (typeof faceapi === 'undefined') {
                            await this.loadScript('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js');
                        }

                        // 2. Load Models (SSD Mobilenet V1 for enrollment)
                        if (!this.modelLoaded) {
                            const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
                            await Promise.all([
                                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL), // Heavy model for enrollment
                                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                                // faceExpressionNet is not needed for enrollment
                            ]);
                            this.modelLoaded = true;
                        }

                        // 3. Start Video Stream
                        this.status = 'Starting Camera...';
                        this.stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                        this.video.srcObject = this.stream;
                        
                        // Fix AbortError by handling the promise
                        try {
                            await this.video.play();
                        } catch (e) {
                            console.log("Play interrupted, automatic handling by browser");
                        }

                        this.video.onplay = () => {
                            this.isLoading = false;
                            this.status = 'Ready for Detection';
                            this.startDetection(); // Start detection loop once video is playing
                        };

                    } catch (err) {
                        console.error(err);
                        this.error = 'System Error: ' + err.message;
                        this.isLoading = false;
                    }
                },

                validateFaceQuality(detection, videoWidth, videoHeight) {
                    const { box } = detection.detection;
                    const score = detection.detection.score;
                    const landmarks = detection.landmarks;

                    // 1. Size Check
                    if (box.width < 150 || box.height < 150) return { valid: false, reason: 'Move Closer' };
                    if (score < 0.90) return { valid: false, reason: 'Face Unclear' };

                    // 2. Centering Check
                    const centerX = box.x + (box.width / 2);
                    const centerY = box.y + (box.height / 2);
                    const thresholdX = videoWidth * 0.2;
                    const thresholdY = videoHeight * 0.2;

                    if (centerX < thresholdX || centerX > (videoWidth - thresholdX) ||
                        centerY < thresholdY || centerY > (videoHeight - thresholdY)) {
                        return { valid: false, reason: 'Center Face' };
                    }

                    // 3. Pose Check (Look Straight)
                    const nose = landmarks.getNose()[3];
                    const jaw = landmarks.getJawOutline();
                    const jawLeft = jaw[0];
                    const jawRight = jaw[16];
                    
                    const distLeft = nose.x - jawLeft.x;
                    const distRight = jawRight.x - nose.x;
                    const totalWidth = distLeft + distRight;
                    const ratio = distLeft / totalWidth;

                    if (ratio < 0.4 || ratio > 0.6) return { valid: false, reason: 'Look Straight' };

                    // 4. Lighting Check
                    const brightness = this.checkBrightness();
                    if (brightness < 40) return { valid: false, reason: 'Too Dark' };
                    if (brightness > 230) return { valid: false, reason: 'Too Bright' };

                    return { valid: true, reason: 'Perfect' };
                },

                checkBrightness() {
                    if (!this.video) return 128;
                    
                    const canvas = document.createElement('canvas');
                    canvas.width = 50; 
                    canvas.height = 50;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(this.video, 0, 0, 50, 50);
                    
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
                },

                startDetection() {
                    setInterval(async () => {
                        if (!this.video || this.video.paused || this.video.ended || !this.modelLoaded) return;

                        const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.6 });
                        
                        const detections = await faceapi.detectSingleFace(this.video, options)
                            .withFaceLandmarks()
                            .withFaceDescriptor(); 
                        
                        const canvas = this.$refs.canvas;
                        const displaySize = { width: this.video.videoWidth, height: this.video.videoHeight };
                        faceapi.matchDimensions(canvas, displaySize);

                        if (detections) {
                            const resizedDetections = faceapi.resizeResults(detections, displaySize);
                            // faceapi.draw.drawDetections(canvas, resizedDetections); // REMOVED

                            const quality = this.validateFaceQuality(detections, this.video.videoWidth, this.video.videoHeight);
                            this.drawCustomViewfinder(canvas, resizedDetections.detection.box, quality.valid);
                            if (quality.valid) {
                                this.isFaceDetected = true;
                                this.status = 'Face Detected (High Quality)';
                                this.descriptor = Array.from(detections.descriptor);
                            } else {
                                this.isFaceDetected = false;
                                this.status = quality.reason;
                                this.descriptor = null;
                            }
                        } else {
                            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                            this.isFaceDetected = false;
                            this.status = 'No Face Found';
                        }
                    }, 500);
                },

                async capture() {
                    if (this.isProcessing) return;
                    this.isProcessing = true;

                    try {
                        // OPTIMIZATION: Use the HIGH-QUALITY descriptor we already found!
                        // We don't need to run detection again.
                        if (!this.descriptor || this.descriptor.length !== 128) {
                             alert("No valid face data ready. Please wait for 'Face Detected (High Quality)' status.");
                             return;
                        }

                        // Capture Image from Video Stream
                        const canvas = document.createElement('canvas');
                        canvas.width = this.video.videoWidth;
                        canvas.height = this.video.videoHeight;
                        canvas.getContext('2d').drawImage(this.video, 0, 0);
                        const photoData = canvas.toDataURL('image/jpeg', 0.9); // High quality JPEG

                        // Send to Livewire via Properties
                        // Set properties first (avoids argument count errors on large payloads)
                        this.$wire.photo = photoData;
                        this.$wire.descriptor = this.descriptor; // Use the stored Array
                        
                        // Call method without args
                        await this.$wire.saveEnrollment();
                        
                        this.status = 'Registered Successfully';
                        this.stopCamera();

                    } catch (err) {
                        console.error(err);
                        alert('Error capturing face: ' + err.message);
                    } finally {
                        this.isProcessing = false;
                    }
                },

                stopCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                        this.stream = null;
                    }
                },
                
                destroy() {
                    this.stopCamera();
                },

                drawCustomViewfinder(canvas, box, isValid) {
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
                }
            };
        }
    </script>
</div>
