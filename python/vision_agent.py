import cv2
import base64
import time
from flask import Flask, Response

app = Flask(__name__)

# Replace with your phone's IP from the streaming app
PHONE_STREAM_URL = "http://192.168.0.22:8080/video"

def generate_frames():
    camera = cv2.VideoCapture(PHONE_STREAM_URL)
    while True:
        success, frame = camera.read()
        if not success:
            break
        else:
            # OPTIONAL: Run AI Detection here
            # frame = my_ai_logic(frame) 

            ret, buffer = cv2.imencode('.jpg', frame)
            frame = buffer.tobytes()
            # Yield the frame in a format the browser understands
            yield (b'--frame\r\n'
                   b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')

@app.route('/video_feed')
def video_feed():
    return Response(generate_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5001)