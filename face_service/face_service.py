"""
face_service.py

A tiny Flask HTTP server. Your Laravel app POSTs two base64-encoded
images to it and gets back { matched: bool, confidence: float }.

Install once:
    pip install flask face_recognition pillow numpy

Run:
    python face_service.py

It listens on http://127.0.0.1:5001 by default (same machine as Laravel).
Change HOST/PORT below if you run it on a separate server.

How face_recognition works under the hood:
    - Uses dlib's deep-learning face detector to find all faces in an image.
    - Encodes each detected face as a 128-number vector (a "face embedding").
    - Compares two embeddings using Euclidean distance.
    - Distance < threshold (0.6 by default, we use 0.55 for stricter matching)
      means the same person.

This is the same approach used by commercial systems -- only the
infrastructure (one small Python file on your server vs. a managed
cloud endpoint) differs.
"""

import base64
import io
import logging
import os

import face_recognition
import numpy as np
from flask import Flask, jsonify, request
from PIL import Image

app = Flask(__name__)
logging.basicConfig(level=logging.INFO)

# ------------------------------------------------------------------ #
# Configuration
# ------------------------------------------------------------------ #
HOST = os.environ.get("FACE_SERVICE_HOST", "127.0.0.1")
PORT = int(os.environ.get("FACE_SERVICE_PORT", 5001))
API_KEY = os.environ.get("FACE_SERVICE_KEY", "")   # set a long random string in production

# Lower = stricter. 0.55 is a good balance for controlled lighting
# (classroom / attendance booth). Raise to 0.60 if you get too many
# false-negatives; lower to 0.50 if you get too many false-positives.
MATCH_THRESHOLD = float(os.environ.get("FACE_MATCH_THRESHOLD", 0.55))


# ------------------------------------------------------------------ #
# Helpers
# ------------------------------------------------------------------ #

def decode_image(b64_string: str):
    """
    Accept a base64 string (with or without a data: URL prefix) and
    return a numpy RGB array that face_recognition can use.
    Returns None if the image can't be decoded or contains no face.
    """
    if "," in b64_string:
        b64_string = b64_string.split(",", 1)[1]

    try:
        raw = base64.b64decode(b64_string)
        image = Image.open(io.BytesIO(raw)).convert("RGB")
        return np.array(image)
    except Exception as exc:
        logging.warning("Could not decode image: %s", exc)
        return None


def get_encoding(image_array):
    """
    Return the face encoding for the first (and hopefully only) face
    found in image_array, or None if no face is detected.
    """
    encodings = face_recognition.face_encodings(image_array)
    return encodings[0] if encodings else None


def check_api_key():
    """
    If an API key is configured, require it in the X-API-Key header.
    Returns an error response tuple or None if auth passes.
    """
    if not API_KEY:
        return None          # no key configured -> open access (dev only)
    if request.headers.get("X-API-Key") != API_KEY:
        return jsonify({"error": "Unauthorized"}), 401
    return None


# ------------------------------------------------------------------ #
# Endpoints
# ------------------------------------------------------------------ #

@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})


@app.route("/compare", methods=["POST"])
def compare():
    """
    POST /compare
    Body (JSON):
        reference_image: base64  -- the student's enrolled photo
        live_image:      base64  -- the frame captured during attendance

    Response:
        { "matched": bool, "confidence": float }   (200)
        { "error": "..." }                          (4xx)
    """
    auth_error = check_api_key()
    if auth_error:
        return auth_error

    data = request.get_json(silent=True)
    if not data:
        return jsonify({"error": "Request body must be JSON."}), 400

    reference_b64 = data.get("reference_image", "")
    live_b64 = data.get("live_image", "")

    if not reference_b64 or not live_b64:
        return jsonify({"error": "Both reference_image and live_image are required."}), 400

    reference_array = decode_image(reference_b64)
    if reference_array is None:
        return jsonify({"error": "Could not decode reference_image."}), 422

    live_array = decode_image(live_b64)
    if live_array is None:
        return jsonify({"error": "Could not decode live_image."}), 422

    reference_encoding = get_encoding(reference_array)
    if reference_encoding is None:
        return jsonify({"error": "No face detected in reference_image. Re-enroll the student with a clear frontal photo."}), 422

    live_encoding = get_encoding(live_array)
    if live_encoding is None:
        return jsonify({"error": "No face detected in live_image. Ask the student to look directly at the camera."}), 422

    # face_distance returns a float: 0.0 = identical, 1.0 = completely different.
    distance = face_recognition.face_distance([reference_encoding], live_encoding)[0]

    # Convert distance to a 0-1 confidence score so the Laravel side
    # gets a familiar percentage-style number.
    confidence = round(float(1.0 - distance), 4)
    matched = distance < MATCH_THRESHOLD

    logging.info(
        "compare: distance=%.4f threshold=%.2f matched=%s",
        distance, MATCH_THRESHOLD, matched
    )

    return jsonify({"matched": matched, "confidence": confidence})


# ------------------------------------------------------------------ #
# Run
# ------------------------------------------------------------------ #

if __name__ == "__main__":
    logging.info("Face recognition service starting on %s:%s", HOST, PORT)
    logging.info("MATCH_THRESHOLD = %.2f", MATCH_THRESHOLD)
    logging.info("API key protection: %s", "enabled" if API_KEY else "disabled (dev mode)")
    app.run(host=HOST, port=PORT, debug=False)
