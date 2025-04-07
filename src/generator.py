import paho.mqtt.client as mqtt
import json
import ssl
import time
import random
from datetime import datetime

# MQTT Configuration
MQTT_CONFIG = {
    'broker': 'ddeede1a.ala.eu-central-1.emqxsl.com',
    'port': 8883,
    'username': 'apaaja',
    'password': '123456789',
    'topics': {
        'sensor': {
            'suhu': 'monitoring/sensor/suhu',
            'kelembapan': 'monitoring/sensor/kelembapan',
            'cahaya': 'monitoring/sensor/cahaya'
        },
        'power': {
            'er_1fase': 'monitoring/ADW310/energi_reaktif',
            'dr_aktif': 'monitoring/ADW310/daya_reaktif',
            'arus': 'monitoring/ADW310/arus',
            'tegangan': 'monitoring/ADW310/tegangan',
            'frekuensi': 'monitoring/ADW310/frekuensi',
            'faktor_daya': 'monitoring/ADW310/faktor_daya',
            'daya_semu': 'monitoring/ADW310/daya_semu'
        }
    }
}

def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print("Successfully connected to MQTT broker")
    else:
        print(f"Failed to connect to MQTT broker with code: {rc}")

# PERUBAHAN 1: Sesuaikan nama sensor dengan key di MQTT_CONFIG
def generate_sensor_data():
    return {
        'suhu': round(random.uniform(20.0, 30.0), 2),        # Sebelumnya 'temperature'
        'kelembapan': round(random.uniform(40.0, 60.0), 2),  # Sebelumnya 'humidity'
        'cahaya': round(random.uniform(100, 1000), 2)        # Sebelumnya 'light'
    }

def generate_power_data():
    return {
        'er_1fase': round(random.uniform(1000, 2000), 2),
        'dr_aktif': round(random.uniform(500, 1000), 2),
        'arus': round(random.uniform(10, 20), 2),
        'tegangan': round(random.uniform(220, 240), 2),
        'frekuensi': round(random.uniform(49.5, 50.5), 2),
        'faktor_daya': round(random.uniform(0.8, 1.0), 2),
        'daya_semu': round(random.uniform(2000, 3000), 2)
    }

# PERUBAHAN 2: Perbaikan fungsi publish_data
def publish_data(client):
    sensor_data = generate_sensor_data()
    power_data = generate_power_data()
    timestamp = datetime.now().isoformat()

    # Publish sensor data dengan pengecekan yang lebih baik
    for sensor_type, value in sensor_data.items():
        topic = MQTT_CONFIG['topics']['sensor'].get(sensor_type)
        if topic:
            payload = {
                'value': value,
                'timestamp': timestamp,
                'sensor_type': sensor_type,
                'mqtt_topic': topic  # Tambahkan mqtt_topic ke payload
            }
            result = client.publish(topic, json.dumps(payload))
            if result.rc == mqtt.MQTT_ERR_SUCCESS:
                print(f"Published {sensor_type} data: {payload}")
            else:
                print(f"Failed to publish {sensor_type} data")

    # Publish power data dengan pengecekan yang sama
    for power_type, value in power_data.items():
        topic = MQTT_CONFIG['topics']['power'].get(power_type)
        if topic:
            payload = {
                'value': value,
                'timestamp': timestamp,
                'power_type': power_type,
                'mqtt_topic': topic  # Tambahkan mqtt_topic ke payload
            }
            result = client.publish(topic, json.dumps(payload))
            if result.rc == mqtt.MQTT_ERR_SUCCESS:
                print(f"Published {power_type} data: {payload}")
            else:
                print(f"Failed to publish {power_type} data")

def main():
    client = mqtt.Client()
    
    # Set credentials dan TLS
    client.username_pw_set(MQTT_CONFIG['username'], MQTT_CONFIG['password'])
    client.on_connect = on_connect
    client.tls_set(cert_reqs=ssl.CERT_REQUIRED, tls_version=ssl.PROTOCOL_TLS)
    client.tls_insecure_set(False)

    try:
        # Connect to broker
        client.connect(MQTT_CONFIG['broker'], MQTT_CONFIG['port'])
        client.loop_start()

        # Main publishing loop dengan error handling yang lebih baik
        while True:
            try:
                if not client.is_connected():
                    print("Reconnecting to MQTT broker...")
                    client.reconnect()
                publish_data(client)
                time.sleep(5)  # Publish every 5 seconds
            except Exception as e:
                print(f"Error in publishing loop: {e}")
                time.sleep(5)  # Wait before retrying

    except KeyboardInterrupt:
        print("\nStopping data generator...")
    except Exception as e:
        print(f"Error in main loop: {e}")
    finally:
        client.loop_stop()
        client.disconnect()
        print("Generator stopped")

if __name__ == "__main__":
    main()