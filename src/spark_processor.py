import logging
import os
from pyspark.sql import SparkSession
from datetime import datetime
from pyspark.sql.dataframe import DataFrame
from pyspark.sql.functions import (
    from_json, col, when, expr, lit, current_timestamp, window, 
    collect_list, struct, first, last, count, coalesce
)
from pyspark.sql.types import StructType, StructField, FloatType, TimestampType, StringType, DoubleType
from pyspark.sql.streaming import StreamingQuery

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Configuration
KAFKA_BOOTSTRAP_SERVERS = os.getenv('KAFKA_BOOTSTRAP_SERVERS', 'kafka:9092')
CHECKPOINT_LOCATION = "/opt/spark/checkpoints"
os.makedirs(CHECKPOINT_LOCATION, exist_ok=True)

# Database Configuration
DB_CONFIG = {
    'url': "jdbc:mysql://db:3306/monitoring_db?serverTimezone=Asia/Jakarta",
    'properties': {
        'user': 'monitoring_user',
        'password': 'monitoring_pass',
        'driver': 'com.mysql.cj.jdbc.Driver'
    }
}

def create_sensor_schema():
    return StructType([
        StructField("value", DoubleType(), True),
        StructField("timestamp", TimestampType(), True),
        StructField("sensor_type", StringType(), True),
        StructField("mqtt_topic", StringType(), True)
    ])

def create_power_schema():
    return StructType([
        StructField("value", DoubleType(), True),
        StructField("timestamp", TimestampType(), True),
        StructField("power_type", StringType(), True),
        StructField("mqtt_topic", StringType(), True)
    ])

def create_spark_session(app_name: str) -> SparkSession:
    return (SparkSession.builder
            .appName(app_name)
            .config("spark.streaming.stopGracefullyOnShutdown", "true")
            .config("spark.sql.streaming.schemaInference", "true")
            .config("spark.driver.memory", "2g")
            .config("spark.executor.memory", "2g")
            .config("spark.sql.shuffle.partitions", "4")
            .config("spark.streaming.kafka.maxRatePerPartition", "100")
            .config("spark.sql.streaming.checkpointLocation", CHECKPOINT_LOCATION)
            .config("spark.jars.packages", 
                    "org.apache.spark:spark-sql-kafka-0-10_2.12:3.2.0,"
                    "org.apache.spark:spark-avro_2.12:3.2.0,"
                    "mysql:mysql-connector-java:8.0.28")
            .getOrCreate())

def read_kafka_stream(spark: SparkSession, topics: list) -> DataFrame:
    return (spark.readStream
            .format("kafka")
            .option("kafka.bootstrap.servers", KAFKA_BOOTSTRAP_SERVERS)
            .option("subscribe", ",".join(topics))
            .option("startingOffsets", "earliest")
            .option("failOnDataLoss", "false")
            .option("kafka.security.protocol", "PLAINTEXT")
            .load())

def process_sensor_stream(df: DataFrame, schema: StructType) -> DataFrame:
    try:
        # Parse JSON data
        parsed_df = df.select(
            from_json(col("value").cast("string"), schema).alias("data")
        ).select("data.*")
        
        # Create windows to group data
        windowed_df = parsed_df.withWatermark("timestamp", "1 minute") \
            .groupBy(
                window("timestamp", "10 seconds", "5 seconds")
            ).agg(
                # Collect all values for each sensor type
                collect_list(
                    struct(
                        col("sensor_type"),
                        col("value"),
                        col("timestamp")
                    )
                ).alias("measurements")
            )
        
        # Extract values for each sensor type
        result_df = windowed_df.select(
            coalesce(
                expr("filter(measurements, x -> x.sensor_type = 'suhu')[0].value"),
                lit(None)
            ).cast("double").alias("suhu"),
            coalesce(
                expr("filter(measurements, x -> x.sensor_type = 'kelembapan')[0].value"),
                lit(None)
            ).cast("double").alias("kelembapan"),
            coalesce(
                expr("filter(measurements, x -> x.sensor_type = 'cahaya')[0].value"),
                lit(None)
            ).cast("double").alias("cahaya"),
            col("window.end").alias("sensor_timestamp"),  # Ganti ini
            current_timestamp().alias("created_at"),
            current_timestamp().alias("updated_at")
        ).filter(
            "suhu IS NOT NULL OR kelembapan IS NOT NULL OR cahaya IS NOT NULL"
        )
        
        logger.info("Sensor stream schema:")
        for field in result_df.schema.fields:
            logger.info(f"Field: {field.name}, Type: {field.dataType}")
        
        return result_df
        
    except Exception as e:
        logger.error(f"Error processing sensor stream: {e}", exc_info=True)
        raise

def process_power_stream(df: DataFrame, schema: StructType) -> DataFrame:
    try:
        # Parse JSON data
        parsed_df = df.select(
            from_json(col("value").cast("string"), schema).alias("data")
        ).select("data.*")
        
        # Create windows to group data
        windowed_df = parsed_df.withWatermark("timestamp", "1 minute") \
            .groupBy(
                window("timestamp", "10 seconds", "5 seconds")
            ).agg(
                # Collect all values for each power type
                collect_list(
                    struct(
                        col("power_type"),
                        col("value"),
                        col("timestamp")
                    )
                ).alias("measurements")
            )
        
        # Extract values for each power type
        result_df = windowed_df.select(
            coalesce(
                expr("filter(measurements, x -> x.power_type = 'er_1fase')[0].value"),
                lit(None)
            ).cast("double").alias("reactive_energy"),
            coalesce(
                expr("filter(measurements, x -> x.power_type = 'dr_aktif')[0].value"),
                lit(None)
            ).cast("double").alias("reactive_power"),
            coalesce(
                expr("filter(measurements, x -> x.power_type = 'arus')[0].value"),
                lit(None)
            ).cast("double").alias("current"),
            coalesce(
                expr("filter(measurements, x -> x.power_type = 'tegangan')[0].value"),
                lit(None)
            ).cast("double").alias("voltage"),
            coalesce(
                expr("filter(measurements, x -> x.power_type = 'frekuensi')[0].value"),
                lit(None)
            ).cast("double").alias("frequency"),
            coalesce(
                expr("filter(measurements, x -> x.power_type = 'faktor_daya')[0].value"),
                lit(None)
            ).cast("double").alias("power_factor"),
            coalesce(
                expr("filter(measurements, x -> x.power_type = 'daya_semu')[0].value"),
                lit(None)
            ).cast("double").alias("apparent_power"),
            col("window.end").alias("power_timestamp"),  # Ganti ini
            current_timestamp().alias("created_at"),
            current_timestamp().alias("updated_at")
        ).filter("""
            reactive_energy IS NOT NULL OR reactive_power IS NOT NULL OR
            current IS NOT NULL OR voltage IS NOT NULL OR
            frequency IS NOT NULL OR power_factor IS NOT NULL OR
            apparent_power IS NOT NULL
        """)
        
        logger.info("Power stream schema:")
        for field in result_df.schema.fields:
            logger.info(f"Field: {field.name}, Type: {field.dataType}")
        
        return result_df
        
    except Exception as e:
        logger.error(f"Error processing power stream: {e}")
        raise

def write_stream(df: DataFrame, query_name: str) -> StreamingQuery:
    checkpoint_path = os.path.join(CHECKPOINT_LOCATION, query_name)
    os.makedirs(checkpoint_path, exist_ok=True)
    
    def write_to_mysql(df, epoch_id):
        try:
            logger.info(f"Processing batch {epoch_id} for {query_name}")
            
            # Count complete and incomplete records
            if query_name == "sensor_metrics":
                complete_records = df.filter(
                    "suhu IS NOT NULL AND kelembapan IS NOT NULL AND cahaya IS NOT NULL"
                )
                partial_records = df.filter(
                    "(suhu IS NULL OR kelembapan IS NULL OR cahaya IS NULL) AND " +
                    "(suhu IS NOT NULL OR kelembapan IS NOT NULL OR cahaya IS NOT NULL)"
                )
            else:
                complete_records = df.filter("""
                    reactive_energy IS NOT NULL AND reactive_power IS NOT NULL AND
                    current IS NOT NULL AND voltage IS NOT NULL AND
                    frequency IS NOT NULL AND power_factor IS NOT NULL AND
                    apparent_power IS NOT NULL
                """)
                partial_records = df.filter("""
                    (reactive_energy IS NULL OR reactive_power IS NULL OR
                    current IS NULL OR voltage IS NULL OR
                    frequency IS NULL OR power_factor IS NULL OR
                    apparent_power IS NULL) AND
                    (reactive_energy IS NOT NULL OR reactive_power IS NOT NULL OR
                    current IS NOT NULL OR voltage IS NOT NULL OR
                    frequency IS NOT NULL OR power_factor IS NOT NULL OR
                    apparent_power IS NOT NULL)
                """)
            
            complete_count = complete_records.count()
            partial_count = partial_records.count()
            
            logger.info(f"Complete records: {complete_count}, Partial records: {partial_count}")
            
            if complete_count > 0:
                complete_records.write \
                    .format("jdbc") \
                    .mode("append") \
                    .option("url", DB_CONFIG['url']) \
                    .option("driver", DB_CONFIG['properties']['driver']) \
                    .option("user", DB_CONFIG['properties']['user']) \
                    .option("password", DB_CONFIG['properties']['password']) \
                    .option("dbtable", "sensors" if query_name == "sensor_metrics" else "powers") \
                    .save()
                    
                logger.info(f"Successfully saved {complete_count} complete records to MySQL")
            
            if partial_count > 0:
                logger.warning(f"Skipped {partial_count} incomplete records")
                
        except Exception as e:
            logger.error(f"Error saving to MySQL: {str(e)}")
            raise

    return (df.writeStream
            .foreachBatch(write_to_mysql)
            .outputMode("append")
            .option("checkpointLocation", checkpoint_path)
            .trigger(processingTime="5 seconds")
            .start())

def main():
    spark = None
    queries = []

    try:
        # Initialize Spark
        spark = create_spark_session("IoTDataProcessor")
        logger.info("Created Spark Session successfully")

        # Define topics
        sensor_topics = [
            "monitoring_sensor_suhu",
            "monitoring_sensor_kelembapan",
            "monitoring_sensor_cahaya"
        ]
        power_topics = [
            "monitoring_adw310_energi_reaktif",
            "monitoring_adw310_daya_reaktif",
            "monitoring_adw310_arus",
            "monitoring_adw310_tegangan",
            "monitoring_adw310_frekuensi",
            "monitoring_adw310_faktor_daya",
            "monitoring_adw310_daya_semu"
        ]

        # Read and process streams
        sensor_stream = read_kafka_stream(spark, sensor_topics)
        power_stream = read_kafka_stream(spark, power_topics)

        processed_sensor = process_sensor_stream(sensor_stream, create_sensor_schema())
        processed_power = process_power_stream(power_stream, create_power_schema())

        # Write streams
        queries.extend([
            write_stream(processed_sensor, "sensor_metrics"),
            write_stream(processed_power, "power_metrics")
        ])

        logger.info("Started streaming queries successfully")
        spark.streams.awaitAnyTermination()

    except Exception as e:
        logger.error(f"An error occurred: {str(e)}")
        raise
    finally:
        for query in queries:
            if query and query.isActive:
                query.stop()
        if spark:
            spark.stop()
            logger.info("Stopped Spark session")

if __name__ == "__main__":
    main()