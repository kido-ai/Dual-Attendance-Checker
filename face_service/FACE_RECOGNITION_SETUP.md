# Face Recognition Setup Guide
# Dual Authentication Attendance Checker

---

## What is happening, in plain language

Your Laravel app does NOT do face comparison itself.
It sends two images to a tiny Python server and asks:
"Do these two faces show the same person?"
The Python server answers: { matched: true, confidence: 0.94 }
Laravel records attendance based on that answer.

That Python server is what you are setting up right now.

---

## The two phases

Phase 1  ENROLLMENT (done once per student, by admin)
         Admin uploads a clear photo of the student.
         Laravel stores it on disk as the reference image.
         face_profiles.status -> 'enrolled'

Phase 2  VERIFICATION (done every attendance check-in)
         Teacher's camera captures the student's live face.
         Laravel sends reference photo + live photo to Python service.
         Python compares them and returns matched + confidence.
         If matched -> attendance recorded as present.

---

## PART A: SET UP THE PYTHON SERVICE

### Step 1 -- Install system requirements

On Ubuntu / Debian (your Laravel server or any nearby machine):

    sudo apt-get update
    sudo apt-get install -y python3 python3-pip cmake build-essential libopenblas-dev liblapack-dev libx11-dev

On Windows (if you are developing locally):
    Install Python 3.11 from https://python.org
    Install Visual Studio Build Tools (needed to compile dlib)
    Run all pip commands below in a regular terminal (not PowerShell)

On Mac:
    brew install cmake
    pip3 install cmake


### Step 2 -- Install Python dependencies

Copy face_service.py and requirements.txt to a folder.
Then run:

    pip install -r requirements.txt

NOTE: face_recognition installs dlib, which compiles from source.
This can take 5-15 minutes. That is normal. Do not cancel it.

If dlib fails to compile on Windows, install a pre-built wheel:
    pip install dlib-bin
then retry:
    pip install face_recognition


### Step 3 -- Test that it works

    python face_service.py

You should see:
    Face recognition service starting on 127.0.0.1:5001

Test it with curl (in another terminal):
    curl http://127.0.0.1:5001/health

You should see:
    {"status":"ok"}

Leave this terminal running. The service must stay running while
Laravel uses it.


### Step 4 -- Keep it running permanently (Linux production)

Create a systemd service file:

    sudo nano /etc/systemd/system/face-recognition.service

Paste this (adjust the paths to match where you put face_service.py):

    [Unit]
    Description=Face Recognition Service
    After=network.target

    [Service]
    Type=simple
    User=www-data
    WorkingDirectory=/home/your-user/face-service
    ExecStart=/usr/bin/python3 /home/your-user/face-service/face_service.py
    Restart=always
    RestartSec=5
    Environment="FACE_SERVICE_KEY=your-long-random-secret-key-here"
    Environment="FACE_MATCH_THRESHOLD=0.55"

    [Install]
    WantedBy=multi-user.target

Then enable it:
    sudo systemctl daemon-reload
    sudo systemctl enable face-recognition
    sudo systemctl start face-recognition
    sudo systemctl status face-recognition


---

## PART B: WIRE IT INTO LARAVEL

### Step 5 -- Add the config to .env

Open your Laravel project's .env file and add:

    FACE_RECOGNITION_ENDPOINT=http://127.0.0.1:5001
    FACE_RECOGNITION_KEY=your-long-random-secret-key-here

Use the same key you set in the systemd service (or face_service.py).
If Python and Laravel are on different servers, replace 127.0.0.1 with
the Python server's IP address.

For a fresh local dev environment with no API key protection:
    FACE_RECOGNITION_ENDPOINT=http://127.0.0.1:5001
    FACE_RECOGNITION_KEY=


### Step 6 -- Add the config to config/services.php

Open config/services.php and add this block anywhere inside the
return array:

    'face_recognition' => [
        'endpoint' => env('FACE_RECOGNITION_ENDPOINT', 'http://127.0.0.1:5001'),
        'key'      => env('FACE_RECOGNITION_KEY', ''),
    ],


### Step 7 -- Bind the real provider in AppServiceProvider

Open app/Providers/AppServiceProvider.php.
Find the register() method and add these lines inside it:

    use App\Services\FaceRecognition\FaceRecognitionProvider;
    use App\Services\FaceRecognition\HttpFaceRecognitionProvider;

    public function register(): void
    {
        $this->app->bind(FaceRecognitionProvider::class, function () {
            return new HttpFaceRecognitionProvider(
                config('services.face_recognition.endpoint'),
                config('services.face_recognition.key'),
            );
        });
    }

This is the ONE change that switches the system from
NullFaceRecognitionProvider (which throws a clear error) to the
real HTTP provider that talks to your Python service.


### Step 8 -- Clear the config cache

    php artisan config:clear
    php artisan cache:clear


---

## PART C: ENROLL STUDENT FACES

### Step 9 -- Add the enrollment route

Open routes/admin.php.
Add this line inside the admin route group:

    use App\Http\Controllers\Admin\FaceEnrollmentController;

    Route::post('students/{student}/enroll-face', [FaceEnrollmentController::class, 'store'])
        ->name('students.enroll-face');


### Step 10 -- Add the enrollment form to a student's page

Open resources/views/admin/students/show.blade.php.
Add this line where you want the face enrollment card to appear:

    @include('admin.students.partials._face-enrollment', ['student' => $student])

This gives you a photo-upload card with tips for a good reference
photo, a preview of the uploaded photo, and a clear "Enroll Face"
button.


### Step 11 -- Enroll each student

1. Log in as admin.
2. Go to Students -> click a student -> find the Face Enrollment card.
3. Upload a clear, frontal photo of the student.
4. Click Enroll Face.
5. The face_profiles row for that student changes to status = 'enrolled'.
6. Repeat for every student who will use dual-authentication attendance.


---

## PART D: TEST THE FULL FLOW

### Step 12 -- Run the dev seeder (if you have no test data)

    php artisan db:seed --class=DevAttendanceSeeder

This creates:
    admin@example.test   password: password
    teacher@example.test password: password
    3 students with QR tokens

Then enroll at least one student's face (Step 11) before testing.


### Step 13 -- Test the full dual-authentication flow

1. Log in as teacher@example.test
2. Go to Take Attendance
3. Click Start Attendance
4. Allow camera access when the browser asks
5. Have the student show their QR code (or print the token UUID from
   the qr_tokens table and display it as a QR code using any online
   QR generator for testing)
6. Scanner detects the QR -> Student Found panel appears
7. Face camera opens -> student looks at camera -> click Verify Face
8. Laravel sends reference + live image to Python service
9. Python compares, returns matched + confidence
10. If matched -> attendance recorded -> green result card appears
11. Scanner resets for the next student after 2.5 seconds

---

## COMMON PROBLEMS AND FIXES

Problem: dlib fails to compile
Fix:     pip install dlib-bin   (pre-built wheel)
         then: pip install face_recognition

Problem: "No face detected in reference_image"
Fix:     Upload a clearer photo. Face must be frontal, well-lit,
         min 200x200 px, no glasses or hat.

Problem: "No face detected in live_image"
Fix:     Student must look directly at the camera.
         Ensure the room is well-lit.
         Camera must be at face height.

Problem: "Face does not match" when it should
Fix:     Lighting difference between enrollment photo and live scan
         is the most common cause. Enroll with similar lighting.
         You can raise FACE_MATCH_THRESHOLD from 0.55 to 0.60
         in the systemd service environment variables.

Problem: Connection refused / provider error
Fix:     Check that face_service.py is running:
             curl http://127.0.0.1:5001/health
         Check the FACE_RECOGNITION_ENDPOINT in .env matches the
         address Python is listening on.
         Run: php artisan config:clear

Problem: All face verifications route to Manual Verification
Fix:     This means NullFaceRecognitionProvider is still bound.
         Make sure Step 7 (AppServiceProvider binding) was saved.
         Run: php artisan config:clear && php artisan cache:clear

---

## HOW THE REFERENCE PHOTO IS STORED

When you click "Enroll Face" in the admin panel:
    1. PHP receives the uploaded photo.
    2. FaceRecognitionService::enrollFace() is called.
    3. The raw image bytes are saved to:
           storage/app/face-profiles/{student_id}-{timestamp}.jpg
       (private disk, not publicly accessible)
    4. face_profiles row is updated:
           face_embedding = 'face-profiles/25-1723456789.jpg'
           status         = 'enrolled'
           enrolled_at    = now()

When a student checks in:
    1. take.blade.php captures a live frame from the teacher's camera.
    2. Laravel reads the stored path from face_profiles.face_embedding.
    3. HttpFaceRecognitionProvider::compare() sends both images
       (reference from disk, live from camera) to the Python service.
    4. Python returns { matched: bool, confidence: float }.
    5. AttendanceService::recordAttendance() writes the result.

---

## CLOUD API ALTERNATIVE (if you prefer not to run Python)

If you do not want to run a Python server, you can use AWS Rekognition.

1. Create an AWS account and enable Rekognition in your region.
2. Add to .env:
       AWS_ACCESS_KEY_ID=your-key
       AWS_SECRET_ACCESS_KEY=your-secret
       AWS_DEFAULT_REGION=ap-southeast-1

3. Install the AWS SDK:
       composer require aws/aws-sdk-php

4. Create app/Services/FaceRecognition/AwsRekognitionProvider.php:

    public function compare(string $referenceImagePath, string $liveImageData): array
    {
        $rekognition = new \Aws\Rekognition\RekognitionClient([
            'region' => config('services.aws.region', 'ap-southeast-1'),
        ]);

        $result = $rekognition->compareFaces([
            'SourceImage' => ['Bytes' => Storage::disk('local')->get($referenceImagePath)],
            'TargetImage' => ['Bytes' => $liveImageData],
            'SimilarityThreshold' => 80,
        ]);

        $matches = $result->get('FaceMatches');

        if (empty($matches)) {
            return ['matched' => false, 'confidence' => 0.0];
        }

        $confidence = $matches[0]['Similarity'] / 100;
        return ['matched' => true, 'confidence' => round($confidence, 4)];
    }

5. Bind AwsRekognitionProvider in AppServiceProvider instead of
   HttpFaceRecognitionProvider.

Cost: approximately $0.001 per comparison (1000 comparisons = $1).

---

## FILE SUMMARY

Files you created in this session:
    face_service.py                                 -- the Python server
    requirements.txt                                -- Python dependencies
    app/Services/FaceRecognition/
        FaceRecognitionProvider.php                 -- the interface
        NullFaceRecognitionProvider.php             -- default (dev, no provider)
        HttpFaceRecognitionProvider.php             -- talks to face_service.py
    app/Services/FaceRecognitionService.php         -- Laravel side logic
    app/Http/Controllers/Admin/
        FaceEnrollmentController.php                -- admin enrollment endpoint
    resources/views/admin/students/partials/
        _face-enrollment.blade.php                  -- enrollment UI card

Changes you need to make manually:
    config/services.php                             -- add face_recognition block (Step 6)
    app/Providers/AppServiceProvider.php            -- add binding (Step 7)
    routes/admin.php                                -- add enrollment route (Step 9)
    admin/students/show.blade.php                   -- include enrollment partial (Step 10)
    .env                                            -- add FACE_RECOGNITION_* vars (Step 5)
