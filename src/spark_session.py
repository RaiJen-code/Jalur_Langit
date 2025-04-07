from pyspark.sql import SparkSession

# Membuat SparkSession
spark = SparkSession.builder \
    .appName("KafkaSparkIntegration") \
    .getOrCreate()

# Membaca data dari Kafka
df = spark.readStream \
    .format("kafka") \
    .option("kafka.bootstrap.servers", "localhost:9092") \
    .option("subscribe", "monitoring_adw310_frekuensi") \
    .load()

# Konversi value dari Kafka ke string
df = df.selectExpr("CAST(key AS STRING)", "CAST(value AS STRING)")

# Menampilkan data secara real-time di console
query = df.writeStream \
    .outputMode("append") \
    .format("console") \
    .start()

query.awaitTermination()
