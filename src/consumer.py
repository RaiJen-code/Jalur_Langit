import os
import json
import threading
from kafka import KafkaConsumer
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

def create_kafka_consumer(topic, group_id="monitoring_group"):
    """Create a Kafka consumer with retries"""
    try:
        consumer = KafkaConsumer(
            topic,
            bootstrap_servers=[KAFKA_CONFIG['bootstrap_servers']],
            auto_offset_reset='earliest',
            enable_auto_commit=True,
            group_id=group_id,
            value_deserializer=lambda x: json.loads(x.decode('utf-8')),
            consumer_timeout_ms=60000  # 60 seconds timeout
        )
        logger.info(f"Successfully created consumer for topic: {topic}")
        return consumer
    except Exception as e:
        logger.error(f"Failed to create consumer for topic {topic}: {e}")
        raise

def consume_sensor_data(topic_key, topic_name):
    """Consume sensor data from Kafka"""
    topic = KAFKA_CONFIG['topics']['sensor'][topic_key]
    try:
        consumer = create_kafka_consumer(topic)
        logger.info(f"Started consuming {topic_name} data from topic: {topic}")
        
        for message in consumer:
            try:
                data = message.value
                logger.info(f"{topic_name.capitalize()} data received: {data}")
                logger.debug(f"Partition: {message.partition}, Offset: {message.offset}")
            except Exception as e:
                logger.error(f"Error processing {topic_name} message: {e}")

    except Exception as e:
        logger.error(f"Error in consumer thread for {topic_name}: {e}")
    finally:
        if 'consumer' in locals():
            consumer.close()
            logger.info(f"Closed consumer for {topic_name}")

def consume_power_data(topic_key, topic_name):
    """Consume power meter data from Kafka"""
    topic = KAFKA_CONFIG['topics']['power'][topic_key]
    try:
        consumer = create_kafka_consumer(topic)
        logger.info(f"Started consuming {topic_name} data from topic: {topic}")
        
        for message in consumer:
            try:
                data = message.value
                logger.info(f"{topic_name.capitalize()} data received: {data}")
                logger.debug(f"Partition: {message.partition}, Offset: {message.offset}")
            except Exception as e:
                logger.error(f"Error processing {topic_name} message: {e}")

    except Exception as e:
        logger.error(f"Error in consumer thread for {topic_name}: {e}")
    finally:
        if 'consumer' in locals():
            consumer.close()
            logger.info(f"Closed consumer for {topic_name}")

def main():
    """Main function to start all consumer threads"""
    try:
        # Create threads for sensor data consumers
        sensor_threads = [
            threading.Thread(target=consume_sensor_data, args=('suhu', 'temperature')),
            threading.Thread(target=consume_sensor_data, args=('kelembapan', 'humidity')),
            threading.Thread(target=consume_sensor_data, args=('cahaya', 'light'))
        ]

        # Create threads for power meter data consumers
        power_threads = [
            threading.Thread(target=consume_power_data, args=('er_1fase', 'reactive energy')),
            threading.Thread(target=consume_power_data, args=('dr_aktif', 'reactive power')),
            threading.Thread(target=consume_power_data, args=('arus', 'current')),
            threading.Thread(target=consume_power_data, args=('tegangan', 'voltage')),
            threading.Thread(target=consume_power_data, args=('frekuensi', 'frequency')),
            threading.Thread(target=consume_power_data, args=('faktor_daya', 'power factor')),
            threading.Thread(target=consume_power_data, args=('daya_semu', 'apparent power'))
        ]

        # Combine all threads
        all_threads = sensor_threads + power_threads

        # Start all threads
        logger.info("Starting Kafka consumers...")
        for thread in all_threads:
            thread.start()
            logger.info(f"Started consumer thread: {thread.name}")

        # Wait for all threads to complete
        for thread in all_threads:
            thread.join()

    except KeyboardInterrupt:
        logger.info("\nShutting down consumers gracefully...")
    except Exception as e:
        logger.error(f"Error in main thread: {e}")
    finally:
        logger.info("Consumer application stopped")

if __name__ == "__main__":
    main()