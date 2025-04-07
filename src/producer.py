import os
from kafka import KafkaProducer
import paho.mqtt.client as mqtt
import json
import ssl
import time
import logging

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Kafka Configuration
KAFKA_CONFIG = {
    'bootstrap_servers': os.getenv('KAFKA_BOOTSTRAP_SERVERS', 'localhost:29092'),
    'topics': {
        'sensor': {
            'suhu': 'monitoring_sensor_suhu',
            'kelembapan': 'monitoring_sensor_kelembapan',
            'cahaya': 'monitoring_sensor_cahaya'
        },
        'power': {
            'er_1fase': 'monitoring_adw310_energi_reaktif',
            'dr_aktif': 'monitoring_adw310_daya_reaktif',
            'arus': 'monitoring_adw310_arus',
            'tegangan': 'monitoring_adw310_tegangan',
            'frekuensi': 'monitoring_adw310_frekuensi',
            'faktor_daya': 'monitoring_adw310_faktor_daya',
            'daya_semu': 'monitoring_adw310_daya_semu'
        }
    }
}

# MQTT Configuration
MQTT_CONFIG = {
    'broker': os.getenv('MQTT_BROKER', 'ddeede1a.ala.eu-central-1.emqxsl.com'),
    'port': int(os.getenv('MQTT_PORT', 8883)),
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

def create_kafka_producer(retries=5, delay=5):
    """Create Kafka producer with retries"""
    for attempt in range(retries):
        try:
            producer = KafkaProducer(
                bootstrap_servers=[KAFKA_CONFIG['bootstrap_servers']],
                value_serializer=lambda x: json.dumps(x).encode('utf-8'),
                acks='all',
                retries=3,
                retry_backoff_ms=1000,
                max_block_ms=20000
            )
            logger.info(f"Successfully connected to Kafka at {KAFKA_CONFIG['bootstrap_servers']}")
            return producer
        except Exception as e:
            logger.error(f"Attempt {attempt + 1} failed: {str(e)}")
            if attempt < retries - 1:
                logger.info(f"Retrying in {delay} seconds...")
                time.sleep(delay)
            else:
                raise

def get_kafka_topic(mqtt_topic):
    """Map MQTT topic to Kafka topic"""
    for category in ['sensor', 'power']:
        for key, mqtt_t in MQTT_CONFIG['topics'][category].items():
            if mqtt_t == mqtt_topic:
                return KAFKA_CONFIG['topics'][category][key]
    return None

def on_connect(client, userdata, flags, rc):
    """Callback for when the client receives a CONNACK response from the server"""
    if rc == 0:
        logger.info("Connected to MQTT broker")
        # Subscribe to all topics
        topics = []
        for category in ['sensor', 'power']:
            topics.extend(list(MQTT_CONFIG['topics'][category].values()))
        
        for topic in topics:
            client.subscribe(topic)
            logger.info(f"Subscribed to {topic}")
    else:
        logger.error(f"Failed to connect to MQTT broker with code: {rc}")

def on_message(client, userdata, msg):
    """Callback for when a PUBLISH message is received from the server"""
    try:
        # Get the corresponding Kafka topic
        kafka_topic = get_kafka_topic(msg.topic)
        if not kafka_topic:
            logger.warning(f"No Kafka topic mapping found for MQTT topic: {msg.topic}")
            return

        # Parse the message
        payload = json.loads(msg.payload.decode('utf-8'))
        
        # Add metadata
        payload['mqtt_topic'] = msg.topic
        payload['timestamp'] = time.time()

        # Send to Kafka
        producer = userdata['producer']
        producer.send(kafka_topic, payload)
        producer.flush()
        
        logger.info(f"Successfully forwarded message from {msg.topic} to {kafka_topic}")
        logger.debug(f"Message content: {payload}")

    except json.JSONDecodeError as e:
        logger.error(f"Failed to decode JSON message from {msg.topic}: {e}")
    except Exception as e:
        logger.error(f"Error processing message from {msg.topic}: {e}")

def main():
    try:
        # Create Kafka producer
        producer = create_kafka_producer()
        
        # Create MQTT client
        client = mqtt.Client()
        client.user_data_set({'producer': producer})  # Pass producer to callbacks
        
        # Set MQTT callbacks
        client.on_connect = on_connect
        client.on_message = on_message
        
        # Set MQTT credentials and security
        client.username_pw_set(MQTT_CONFIG['username'], MQTT_CONFIG['password'])
        client.tls_set(cert_reqs=ssl.CERT_REQUIRED, tls_version=ssl.PROTOCOL_TLS)
        client.tls_insecure_set(False)

        # Connect to MQTT broker
        logger.info(f"Connecting to MQTT broker at {MQTT_CONFIG['broker']}:{MQTT_CONFIG['port']}")
        client.connect(MQTT_CONFIG['broker'], MQTT_CONFIG['port'])

        # Start MQTT loop
        client.loop_forever()

    except KeyboardInterrupt:
        logger.info("Shutting down...")
    except Exception as e:
        logger.error(f"Fatal error: {e}")
    finally:
        if 'producer' in locals():
            producer.close()
        if 'client' in locals():
            client.loop_stop()
            client.disconnect()
        logger.info("Cleanup completed")

if __name__ == "__main__":
    main()