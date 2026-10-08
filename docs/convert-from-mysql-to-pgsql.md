-- PostgreSQL conversion of fleet7_db.sql

SET standard_conforming_strings = on;

-- Table structure for table addresses
CREATE TABLE addresses (
 id SERIAL PRIMARY KEY,
 customer_id INTEGER DEFAULT NULL,
 address TEXT DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX addresses_customer_id_index ON addresses (customer_id);

-- Dumping data for table addresses
INSERT INTO addresses VALUES ();

-- Table structure for table api_settings
CREATE TABLE api_settings (
 id SERIAL PRIMARY KEY,
 key_name VARCHAR(255) DEFAULT NULL,
 key_value TEXT DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX api_settings_key_name_index ON api_settings (key_name);

-- Dumping data for table api_settings
INSERT INTO api_settings VALUES (1,'api','1','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(2,'anyone_register','0','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(3,'region_availability','region o ne, region two, region three','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(4,'driver_review','0','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(5,'booking','3','2021-11-2 0 07:03:58','2021-11-20 07:03:58',NULL),(6,'cancel','2','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(7,'max_trip','1','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(8,'ap i_key','','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(9,'db_url','','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(10,'db_secret','','2021-11-20 07:03:58','2021-11-20 07 :03:58',NULL),(11,'server_key','','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(12,'google_api','0','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(13,'vendor_server_key',' ','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL),(14,'is_on_ven_app','0','2021-11-20 07:03:58','2021-11-20 07:03:58',NULL);

-- Table structure for table booking_income
CREATE TABLE booking_income (
 id SERIAL PRIMARY KEY,
 booking_id INTEGER DEFAULT NULL,
 income_id INTEGER DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX booking_income_booking_id_income_id_index ON booking_income (booking_id, income_id);

-- Dumping data for table booking_income
INSERT INTO booking_income VALUES (1,1,3,'2021-11-20 07:03:51','2021-11-20 07:03:51',NULL);

-- Table structure for table booking_payments
CREATE TABLE booking_payments (
 id SERIAL PRIMARY KEY,
 booking_id INTEGER DEFAULT NULL,
 method VARCHAR(255) DEFAULT NULL,
 transaction_id VARCHAR(255) DEFAULT NULL,
 amount DOUBLE PRECISION NOT NULL,
 payment_status VARCHAR(255) DEFAULT NULL,
 payment_details TEXT DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Table structure for table booking_quotation
CREATE TABLE booking_quotation (
 id BIGSERIAL PRIMARY KEY,
 customer_id INTEGER DEFAULT NULL,
 user_id INTEGER DEFAULT NULL,
 vehicle_id INTEGER DEFAULT NULL,
 driver_id INTEGER DEFAULT NULL,
 pickup TIMESTAMP NULL DEFAULT NULL,
 dropoff TIMESTAMP NULL DEFAULT NULL,
 pickup_addr VARCHAR(255) DEFAULT NULL,
 dest_addr VARCHAR(255) DEFAULT NULL,
 note TEXT DEFAULT NULL,
 travellers INTEGER NOT NULL DEFAULT 1,
 status INTEGER NOT NULL DEFAULT 0,
 payment INTEGER NOT NULL DEFAULT 0,
 day INTEGER DEFAULT NULL,
 mileage DOUBLE PRECISION DEFAULT NULL,
 waiting_time INTEGER DEFAULT NULL,
 total DOUBLE PRECISION DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 tax_total NUMERIC(10,2) DEFAULT NULL,
 total_tax_percent NUMERIC(10,2) DEFAULT NULL,
 total_tax_charge_rs NUMERIC(10,2) DEFAULT NULL
);
CREATE INDEX booking_quotation_customer_id_user_id_vehicle_id_driver_id_index ON booking_quotation (customer_id, user_id, vehicle_id, driver_id);
CREATE INDEX booking_quotation_status_payment_index ON booking_quotation (status, payment);

-- Table structure for table bookings
CREATE TABLE bookings (
 id SERIAL PRIMARY KEY,
 customer_id INTEGER DEFAULT NULL,
 user_id INTEGER DEFAULT NULL,
 vehicle_id INTEGER DEFAULT NULL,
 driver_id INTEGER DEFAULT NULL,
 pickup TIMESTAMP NULL DEFAULT NULL,
 dropoff TIMESTAMP NULL DEFAULT NULL,
 duration INTEGER DEFAULT NULL,
 pickup_addr VARCHAR(255) DEFAULT NULL,
 dest_addr VARCHAR(255) DEFAULT NULL,
 note TEXT DEFAULT NULL,
 travellers INTEGER NOT NULL DEFAULT 1,
 cancellation INTEGER NOT NULL DEFAULT 0,
 status INTEGER NOT NULL DEFAULT 0,
 payment INTEGER NOT NULL DEFAULT 0,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX bookings_customer_id_driver_id_vehicle_id_user_id_index ON bookings (customer_id, driver_id, vehicle_id, user_id);
CREATE INDEX bookings_payment_status_index ON bookings (payment, status);

-- Dumping data for table bookings
INSERT INTO bookings VALUES (1,4,1,1,6,'2021-11-09 23:29:07','2021-11-10 10:26:56',2880,'368 Matilda Landing Apt. 901
Prohaskatown, SD 24803','887 Sabina Parkway
North Robbie, GA 83557-9858','sample note',2,0,1,1,'2021-11-20 07:03:41','2021-11-20 07:03:51',NULL),(2,5,1,1,7,'2021-10-26 07:53:04','2021-10-27 10:42:16',2880,'28114 Bernhard Springs
McDermottberg, GA 86108','66878 Dora Mountains
Kenyastad, NY 63820','sample note',3,0,0,0,'2021-11-20 07:03:41','2021-11-20 07:03:41',NULL);

-- Table structure for table bookings_meta
CREATE TABLE bookings_meta (
 id SERIAL PRIMARY KEY,
 booking_id INTEGER NOT NULL,
 type VARCHAR(255) NOT NULL DEFAULT 'null',
 "key" VARCHAR(255) NOT NULL,
 value TEXT DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX bookings_meta_booking_id_index ON bookings_meta (booking_id);
CREATE INDEX bookings_meta_key_index ON bookings_meta ("key");

-- Dumping data for table bookings_meta
INSERT INTO bookings_meta VALUES (1,1,'integer','tax_total','500',NULL,'2021-11-20 07:03:51','2021-11-20 07:03:51'),(2,1,'integer','total_tax_percent','0',NULL,'2021-11-20 07:03:51','2021-11-20 07:03:51'), (3,1,'integer','total_tax_charge_rs','0',NULL,'2021-11-20 07:03:51','2021-11-20 07:03:51'),(4,1,'string','ride_status','Completed',NULL,'2021-11-20 07:03:52','2021-11-20 07:03:52') ,(5,1,'string','journey_date','10-11-2021',NULL,'2021-11-20 07:03:52','2021-11-20 07:03:52'),(6,1,'string','journey_time','04:59:07',NULL,'2021-11-20 07:03:52','2021-11-20 07:03:52 '),(7,1,'integer','customerid','4',NULL,'2021-11-20 07:03:52','2021-11-20 07:03:52'),(8,1,'integer','vehicleid','1',NULL,'2021-11-20 07:03:52','2021-11-20 07:03:52'),(9,1,'integer' ,'day','1',NULL,'2021-11-20 07:03:52','2021-11-20 07:03:52'),(10,1,'integer','mileage','10',NULL,'2021-11-20 07:03:52','2021-11-20 07:03:52'),(11,1,'integer','waiting_time','0',NUL L,'2021-11-20 07:03:52','2021-11-20 07:03:52'),(12,1,'string','date','2021-11-20',NULL,'2021-11-20 07:03:52','2021-11-20 07:03:52'),(13,1,'integer','total','500',NULL,'2021-11-20 0 7:03:52','2021-11-20 07:03:52'),(14,1,'integer','receipt','1',NULL,'2021-11-20 07:03:52','2021-11-20 07:03:52'),(15,2,'string','ride_status','Upcoming',NULL,'2021-11-20 07:03:52',' 2021-11-20 07:03:52'),(16,2,'string','journey_date','26-10-2021',NULL,'2021-11-20 07:03:52','2021-11-20 07:03:52'),(17,2,'string','journey_time','13:23:04',NULL,'2021-11-20 07:03:5 2','2021-11-20 07:03:52');

-- Table structure for table chat_settings
CREATE TABLE chat_settings (
 id BIGSERIAL PRIMARY KEY,
 name VARCHAR(255) DEFAULT NULL,
 value VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table chat_settings
INSERT INTO chat_settings VALUES (1,'pusher_app_id','','2022-01-23 23:35:30','2022-01-24 00:02:32',NULL),(2,'pusher_app_key','','2022-01-23 23:35:30','2022-01-24 00:02:32',NULL),(3,'pusher_app_secret','','2022-01-23 23:35:30','2022-01-24 00:02:32',NULL),(4,'pusher_app_cluster','','2022-01-23 23:35:30','2022-01-24 00:02:32',NULL);

-- Table structure for table company_services
CREATE TABLE company_services (
 id SERIAL PRIMARY KEY,
 title VARCHAR(255) DEFAULT NULL,
 image VARCHAR(255) DEFAULT NULL,
 description TEXT DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table company_services
INSERT INTO company_services VALUES (1,'Best price guranteed','fleet-bestprice.png','Lorem ipsum dolor sit amet, consectetur adipisicing elit.Neque at, nobis repudiandae dolores.',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45'),(2,'24/7 Customer care','fleet-care.png','Lorem ipsum dolor sit amet, consectetur adipisicing elit.Neque at, nobis repudiandae dolores.',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45'),(3,'Home pickups','fleet-homepickup.png','Lorem ipsum dolor sit amet, consectetur adipisicing elit.Neque at, nobis repudiandae dolores.',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45'),(4,'Easy Bookings','fleet-easybooking.png','Lorem ipsum dolor sit amet, consectetur adipisicing elit.Neque at, nobis repudiandae dolores.',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45');

-- Table structure for table driver_logs
CREATE TABLE driver_logs (
 id SERIAL PRIMARY KEY,
 vehicle_id INTEGER NOT NULL,
 driver_id INTEGER NOT NULL,
 date TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX driver_logs_driver_id_vehicle_id_index ON driver_logs (driver_id, vehicle_id);

-- Dumping data for table driver_logs
INSERT INTO driver_logs VALUES (1,1,6,'2021-11-20 07:03:53','2021-11-20 07:03:53','2021-11-20 07:03:53'),(2,2,8,'2021-11-22 23:02:01','2021-11-22 23:02:01','2021-11-22 23:02:01');

-- Table structure for table driver_vehicle
CREATE TABLE driver_vehicle (
 id SERIAL PRIMARY KEY,
 vehicle_id INTEGER DEFAULT NULL,
 driver_id INTEGER DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX driver_vehicle_driver_id_vehicle_id_index ON driver_vehicle (driver_id, vehicle_id);

-- Dumping data for table driver_vehicle
INSERT INTO driver_vehicle VALUES (1,1,6,'2021-11-20 07:03:53','2021-11-20 07:03:53'),(2,2,8,'2021-11-22 23:02:01','2021-11-22 23:02:01');

-- Table structure for table driver_payments
CREATE TABLE driver_payments (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 driver_id INTEGER NOT NULL,
 amount NUMERIC(10,2) NOT NULL DEFAULT 0.00,
 notes TEXT DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX driver_payments_driver_id_user_id_index ON driver_payments (driver_id, user_id);

-- Table structure for table email_content
CREATE TABLE email_content (
 id SERIAL PRIMARY KEY,
 "key" VARCHAR(255) DEFAULT NULL,
 value TEXT DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX email_content_key_index ON email_content ("key");

-- Dumping data for table email_content
INSERT INTO email_content VALUES (1,'insurance','vehicle insurance email content','2021-11-20 07:04:07','2021-11-20 07:04:07',NULL),(2,'vehicle_licence','vehicle licence email content','2021-11-20 07:04:07','2021-11-20 07:04:07',NULL),(3,'driving_licence','driving licence email content','2021-11-20 07:04:07','2021-11-20 07:04:07',NULL),(4,'registration','vehicle registration email content','2021-11-20 07:04:07','2021-11-20 07:04:07',NULL),(5,'service_reminder','service reminder email content','2021-11-20 07:04:07','2021-11-20 07:04:07',NULL),(6,'users','','2021-11-20 07:04:07','2021-11-20 07:04:07',NULL),(7,'options','','2021-11-20 07:04:07','2021-11-20 07:04:07',NULL),(8,'email','0','2021-11-20 07:04:07','2021-11-20 07:04:07',NULL);

-- Table structure for table expense
CREATE TABLE expense (
 id SERIAL PRIMARY KEY,
 vehicle_id INTEGER DEFAULT NULL,
 exp_id INTEGER DEFAULT NULL,
 type VARCHAR(10) NOT NULL DEFAULT 'e',
 amount NUMERIC(10,2) NOT NULL DEFAULT 0.00,
 driver_amount NUMERIC(10,2) DEFAULT NULL,
 user_id INTEGER DEFAULT NULL,
 expense_type INTEGER DEFAULT NULL,
 comment TEXT DEFAULT NULL,
 date DATE DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 vendor_id INTEGER DEFAULT NULL
);
CREATE INDEX expense_vehicle_id_exp_id_user_id_expense_type_index ON expense (vehicle_id, exp_id, user_id, expense_type);
CREATE INDEX expense_type_index ON expense (type);
CREATE INDEX expense_date_index ON expense (date);

-- Dumping data for table expense
INSERT INTO expense VALUES (1,1,NULL,'e',1763.00,0.00,2,1,'Sample Comment','2021-11-19','2021-11-20 07:03:50','2021-11-20 07:03:50',NULL,NULL),(2,2,NULL,'e',1312.00,0.00,3,4,'Sample Comment','2021-11-15','2021-11-20 07:03:51','2021-11-20 07:03:51',NULL,NULL),(3,1,1,'e',500.00,0.00,2,8,'Sample Comment','2021-11-18','2021-11-20 07:03:53','2021-11-20 07:03:53',NULL,NULL),(4,1,2,'e',500.00,0.00,2,8,'Sample Comment','2021-11-30','2021-11-20 07:03:53','2021-11-20 07:03:53',NULL,NULL);

-- Table structure for table expense_cat
CREATE TABLE expense_cat (
 id SERIAL PRIMARY KEY,
 name VARCHAR(100) DEFAULT NULL,
 user_id INTEGER DEFAULT NULL,
 type VARCHAR(5) DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX expense_cat_name_type_index ON expense_cat (name, type);

-- Dumping data for table expense_cat
INSERT INTO expense_cat VALUES (1,'Insurance',1,'d','2021-11-20 07:03:54','2021-11-20 07:03:54',NULL),(2,'Patente',1,'d','2021-11-20 07:03:54','2021-11-20 07:03:54',NULL),(3,'Mechanics',1,'d','2021-11-20 07:03:55','2021-11-20 07:03:55',NULL),(4,'Car wash',1,'d','2021-11-20 07:03:55','2021-11-20 07:03:55',NULL),(5,'Vignette',1,'d','2021-11-20 07:03:55','2021-11-20 07:03:55',NULL),(6,'Maintenance',1,'d','2021-11-20 07:03:55','2021-11-20 07:03:55',NULL),(7,'Parking',1,'d','2021-11-20 07:03:55','2021-11-20 07:03:55',NULL),(8,'Fuel',1,'d','2021-11-20 07:03:55','2021-11-20 07:03:55',NULL),(9,'Car Services',1,'d','2021-11-20 07:03:55','2021-11-20 07:03:55',NULL);

-- Table structure for table fare_settings
CREATE TABLE fare_settings (
 id SERIAL PRIMARY KEY,
 key_name VARCHAR(255) DEFAULT NULL,
 key_value TEXT DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 type_id INTEGER DEFAULT NULL
);
CREATE INDEX fare_settings_key_name_index ON fare_settings (key_name);
CREATE INDEX fare_settings_type_id_index ON fare_settings (type_id);

-- Dumping data for table fare_settings
INSERT INTO fare_settings VALUES (1,'hatchback_base_fare','500','2021-11-20 07:03:59','2021-11-20 07:03:59',NULL,1),(2,'hatchback_base_km','10','2021-11-20 07:03:59','2021-11-20 07:03:59',NULL,1),(3,'hatch back_base_time','2','2021-11-20 07:03:59','2021-11-20 07:03:59',NULL,1),(4,'hatchback_std_fare','20','2021-11-20 07:03:59','2021-11-20 07:03:59',NULL,1),(5,'hatchback_weekend_base_ fare','500','2021-11-20 07:03:59','2021-11-20 07:03:59',NULL,1),(6,'hatchback_weekend_base_km','10','2021-11-20 07:03:59','2021-11-20 07:03:59',NULL,1),(7,'hatchback_weekend_wait_t ime','2','2021-11-20 07:03:59','2021-11-20 07:03:59',NULL,1),(8,'hatchback_weekend_std_fare','20','2021-11-20 07:04:00','2021-11-20 07:04:00',NULL,1),(9,'hatchback_night_base_fare' ,'500','2021-11-20 07:04:00','2021-11-20 07:04:00',NULL,1),(10,'hatchback_night_base_km','10','2021-11-20 07:04:00','2021-11-20 07:04:00',NULL,1),(11,'hatchback_night_wait_time','2 ','2021-11-20 07:04:00','2021-11-20 07:04:00',NULL,1),(12,'hatchback_night_std_fare','20','2021-11-20 07:04:00','2021-11-20 07:04:00',NULL,1),(13,'sedan_base_fare','500','2021-11-2 0 07:04:00','2021-11-20 07:04:00',NULL,2),(14,'sedan_base_km','10','2021-11-20 07:04:00','2021-11-20 07:04:00',NULL,2),(15,'sedan_base_time','2','2021-11-20 07:04:00','2021-11-20 0 7:04:00',NULL,2),(16,'sedan_std_fare','20','2021-11-20 07:04:01','2021-11-20 07:04:01',NULL,2),(17,'sedan_weekend_base_fare','500','2021-11-20 07:04:01','2021-11-20 07:04:01',NULL, 2),(18,'sedan_weekend_base_km','10','2021-11-20 07:04:01','2021-11-20 07:04:01',NULL,2),(19,'sedan_weekend_wait_time','2','2021-11-20 07:04:01','2021-11-20 07:04:01',NULL,2),(20,'s edan_weekend_std_fare','20','2021-11-20 07:04:01','2021-11-20 07:04:01',NULL,2),(21,'sedan_night_base_fare','500','2021-11-20 07:04:01','2021-11-20 07:04:01',NULL,2),(22,'sedan_nig ht_base_km','10','2021-11-20 07:04:01','2021-11-20 07:04:01',NULL,2),(23,'sedan_night_wait_time','2','2021-11-20 07:04:01','2021-11-20 07:04:01',NULL,2),(24,'sedan_night_std_fare', '20','2021-11-20 07:04:02','2021-11-20 07:04:02',NULL,2),(25,'minivan_base_fare','500','2021-11-20 07:04:02','2021-11-20 07:04:02',NULL,3),(26,'minivan_base_km','10','2021-11-20 07 :04:02','2021-11-20 07:04:02',NULL,3),(27,'minivan_base_time','2','2021-11-20 07:04:02','2021-11-20 07:04:02',NULL,3),(28,'minivan_std_fare','20','2021-11-20 07:04:02','2021-11-20  07:04:02',NULL,3),(29,'minivan_weekend_base_fare','500','2021-11-20 07:04:03','2021-11-20 07:04:03',NULL,3),(30,'minivan_weekend_base_km','10','2021-11-20 07:04:03','2021-11-20 07: 04:03',NULL,3),(31,'minivan_weekend_wait_time','2','2021-11-20 07:04:03','2021-11-20 07:04:03',NULL,3),(32,'minivan_weekend_std_fare','20','2021-11-20 07:04:03','2021-11-20 07:04:0 3',NULL,3),(33,'minivan_night_base_fare','500','2021-11-20 07:04:03','2021-11-20 07:04:03',NULL,3),(34,'minivan_night_base_km','10','2021-11-20 07:04:03','2021-11-20 07:04:03',NULL ,3),(35,'minivan_night_wait_time','2','2021-11-20 07:04:03','2021-11-20 07:04:03',NULL,3),(36,'minivan_night_std_fare','20','2021-11-20 07:04:03','2021-11-20 07:04:03',NULL,3),(37, 'saloon_base_fare','500','2021-11-20 07:04:03','2021-11-20 07:04:03',NULL,4),(38,'saloon_base_km','10','2021-11-20 07:04:03','2021-11-20 07:04:03',NULL,4),(39,'saloon_base_time','2 ','2021-11-20 07:04:04','2021-11-20 07:04:04',NULL,4),(40,'saloon_std_fare','20','2021-11-20 07:04:04','2021-11-20 07:04:04',NULL,4),(41,'saloon_weekend_base_fare','500','2021-11-2 0 07:04:04','2021-11-20 07:04:04',NULL,4),(42,'saloon_weekend_base_km','10','2021-11-20 07:04:04','2021-11-20 07:04:04',NULL,4),(43,'saloon_weekend_wait_time','2','2021-11-20 07:04 :04','2021-11-20 07:04:04',NULL,4),(44,'saloon_weekend_std_fare','20','2021-11-20 07:04:04','2021-11-20 07:04:04',NULL,4),(45,'saloon_night_base_fare','500','2021-11-20 07:04:04',' 2021-11-20 07:04:04',NULL,4),(46,'saloon_night_base_km','10','2021-11-20 07:04:04','2021-11-20 07:04:04',NULL,4),(47,'saloon_night_wait_time','2','2021-11-20 07:04:04','2021-11-20  07:04:04',NULL,4),(48,'saloon_night_std_fare','20','2021-11-20 07:04:04','2021-11-20 07:04:04',NULL,4),(49,'suv_base_fare','500','2021-11-20 07:04:04','2021-11-20 07:04:04',NULL,5) ,(50,'suv_base_km','10','2021-11-20 07:04:04','2021-11-20 07:04:04',NULL,5),(51,'suv_base_time','2','2021-11-20 07:04:04','2021-11-20 07:04:04',NULL,5),(52,'suv_std_fare','20','202 1-11-20 07:04:04','2021-11-20 07:04:04',NULL,5),(53,'suv_weekend_base_fare','500','2021-11-20 07:04:04','2021-11-20 07:04:04',NULL,5),(54,'suv_weekend_base_km','10','2021-11-20 07: 04:05','2021-11-20 07:04:05',NULL,5),(55,'suv_weekend_wait_time','2','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,5),(56,'suv_weekend_std_fare','20','2021-11-20 07:04:05','2021 -11-20 07:04:05',NULL,5),(57,'suv_night_base_fare','500','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,5),(58,'suv_night_base_km','10','2021-11-20 07:04:05','2021-11-20 07:04:05 ',NULL,5),(59,'suv_night_wait_time','2','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,5),(60,'suv_night_std_fare','20','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,5),(61,'b us_base_fare','500','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,6),(62,'bus_base_km','10','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,6),(63,'bus_base_time','2','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,6),(64,'bus_std_fare','20','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,6),(65,'bus_weekend_base_fare','500','2021-11-20 07:04:05','2021 -11-20 07:04:05',NULL,6),(66,'bus_weekend_base_km','10','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,6),(67,'bus_weekend_wait_time','2','2021-11-20 07:04:05','2021-11-20 07:04: 05',NULL,6),(68,'bus_weekend_std_fare','20','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,6),(69,'bus_night_base_fare','500','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,6), (70,'bus_night_base_km','10','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,6),(71,'bus_night_wait_time','2','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,6),(72,'bus_night_st d_fare','20','2021-11-20 07:04:05','2021-11-20 07:04:05',NULL,6),(73,'truck_base_fare','500','2021-11-20 07:04:06','2021-11-20 07:04:06',NULL,7),(74,'truck_base_km','10','2021-11-2 0 07:04:06','2021-11-20 07:04:06',NULL,7),(75,'truck_base_time','2','2021-11-20 07:04:06','2021-11-20 07:04:06',NULL,7),(76,'truck_std_fare','20','2021-11-20 07:04:06','2021-11-20  07:04:06',NULL,7),(77,'truck_weekend_base_fare','500','2021-11-20 07:04:06','2021-11-20 07:04:06',NULL,7),(78,'truck_weekend_base_km','10','2021-11-20 07:04:06','2021-11-20 07:04:0 6',NULL,7),(79,'truck_weekend_wait_time','2','2021-11-20 07:04:06','2021-11-20 07:04:06',NULL,7),(80,'truck_weekend_std_fare','20','2021-11-20 07:04:06','2021-11-20 07:04:06',NULL, 7),(81,'truck_night_base_fare','500','2021-11-20 07:04:06','2021-11-20 07:04:06',NULL,7),(82,'truck_night_base_km','10','2021-11-20 07:04:06','2021-11-20 07:04:06',NULL,7),(83,'tru ck_night_wait_time','2','2021-11-20 07:04:06','2021-11-20 07:04:06',NULL,7),(84,'truck_night_std_fare','20','2021-11-20 07:04:06','2021-11-20 07:04:06',NULL,7);

-- Table structure for table frontend
CREATE TABLE frontend (
 id SERIAL PRIMARY KEY,
 key_name VARCHAR(255) DEFAULT NULL,
 key_value TEXT DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX frontend_key_name_index ON frontend (key_name);

-- Dumping data for table frontend
INSERT INTO frontend VALUES (1,'about_us','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','2021-11-20 07:04:08','2021-11-2 0 07:04:08',NULL),(2,'contact_email','master@admin.com','2021-11-20 07:04:08','2021-11-20 07:04:08',NULL),(3,'contact_phone','0123456789','2021-11-20 07:04:08','2021-11-20 07:04:08 ',NULL),(4,'customer_support','0999988888','2021-11-20 07:04:08','2021-11-20 07:04:08',NULL),(5,'about_description','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do  eiusmod tempor incididunt ut labore et dolore magna aliqua.','2021-11-20 07:04:08','2021-11-20 07:04:08',NULL),(6,'about_title','Proudly serving you','2021-11-20 07:04:08','2021-1 1-20 07:04:08',NULL),(7,'facebook','https://www.facebook.com/hyvikk/','2021-11-20 07:04:08','2021-11-20 07:04:08',NULL),(8,'twitter','https://x.com/hyvikks?mx=2','2021-11-20 07:04: 08','2021-11-20 07:04:08',NULL),(9,'instagram',NULL,'2021-11-20 07:04:08','2021-11-20 07:04:08',NULL),(10,'linkedin',NULL,'2021-11-20 07:04:08','2021-11-20 07:04:08',NULL),(11,'faq _link',NULL,'2021-11-20 07:04:08','2021-11-20 07:04:08',NULL),(12,'cities','5','2021-11-20 07:04:08','2021-11-20 07:04:08',NULL),(13,'vehicles','10','2021-11-20 07:04:08','2021-11-20 07:04:08',NULL),(14,'cancellation',NULL,'2021-11-20 07:04:08','2021-11-20 07:04:08',NULL),(15,'terms',NULL,'2021-11-20 07:04:09','2021-11-20 07:04:09',NULL),(16,'privacy_policy' ,NULL,'2021-11-20 07:04:09','2021-11-20 07:04:09',NULL),(17,'enable','1','2021-11-20 07:04:09','2021-11-20 07:04:09',NULL),(18,'language','English-en','2021-11-20 07:04:09','2021-1 1-20 07:04:09',NULL),(19,'admin_approval','1','2021-11-20 07:04:09','2021-11-20 07:04:09',NULL),(20,'booking_time','1','2022-01-05 16:00:09','2022-01-05 16:00:09',NULL),
(21,'foote r_link','[{ "title ": "Company ", "url ": "https://fleetdemo.hyvikk.solutions/ "},{ "title ": "About ", "url ": "https://fleetdemo.hyvikk.solutions/about "}]','2022-01-05 16:00:09','2022-01-05 16:00:09',NULL),
(22,'sign_up_title','Mobile App  & Web Based Solution','2022-01-05 16:00:09','2022-01-05 16:00:09',NULL),
(23,'sign_up_content','[{ "file_path ": "dark_mode.svg ", "title ": "Stunning Dark Theme ", "subtitle ": "Give a Stunning Dark  & Bold Version to your Fleet Manager Admin. "},{ "file_path ": "search.svg ", "title ": "Sidebar Search ", "subtitle ": "Search any Module / Section with Just a Few Key Presses. "},{ "file_path ": "upgrade.svg ", "title ": "Upgraded Front-end Website ", "subtitle ": "A Revamped Front-end UI Design to give you a Fresh Experience. "},{ "file_path ": "menu.svg ", "title ": "The Awesome Font Awesome Icons ", "subtitle ": "Because Good Icons Represent Features Better. "}]
','2022-01-05 16:00:09','2022-01-05 16:00:09',NULL),
(24,'sign_up_sub_title','A better way to Manage Fleets  & Vehicle Bookings','2022-01-05 16:00:09','2022-01-05 16:00:09',NULL);

-- Table structure for table fuel
CREATE TABLE fuel (
 id SERIAL PRIMARY KEY,
 vehicle_id INTEGER DEFAULT NULL,
 user_id INTEGER DEFAULT NULL,
 start_meter VARCHAR(255) DEFAULT NULL,
 end_meter VARCHAR(255) DEFAULT NULL,
 reference VARCHAR(255) DEFAULT NULL,
 province VARCHAR(255) DEFAULT NULL,
 note TEXT DEFAULT NULL,
 vendor_name VARCHAR(255) DEFAULT NULL,
 qty NUMERIC(10,2) DEFAULT NULL,
 fuel_from VARCHAR(255) DEFAULT NULL,
 cost_per_unit VARCHAR(255) DEFAULT NULL,
 consumption INTEGER DEFAULT NULL,
 complete INTEGER DEFAULT 0,
 date DATE DEFAULT NULL,
 image VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX fuel_vehicle_id_user_id_index ON fuel (vehicle_id, user_id);
CREATE INDEX fuel_date_index ON fuel (date);

-- Dumping data for table fuel
INSERT INTO fuel VALUES (1,1,2,'1000','2000',NULL,'Gujarat','sample note',NULL,10,'Fuel Tank','50',100,0,'2021-11-18',NULL,'2021-11-20 07:03:53','2021-11-20 07:03:53',NULL),(2,1,2,'2000','0',NULL,'Gujarat','sample note',NULL,10,'Fuel Tank','50',0,0,'2021-11-30',NULL,'2021-11-20 07:03:53','2021-11-20 07:03:53',NULL);

-- Table structure for table income
CREATE TABLE income (
 id SERIAL PRIMARY KEY,
 vehicle_id INTEGER DEFAULT NULL,
 income_id INTEGER DEFAULT NULL,
 amount NUMERIC(10,2) NOT NULL DEFAULT 0.00,
 driver_amount NUMERIC(10,2) DEFAULT NULL,
 user_id INTEGER DEFAULT NULL,
 income_cat INTEGER DEFAULT NULL,
 mileage INTEGER DEFAULT NULL,
 date DATE DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 tax_percent NUMERIC(10,2) DEFAULT NULL,
 tax_charge_rs NUMERIC(10,2) DEFAULT NULL
);
CREATE INDEX income_vehicle_id_income_id_user_id_income_cat_index ON income (vehicle_id, income_id, user_id, income_cat);
CREATE INDEX income_date_index ON income (date);

-- Dumping data for table income
INSERT INTO income VALUES (1,1,NULL,4018.00,0.00,2,1,NULL,'2021-11-15','2021-11-20 07:03:50','2021-11-20 07:03:50',NULL,0.00,0.00),(2,2,NULL,3801.00,0.00,3,1,NULL,'2021-11-19','2021-11-20 07:03:51','2021-11-20 07:03:51',NULL,0.00,0.00),(3,1,1,500.00,0.00,1,1,10,'2021-11-20','2021-11-20 07:03:51','2021-11-20 07:03:51',NULL,0.00,0.00);

-- Table structure for table income_cat
CREATE TABLE income_cat (
 id SERIAL PRIMARY KEY,
 name VARCHAR(100) DEFAULT NULL,
 user_id INTEGER DEFAULT NULL,
 type VARCHAR(5) DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX income_cat_name_type_index ON income_cat (name, type);

-- Dumping data for table income_cat
INSERT INTO income_cat VALUES (1,'Booking',1,'d','2021-11-20 07:03:54','2021-11-20 07:03:54',NULL);

-- Table structure for table mechanics
CREATE TABLE mechanics (
 id BIGSERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 name VARCHAR(255) DEFAULT NULL,
 email VARCHAR(255) DEFAULT NULL,
 contact_number VARCHAR(255) DEFAULT NULL,
 category VARCHAR(255) DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table mechanics
INSERT INTO mechanics VALUES (1,1,'Tracy Lakin','connelly.mckenna@example.com','1-350-561-3982 x177','Electrical Engineering',NULL,'2021-11-20 07:04:11','2021-11-20 07:04:11'),(2,1,'Theresa Toy','chris.haley@example.net','734-670-6060','Electrical Engineering',NULL,'2021-11-20 07:04:12','2021-11-20 07:04:12');

-- Table structure for table message
CREATE TABLE message (
 id SERIAL PRIMARY KEY,
 fcm_id VARCHAR(255) DEFAULT NULL,
 user_id INTEGER DEFAULT NULL,
 message TEXT DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 name VARCHAR(255) DEFAULT NULL,
 email VARCHAR(255) DEFAULT NULL
);

-- Table structure for table messages
CREATE TABLE messages (
 id SERIAL PRIMARY KEY,
 from_user INTEGER DEFAULT NULL,
 to_user INTEGER DEFAULT NULL,
 content TEXT NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX from_user ON messages (from_user);
CREATE INDEX to_user ON messages (to_user);

-- Table structure for table migrations
CREATE TABLE migrations (
 id SERIAL PRIMARY KEY,
 migration VARCHAR(255) NOT NULL,
 batch INTEGER NOT NULL
);

-- Dumping data for table migrations
INSERT INTO migrations VALUES (1,'2016_06_01_000001_create_oauth_auth_codes_table',1),(2,'2016_06_01_000002_create_oauth_access_tokens_table',1),(3,'2016_06_01_000003_create_oauth_refresh_tokens_table', 1),(4,'2016_06_01_000004_create_oauth_clients_table',1),(5,'2016_06_01_000005_create_oauth_personal_access_clients_table',1),(6,'2017_06_03_134331_create_expense_table',1),(7,'2017 _06_03_134332_create_expense_cat_table',1),(8,'2017_06_03_134332_create_income_table',1),(9,'2017_06_03_134333_create_income_cat_table',1),(10,'2017_06_03_134336_create_password_re sets_table',1),(11,'2017_06_03_134337_create_users_table',1),(12,'2017_06_03_134338_create_vehicles_table',1),(13,'2017_07_24_080537_create_booking_table',1),(14,'2017_07_24_080643 _create_settings_table',1),(15,'2017_08_01_073926_create_booking_income_table',1),(16,'2017_10_30_064357_create_notifications_table',1),(17,'2017_10_30_094858_create_fuel_table',1) ,(18,'2017_11_09_105729_create_vendors_table',1),(19,'2017_11_10_062609_create_work_orders_table',1),(20,'2017_11_10_095438_create_notes_table',1),(21,'2017_11_22_093559_create_veh icle_group_table',1),(22,'2017_12_28_091600_create_service_items_table',1),(23,'2017_12_28_122952_create_service_reminder_table',1),(24,'2017_12_28_174333_create_api_settings_table ',1),(25,'2018_01_08_062105_create_driver_vehicle_table',1),(26,'2018_01_10_130517_users_meta',1),(27,'2018_01_13_050018_bookings_meta',1),(28,'2018_01_16_095657_fare_settings',1), (29,'2018_01_25_050939_create_vehicles_meta_table',1),(30,'2018_02_06_052302_create_message_table',1),(31,'2018_02_06_125252_create_reviews_table',1),(32,'2018_03_13_124424_create_ addresses_table',1),(33,'2018_03_28_085735_create_reasons_table',1),(34,'2018_04_28_073004_create_email_content_table',1),(35,'2018_08_14_061757_create_vehicle_review_table',1),(36 ,'2019_01_18_063916_add_vendor_id_to_expense',1),(37,'2019_01_19_080738_add_udf_to_vendors',1),(38,'2019_01_19_103826_create_parts_table',1),(39,'2019_01_19_110823_create_vehicle_t ypes_table',1),(40,'2019_01_22_101948_create_driver_logs_table',1),(41,'2019_01_23_113852_add_type_id_to_vehicles_table',1),(42,'2019_01_24_095115_add_type_id_to_fare_settings_tabl e',1),(43,'2019_04_12_092111_create_parts_category_table',1),(44,'2019_04_19_053314_create_work_order_logs_table',1),(45,'2019_05_13_062039_create_push_notification_table',1),(46,' 2019_07_18_110031_add_column_to_vendors',1),(47,'2019_07_31_082514_create_testimonials_table',1),(48,'2019_07_31_102801_create_frontend_table',1),(49,'2019_08_01_045837_add_columns _to_message_table',1),(50,'2019_08_19_101509_create_booking_quotation_table',1),(51,'2019_08_22_052138_create_parts_used_table',1),(52,'2019_08_22_113138_add_parts_price_to_work_or der_logs_table',1),(53,'2019_08_29_104613_create_company_services_table',1),(54,'2019_09_16_085700_create_teams_table',1),(55,'2019_12_10_083547_add_columns_to_booking_quotation_ta ble',1),(56,'2019_12_16_064152_add_indexes_to_users_table',1),(57,'2019_12_16_064951_add_indexes_to_addresses_table',1),(58,'2019_12_16_065511_add_indexes_to_bookings_table',1),(59 ,'2019_12_16_083315_add_indexes_to_booking_income_table',1),(60,'2019_12_16_084539_add_indexes_to_booking_quotation_table',1),(61,'2019_12_16_085312_add_indexes_to_driver_logs_tabl e',1),(62,'2019_12_16_085505_add_indexes_to_driver_vehicle_table',1),(63,'2019_12_16_091010_add_indexes_to_email_content_table',1),(64,'2019_12_16_091713_add_indexes_to_expense_tab le',1),(65,'2019_12_16_094305_add_indexes_to_expense_cat_table',1),(66,'2019_12_16_094651_add_indexes_to_fare_settings_table',1),(67,'2019_12_16_095024_add_indexes_to_frontend_tabl e',1),(68,'2019_12_16_095339_add_indexes_to_fuel_table',1),(69,'2019_12_16_095634_add_indexes_to_income_table',1),(70,'2019_12_16_095953_add_indexes_to_income_cat_table',1),(71,'20 19_12_16_100221_add_indexes_to_notes_table',1),(72,'2019_12_16_100437_add_indexes_to_notifications_table',1),(73,'2019_12_16_100545_add_indexes_to_parts_table',1),(74,'2019_12_16_1 01113_add_indexes_to_parts_used_table',1),(75,'2019_12_16_101540_add_indexes_to_push_notification_table',1),(76,'2019_12_16_101851_add_indexes_to_reviews_table',1),(77,'2019_12_16_ 102259_add_indexes_to_service_reminder_table',1),(78,'2019_12_16_102555_add_indexes_to_vehicles_table',1),(79,'2019_12_16_104209_add_indexes_to_vehicle_review_table',1),(80,'2019_1 2_16_104440_add_indexes_to_vendors_table',1),(81,'2019_12_16_104704_add_indexes_to_work_orders_table',1),(82,'2019_12_16_105013_add_indexes_to_work_order_logs_table',1),(83,'2019_1 2_16_115309_add_indexes_to_api_settings_table',1),(84,'2019_12_17_080649_add_taxes_to_income_table',1),(85,'2019_12_19_052248_create_payment_settings_table',1),(86,'2019_12_19_0635 20_create_booking_payments_table',1),(87,'2021_01_04_113449_create_twilio_settings_table',1),(88,'2021_06_29_052236_add_udf_field_to_vehicle_review_table',1),(89,'2021_06_29_115538 _create_mechanics_table',1),(90,'2021_07_02_051340_create_permission_tables',1),(91,'2021_07_02_052117_add_mechanic_work_order_table',1),(92,'2021_07_02_055514_add_mechanic_work_or der_log_table',1),(93,'2021_07_22_071412_create_push_subscriptions_table',1),(94,'2021_07_22_113433_add_provider_to_oauth_clients_table',1),(95,'2021_08_27_121756_add_user_id_to_me chanics_table',1),(96,'2021_08_27_121856_add_user_id_to_parts_category_table',1),(97,'2021_08_27_121941_add_user_id_to_service_items_table',1),(98,'2021_08_27_122008_add_user_id_to _service_reminder_table',1),(99,'2021_08_27_122045_add_user_id_to_vehicle_group_table',1),(100,'2021_08_27_122127_add_user_id_to_vendors_table',1),(101,'2021_08_27_122155_add_user_ id_to_work_orders_table',1),(102,'2021_08_27_122217_add_user_id_to_work_order_logs_table',1),(103,'2021_08_27_122259_add_user_id_to_notes_table',1),(104,'2021_09_07_070458_add_user _id_to_users_table',1),(105,'2021_08_07_063711_create_messages_table',1),(106,'2022_01_17_065748_create_chat_settings_table',1);

-- Table structure for table model_has_permissions
CREATE TABLE model_has_permissions (
 permission_id BIGINT NOT NULL,
 model_type VARCHAR(255) NOT NULL,
 model_id BIGINT NOT NULL,
 PRIMARY KEY (permission_id, model_id, model_type),
 CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
);
CREATE INDEX model_has_permissions_model_id_model_type_index ON model_has_permissions (model_id, model_type);

-- Dumping data for table model_has_permissions
INSERT INTO model_has_permissions VALUES (9,'App\Model\User',6),(9,'App\Model\User',7),(9,'App\Model\User',8),(41,'App\Model\User',6),(41,'App\Model\User',7),(41,'App\Model\User',8),(66,'App\Model\User',6),(66,'Ap p\Model\User',7),(66,'App\Model\User',8),(67,'App\Model\User',6),(67,'App\Model\User',7),(67,'App\Model\User',8),(68,'App\Model\User',6),(68,'App\Model\User',7),(68,'App\Model\User ',8),(69,'App\Model\User',6),(69,'App\Model\User',7),(69,'App\Model\User',8),(71,'App\Model\User',4),(71,'App\Model\User',5),(72,'App\Model\User',4),(72,'App\Model\User',5),(73,'Ap p\Model\User',4),(73,'App\Model\User',5),(74,'App\Model\User',4),(74,'App\Model\User',5),(81,'App\Model\User',6),(81,'App\Model\User',7),(81,'App\Model\User',8),(82,'App\Model\User ',6),(82,'App\Model\User',7),(82,'App\Model\User',8),(83,'App\Model\User',6),(83,'App\Model\User',7),(83,'App\Model\User',8),(84,'App\Model\User',6),(84,'App\Model\User',7),(84,'Ap p\Model\User',8),(101,'App\Model\User',6),(101,'App\Model\User',7),(101,'App\Model\User',8),(102,'App\Model\User',6),(102,'App\Model\User',7),(102,'App\Model\User',8),(103,'App\Mod el\User',6),(103,'App\Model\User',7),(103,'App\Model\User',8),(104,'App\Model\User',6),(104,'App\Model\User',7),(104,'App\Model\User',8);

-- Table structure for table model_has_roles
CREATE TABLE model_has_roles (
 role_id BIGINT NOT NULL,
 model_type VARCHAR(255) NOT NULL,
 model_id BIGINT NOT NULL,
 PRIMARY KEY (role_id, model_id, model_type),
 CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
);
CREATE INDEX model_has_roles_model_id_model_type_index ON model_has_roles (model_id, model_type);

-- Dumping data for table model_has_roles
INSERT INTO model_has_roles VALUES (1,'App\Model\User',1),(2,'App\Model\User',2),(2,'App\Model\User',3);

-- Table structure for table notes
CREATE TABLE notes (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 vehicle_id INTEGER DEFAULT NULL,
 customer_id INTEGER DEFAULT NULL,
 note TEXT DEFAULT NULL,
 submitted_on DATE DEFAULT NULL,
 status VARCHAR(255) DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX notes_vehicle_id_customer_id_index ON notes (vehicle_id, customer_id);

-- Table structure for table notifications
CREATE TABLE notifications (
 id CHAR(36) NOT NULL,
 type VARCHAR(255) NOT NULL,
 notifiable_type VARCHAR(255) NOT NULL,
 notifiable_id BIGINT NOT NULL,
 data TEXT NOT NULL,
 read_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 PRIMARY KEY (id)
);
CREATE INDEX notifications_notifiable_type_notifiable_id_index ON notifications (notifiable_type, notifiable_id);
CREATE INDEX notifications_type_index ON notifications (type);

-- Table structure for table oauth_access_tokens
CREATE TABLE oauth_access_tokens (
 id VARCHAR(100) NOT NULL,
 user_id BIGINT DEFAULT NULL,
 client_id INTEGER NOT NULL,
 name VARCHAR(255) DEFAULT NULL,
 scopes TEXT DEFAULT NULL,
 revoked SMALLINT NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 expires_at TIMESTAMP DEFAULT NULL,
 PRIMARY KEY (id)
);
CREATE INDEX oauth_access_tokens_user_id_index ON oauth_access_tokens (user_id);

-- Table structure for table oauth_auth_codes
CREATE TABLE oauth_auth_codes (
 id VARCHAR(100) NOT NULL,
 user_id BIGINT NOT NULL,
 client_id INTEGER NOT NULL,
 scopes TEXT DEFAULT NULL,
 revoked SMALLINT NOT NULL,
 expires_at TIMESTAMP DEFAULT NULL,
 PRIMARY KEY (id)
);

-- Table structure for table oauth_clients
CREATE TABLE oauth_clients (
 id SERIAL PRIMARY KEY,
 user_id BIGINT DEFAULT NULL,
 name VARCHAR(255) NOT NULL,
 secret VARCHAR(100) NOT NULL,
 provider VARCHAR(255) DEFAULT NULL,
 redirect TEXT NOT NULL,
 personal_access_client SMALLINT NOT NULL,
 password_client SMALLINT NOT NULL,
 revoked SMALLINT NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX oauth_clients_user_id_index ON oauth_clients (user_id);

-- Dumping data for table oauth_clients
INSERT INTO oauth_clients VALUES (1,NULL,'Fleet Manager Personal Access Client','RSgOa49VlbquB3GxT1WAO2jReLCnHoWndPfyrJ4p',NULL,'http://localhost',1,0,0,'2021-11-20 07:03:53','2021-11-20 07:03:53'),(2,NULL,'Fleet Manager Password Grant Client','sX7qzt55VQ5pGjl4gkxyycwKz9yE6ngT4EoPEtRH','users','http://localhost',0,1,0,'2021-11-20 07:03:53','2021-11-20 07:03:53');

-- Table structure for table oauth_personal_access_clients
CREATE TABLE oauth_personal_access_clients (
 id SERIAL PRIMARY KEY,
 client_id INTEGER NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX oauth_personal_access_clients_client_id_index ON oauth_personal_access_clients (client_id);

-- Dumping data for table oauth_personal_access_clients
INSERT INTO oauth_personal_access_clients VALUES (1,1,'2021-11-20 07:03:53','2021-11-20 07:03:53');

-- Table structure for table oauth_refresh_tokens
CREATE TABLE oauth_refresh_tokens (
 id VARCHAR(100) NOT NULL,
 access_token_id VARCHAR(100) NOT NULL,
 revoked SMALLINT NOT NULL,
 expires_at TIMESTAMP DEFAULT NULL,
 PRIMARY KEY (id)
);
CREATE INDEX oauth_refresh_tokens_access_token_id_index ON oauth_refresh_tokens (access_token_id);

-- Table structure for table parts
CREATE TABLE parts (
 id SERIAL PRIMARY KEY,
 category_id INTEGER DEFAULT NULL,
 user_id INTEGER DEFAULT NULL,
 status VARCHAR(255) DEFAULT NULL,
 availability INTEGER DEFAULT NULL,
 title VARCHAR(255) DEFAULT NULL,
 year VARCHAR(255) DEFAULT NULL,
 model VARCHAR(255) DEFAULT NULL,
 image VARCHAR(255) DEFAULT NULL,
 barcode VARCHAR(255) DEFAULT NULL,
 number VARCHAR(255) DEFAULT NULL,
 description VARCHAR(255) DEFAULT NULL,
 unit_cost INTEGER DEFAULT NULL,
 vendor_id INTEGER DEFAULT NULL,
 manufacturer VARCHAR(255) DEFAULT NULL,
 note TEXT DEFAULT NULL,
 stock INTEGER DEFAULT NULL,
 udf TEXT DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX parts_category_id_user_id_availability_index ON parts (category_id, user_id, availability);

-- Table structure for table parts_category
CREATE TABLE parts_category (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 name VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table parts_category
INSERT INTO parts_category VALUES (1,1,'Engine Parts','2021-11-20 07:03:50','2021-11-20 07:03:50',NULL),(2,1,'Electricals','2021-11-20 07:03:50','2021-11-20 07:03:50',NULL);

-- Table structure for table parts_used
CREATE TABLE parts_used (
 id SERIAL PRIMARY KEY,
 part_id INTEGER DEFAULT NULL,
 work_id INTEGER DEFAULT NULL,
 qty INTEGER DEFAULT NULL,
 price DOUBLE PRECISION DEFAULT NULL,
 total DOUBLE PRECISION DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX parts_used_part_id_work_id_index ON parts_used (part_id, work_id);

-- Table structure for table password_resets
CREATE TABLE password_resets (
 email VARCHAR(255) NOT NULL,
 token VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX password_resets_email_index ON password_resets (email);

-- Table structure for table payment_settings
CREATE TABLE payment_settings (
 id SERIAL PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 value TEXT DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 UNIQUE (name)
);

-- Dumping data for table payment_settings
INSERT INTO payment_settings VALUES (1,'method','["cash"]','2021-11-20 07:04:09','2021-11-20 07:04:09',NULL),(2,'currency_code','INR','2021-11-20 07:04:10','2021-11-20 07:04:10',NULL),(3,'stripe_publishable_key','','2021-11-20 07:04:10','2021-11-20 07:04:10',NULL),(4,'stripe_secret_key','','2021-11-20 07:04:10','2021-11-20 07:04:10',NULL),(5,'razorpay_key','','2021-11-20 07:04:10','2021-11-20 07:04:10',NULL),(6,'razorpay_secret','','2021-11-20 07:04:10','2021-11-20 07:04:10',NULL),(7,'paystack_secret','','2021-11-20 07:04:10','2021-11-20 07:04:10',NULL);

-- Table structure for table permissions
CREATE TABLE permissions (
 id BIGSERIAL PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 guard_name VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table permissions
INSERT INTO permissions VALUES (1,'Users add','web','2021-11-20 07:04:15','2021-11-20 07:04:15'),(2,'Users edit','web','2021-11-20 07:04:15','2021-11-20 07:04:15'),(3,'Users delete','web','2021-11-20 07: 04:15','2021-11-20 07:04:15'),(4,'Users list','web','2021-11-20 07:04:15','2021-11-20 07:04:15'),(5,'Users import','web','2021-11-20 07:04:15','2021-11-20 07:04:15'),(6,'Drivers ad d','web','2021-11-20 07:04:15','2021-11-20 07:04:15'),(7,'Drivers edit','web','2021-11-20 07:04:15','2021-11-20 07:04:15'),(8,'Drivers delete','web','2021-11-20 07:04:15','2021-11-20 07:04:15'),(9,'Drivers list','web','2021-11-20 07:04:15','2021-11-20 07:04:15'),(10,'Drivers import','web','2021-11-20 07:04:15','2021-11-20 07:04:15'),(11,'Customer add','web', '2021-11-20 07:04:15','2021-11-20 07:04:15'),(12,'Customer edit','web','2021-11-20 07:04:16','2021-11-20 07:04:16'),(13,'Customer delete','web','2021-11-20 07:04:16','2021-11-20 07 :04:16'),(14,'Customer list','web','2021-11-20 07:04:16','2021-11-20 07:04:16'),(15,'Customer import','web','2021-11-20 07:04:16','2021-11-20 07:04:16'),(16,'VehicleType add','web' ,'2021-11-20 07:04:16','2021-11-20 07:04:16'),(17,'VehicleType edit','web','2021-11-20 07:04:16','2021-11-20 07:04:16'),(18,'VehicleType delete','web','2021-11-20 07:04:16','2021-1 1-20 07:04:16'),(19,'VehicleType list','web','2021-11-20 07:04:16','2021-11-20 07:04:16'),(20,'VehicleType import','web','2021-11-20 07:04:16','2021-11-20 07:04:16'),(21,'VehicleMa ker add','web','2021-11-20 07:04:16','2021-11-20 07:04:16'),(22,'VehicleMaker edit','web','2021-11-20 07:04:16','2021-11-20 07:04:16'),(23,'VehicleMaker delete','web','2021-11-20 0 7:04:16','2021-11-20 07:04:16'),(24,'VehicleMaker list','web','2021-11-20 07:04:17','2021-11-20 07:04:17'),(25,'VehicleMaker import','web','2021-11-20 07:04:17','2021-11-20 07:04:1 7'),(26,'VehicleModels add','web','2021-11-20 07:04:17','2021-11-20 07:04:17'),(27,'VehicleModels edit','web','2021-11-20 07:04:17','2021-11-20 07:04:17'),(28,'VehicleModels delete ','web','2021-11-20 07:04:17','2021-11-20 07:04:17'),(29,'VehicleModels list','web','2021-11-20 07:04:17','2021-11-20 07:04:17'),(30,'VehicleModels import','web','2021-11-20 07:04: 17','2021-11-20 07:04:17'),(31,'VehicleColors add','web','2021-11-20 07:04:17','2021-11-20 07:04:17'),(32,'VehicleColors edit','web','2021-11-20 07:04:17','2021-11-20 07:04:17'),(3 3,'VehicleColors delete','web','2021-11-20 07:04:17','2021-11-20 07:04:17'),(34,'VehicleColors list','web','2021-11-20 07:04:17','2021-11-20 07:04:17'),(35,'VehicleColors import',' web','2021-11-20 07:04:17','2021-11-20 07:04:17'),(36,'VehicleGroup add','web','2021-11-20 07:04:18','2021-11-20 07:04:18'),(37,'VehicleGroup edit','web','2021-11-20 07:04:18','202 1-11-20 07:04:18'),(38,'VehicleGroup delete','web','2021-11-20 07:04:18','2021-11-20 07:04:18'),(39,'VehicleGroup list','web','2021-11-20 07:04:18','2021-11-20 07:04:18'),(40,'Vehi cleGroup import','web','2021-11-20 07:04:18','2021-11-20 07:04:18'),(41,'VehicleInspection add','web','2021-11-20 07:04:18','2021-11-20 07:04:18'),(42,'VehicleInspection edit','web ','2021-11-20 07:04:18','2021-11-20 07:04:18'),(43,'VehicleInspection delete','web','2021-11-20 07:04:18','2021-11-20 07:04:18'),(44,'VehicleInspection list','web','2021-11-20 07:0 4:18','2021-11-20 07:04:18'),(45,'VehicleInspection import','web','2021-11-20 07:04:18','2021-11-20 07:04:18'),(46,'BookingQuotations add','web','2021-11-20 07:04:18','2021-11-20 0 7:04:18'),(47,'BookingQuotations edit','web','2021-11-20 07:04:18','2021-11-20 07:04:18'),(48,'BookingQuotations delete','web','2021-11-20 07:04:19','2021-11-20 07:04:19'),(49,'Boo kingQuotations list','web','2021-11-20 07:04:19','2021-11-20 07:04:19'),(50,'BookingQuotations import','web','2021-11-20 07:04:19','2021-11-20 07:04:19'),(51,'PartsCategory add','w eb','2021-11-20 07:04:19','2021-11-20 07:04:19'),(52,'PartsCategory edit','web','2021-11-20 07:04:19','2021-11-20 07:04:19'),(53,'PartsCategory delete','web','2021-11-20 07:04:19', '2021-11-20 07:04:19'),(54,'PartsCategory list','web','2021-11-20 07:04:19','2021-11-20 07:04:19'),(55,'PartsCategory import','web','2021-11-20 07:04:19','2021-11-20 07:04:19'),(56 ,'Mechanics add','web','2021-11-20 07:04:19','2021-11-20 07:04:19'),(57,'Mechanics edit','web','2021-11-20 07:04:20','2021-11-20 07:04:20'),(58,'Mechanics delete','web','2021-11-20  07:04:20','2021-11-20 07:04:20'),(59,'Mechanics list','web','2021-11-20 07:04:20','2021-11-20 07:04:20'),(60,'Mechanics import','web','2021-11-20 07:04:20','2021-11-20 07:04:20'), (61,'Vehicles add','web','2021-11-20 07:04:20','2021-11-20 07:04:20'),(62,'Vehicles edit','web','2021-11-20 07:04:20','2021-11-20 07:04:20'),(63,'Vehicles delete','web','2021-11-20  07:04:20','2021-11-20 07:04:20'),(64,'Vehicles list','web','2021-11-20 07:04:20','2021-11-20 07:04:20'),(65,'Vehicles import','web','2021-11-20 07:04:20','2021-11-20 07:04:20'),(6 6,'Transactions add','web','2021-11-20 07:04:20','2021-11-20 07:04:20'),(67,'Transactions edit','web','2021-11-20 07:04:21','2021-11-20 07:04:21'),(68,'Transactions delete','web',' 2021-11-20 07:04:21','2021-11-20 07:04:21'),(69,'Transactions list','web','2021-11-20 07:04:21','2021-11-20 07:04:21'),(70,'Transactions import','web','2021-11-20 07:04:21','2021-1 1-20 07:04:21'),(71,'Bookings add','web','2021-11-20 07:04:21','2021-11-20 07:04:21'),(72,'Bookings edit','web','2021-11-20 07:04:21','2021-11-20 07:04:21'),(73,'Bookings delete',' web','2021-11-20 07:04:21','2021-11-20 07:04:21'),(74,'Bookings list','web','2021-11-20 07:04:21','2021-11-20 07:04:21'),(75,'Bookings import','web','2021-11-20 07:04:21','2021-11-20 07:04:21'),(76,'Reports add','web','2021-11-20 07:04:21','2021-11-20 07:04:21'),(77,'Reports edit','web','2021-11-20 07:04:22','2021-11-20 07:04:22'),(78,'Reports delete','web', '2021-11-20 07:04:22','2021-11-20 07:04:22'),(79,'Reports list','web','2021-11-20 07:04:22','2021-11-20 07:04:22'),(80,'Reports import','web','2021-11-20 07:04:22','2021-11-20 07:0 4:22'),(81,'Fuel add','web','2021-11-20 07:04:22','2021-11-20 07:04:22'),(82,'Fuel edit','web','2021-11-20 07:04:22','2021-11-20 07:04:22'),(83,'Fuel delete','web','2021-11-20 07:0 4:22','2021-11-20 07:04:22'),(84,'Fuel list','web','2021-11-20 07:04:22','2021-11-20 07:04:22'),(85,'Fuel import','web','2021-11-20 07:04:22','2021-11-20 07:04:22'),(86,'Vendors ad d','web','2021-11-20 07:04:22','2021-11-20 07:04:22'),(87,'Vendors edit','web','2021-11-20 07:04:23','2021-11-20 07:04:23'),(88,'Vendors delete','web','2021-11-20 07:04:23','2021-1 1-20 07:04:23'),(89,'Vendors list','web','2021-11-20 07:04:23','2021-11-20 07:04:23'),(90,'Vendors import','web','2021-11-20 07:04:23','2021-11-20 07:04:23'),(91,'Parts add','web', '2021-11-20 07:04:23','2021-11-20 07:04:23'),(92,'Parts edit','web','2021-11-20 07:04:23','2021-11-20 07:04:23'),(93,'Parts delete','web','2021-11-20 07:04:23','2021-11-20 07:04:23 '),(94,'Parts list','web','2021-11-20 07:04:23','2021-11-20 07:04:23'),(95,'Parts import','web','2021-11-20 07:04:23','2021-11-20 07:04:23'),(96,'WorkOrders add','web','2021-11-20  07:04:23','2021-11-20 07:04:23'),(97,'WorkOrders edit','web','2021-11-20 07:04:23','2021-11-20 07:04:23'),(98,'WorkOrders delete','web','2021-11-20 07:04:24','2021-11-20 07:04:24') ,(99,'WorkOrders list','web','2021-11-20 07:04:24','2021-11-20 07:04:24'),(100,'WorkOrders import','web','2021-11-20 07:04:24','2021-11-20 07:04:24'),(101,'Notes add','web','2021-1 1-20 07:04:24','2021-11-20 07:04:24'),(102,'Notes edit','web','2021-11-20 07:04:24','2021-11-20 07:04:24'),(103,'Notes delete','web','2021-11-20 07:04:24','2021-11-20 07:04:24'),(1 04,'Notes list','web','2021-11-20 07:04:25','2021-11-20 07:04:25'),(105,'Notes import','web','2021-11-20 07:04:25','2021-11-20 07:04:25'),(106,'ServiceReminders add','web','2021-11 -20 07:04:25','2021-11-20 07:04:25'),(107,'ServiceReminders edit','web','2021-11-20 07:04:25','2021-11-20 07:04:25'),(108,'ServiceReminders delete','web','2021-11-20 07:04:25','202 1-11-20 07:04:25'),(109,'ServiceReminders list','web','2021-11-20 07:04:25','2021-11-20 07:04:25'),(110,'ServiceReminders import','web','2021-11-20 07:04:25','2021-11-20 07:04:25') ,(111,'ServiceItems add','web','2021-11-20 07:04:25','2021-11-20 07:04:25'),(112,'ServiceItems edit','web','2021-11-20 07:04:25','2021-11-20 07:04:25'),(113,'ServiceItems delete',' web','2021-11-20 07:04:25','2021-11-20 07:04:25'),(114,'ServiceItems list','web','2021-11-20 07:04:25','2021-11-20 07:04:25'),(115,'ServiceItems import','web','2021-11-20 07:04:25' ,'2021-11-20 07:04:25'),(116,'Testimonials add','web','2021-11-20 07:04:26','2021-11-20 07:04:26'),(117,'Testimonials edit','web','2021-11-20 07:04:26','2021-11-20 07:04:26'),(118, 'Testimonials delete','web','2021-11-20 07:04:26','2021-11-20 07:04:26'),(119,'Testimonials list','web','2021-11-20 07:04:26','2021-11-20 07:04:26'),(120,'Testimonials import','web ','2021-11-20 07:04:26','2021-11-20 07:04:26'),(121,'Team add','web','2021-11-20 07:04:26','2021-11-20 07:04:26'),(122,'Team edit','web','2021-11-20 07:04:26','2021-11-20 07:04:26' ),(123,'Team delete','web','2021-11-20 07:04:27','2021-11-20 07:04:27'),(124,'Team list','web','2021-11-20 07:04:27','2021-11-20 07:04:27'),(125,'Team import','web','2021-11-20 07: 04:27','2021-11-20 07:04:27'),(126,'Settings add','web','2021-11-20 07:04:27','2021-11-20 07:04:27'),(127,'Settings edit','web','2021-11-20 07:04:27','2021-11-20 07:04:27'),(128,'S ettings delete','web','2021-11-20 07:04:27','2021-11-20 07:04:27'),(129,'Settings list','web','2021-11-20 07:04:27','2021-11-20 07:04:27'),(130,'Settings import','web','2021-11-20  07:04:27','2021-11-20 07:04:27'),(131,'Inquiries add','web','2021-11-20 07:04:27','2021-11-20 07:04:27'),(132,'Inquiries edit','web','2021-11-20 07:04:27','2021-11-20 07:04:27'),(1 33,'Inquiries delete','web','2021-11-20 07:04:27','2021-11-20 07:04:27'),(134,'Inquiries list','web','2021-11-20 07:04:27','2021-11-20 07:04:27'),(135,'Inquiries import','web','202 1-11-20 07:04:28','2021-11-20 07:04:28'),(136,'Inquiries import','web','2021-11-20 07:04:28','2021-11-20 07:04:28'),(137,'VehicleBreakdown add','web','2021-11-20 07:04:28','2021-11 -20 07:04:28'),(138,'VehicleBreakdown edit','web','2021-11-20 07:04:28','2021-11-20 07:04:28'),(139,'VehicleBreakdown delete','web','2021-11-20 07:04:28','2021-11-20 07:04:28'),(14 0,'VehicleBreakdown list','web','2021-11-20 07:04:28','2021-11-20 07:04:28'),
(141,'DriverAlert add','web','2021-11-20 07:04:28','2021-11-20 07:04:28'),(142,'DriverAlert edit','web ','2021-11-20 07:04:28','2021-11-20 07:04:28'),(143,'DriverAlert delete','web','2021-11-20 07:04:28','2021-11-20 07:04:28'),(144,'DriverAlert list','web','2021-11-20 07:04:28','202 1-11-20 07:04:28');

-- Table structure for table push_notification
CREATE TABLE push_notification (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 user_type VARCHAR(255) DEFAULT NULL,
 authtoken VARCHAR(255) DEFAULT NULL,
 contentencoding VARCHAR(255) DEFAULT NULL,
 endpoint VARCHAR(255) DEFAULT NULL,
 publickey VARCHAR(255) DEFAULT NULL
);
CREATE INDEX push_notification_user_id_index ON push_notification (user_id);
CREATE INDEX push_notification_user_type_index ON push_notification (user_type);

-- Table structure for table push_subscriptions
CREATE TABLE push_subscriptions (
 id BIGSERIAL PRIMARY KEY,
 subscribable_type VARCHAR(255) NOT NULL,
 subscribable_id BIGINT NOT NULL,
 endpoint VARCHAR(255) NOT NULL,
 public_key VARCHAR(255) DEFAULT NULL,
 auth_token VARCHAR(255) DEFAULT NULL,
 content_encoding VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 UNIQUE (endpoint)
);
CREATE INDEX push_subscriptions_subscribable_type_subscribable_id_index ON push_subscriptions (subscribable_type, subscribable_id);

-- Table structure for table reasons
CREATE TABLE reasons (
 id SERIAL PRIMARY KEY,
 reason TEXT DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table reasons
INSERT INTO reasons VALUES (1,'No fuel',NULL,'2021-11-20 07:03:46','2021-11-20 07:03:46'),(2,'Tire punctured',NULL,'2021-11-20 07:03:46','2021-11-20 07:03:46');

-- Table structure for table reviews
CREATE TABLE reviews (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 booking_id INTEGER DEFAULT NULL,
 driver_id INTEGER DEFAULT NULL,
 ratings NUMERIC(8,2) DEFAULT NULL,
 review_text TEXT DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX reviews_user_id_booking_id_driver_id_index ON reviews (user_id, booking_id, driver_id);

-- Table structure for table role_has_permissions
CREATE TABLE role_has_permissions (
 permission_id BIGINT NOT NULL,
 role_id BIGINT NOT NULL,
 PRIMARY KEY (permission_id, role_id),
 CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE,
 CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
);

-- Dumping data for table role_has_permissions
INSERT INTO role_has_permissions VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(6,2),(7,1),(7,2),(8,1),(8,2),(9,1),(9,2),(10,1),(11,1),(11,2),(12,1),(12,2),(13,1),(13,2),(14,1),(14,2),(15,1),(16,1),(17,1),(18,1),(19 ,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),( 45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1) ,(71,1),(71,2),(72,1),(72,2),(73,1),(73,2),(74,1),(74,2),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92, 1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(112,1),(113,1),(114,1),(115,1), (116,1),(117,1),(118,1),(119,1),(120,1),(121,1),(122,1),(123,1),(124,1),(125,1),(126,1),(127,1),(128,1),(129,1),(130,1),(131,1),(132,1),(133,1),(134,1),(135,1),(136,1),(137,1),(138 ,1),(139,1),(140,1),(141,1),(142,1),(143,1),(144,1);

-- Table structure for table roles
CREATE TABLE roles (
 id BIGSERIAL PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 guard_name VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table roles
INSERT INTO roles VALUES (1,'Super Admin','web','2021-11-20 07:04:28','2021-11-20 07:04:28'),(2,'Admin','web','2021-11-20 07:04:36','2021-11-20 07:04:36');

-- Table structure for table service_items
CREATE TABLE service_items (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 description TEXT DEFAULT NULL,
 time_interval VARCHAR(255) DEFAULT 'off',
 overdue_time VARCHAR(255) DEFAULT NULL,
 overdue_unit VARCHAR(255) DEFAULT NULL,
 meter_interval VARCHAR(255) DEFAULT 'off',
 overdue_meter VARCHAR(255) DEFAULT NULL,
 show_time VARCHAR(255) DEFAULT 'off',
 duesoon_time VARCHAR(255) DEFAULT NULL,
 duesoon_unit VARCHAR(255) DEFAULT NULL,
 show_meter VARCHAR(255) DEFAULT 'off',
 duesoon_meter VARCHAR(255) DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table service_items
INSERT INTO service_items VALUES (1,1,'Change oil','on','60','day(s)','off',NULL,'on','2','day(s)','off',NULL,NULL,'2021-11-20 07:03:53','2021-11-20 07:03:53');

-- Table structure for table service_reminder
CREATE TABLE service_reminder (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 vehicle_id INTEGER DEFAULT NULL,
 service_id INTEGER DEFAULT NULL,
 last_date VARCHAR(255) DEFAULT NULL,
 last_meter INTEGER DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX service_reminder_vehicle_id_service_id_index ON service_reminder (vehicle_id, service_id);

-- Table structure for table settings
CREATE TABLE settings (
 id SERIAL PRIMARY KEY,
 label VARCHAR(255) NOT NULL,
 name VARCHAR(255) NOT NULL,
 value TEXT NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 UNIQUE (name)
);

-- Dumping data for table settings
INSERT INTO settings VALUES (1,'Website Name','app_name','Fleet Manager','2021-11-20 07:03:56','2021-11-22 23:03:02',NULL),(2,'Business Address 1','badd1','Company Address 1','2021-11-20 07:03:56','20 21-11-22 23:03:03',NULL),(3,'Business Address 2','badd2','Company Address 2','2021-11-20 07:03:56','2021-11-22 23:03:03',NULL),(4,'Email Address','email','master@admin.com','2021-1 1-20 07:03:56','2021-11-22 23:03:03',NULL),(5,'City','city','Bhavnagar','2021-11-20 07:03:56','2021-11-22 23:03:03',NULL),(6,'State','state','Gujarat','2021-11-20 07:03:56','2021-1 1-22 23:03:03',NULL),(7,'Country','country','India','2021-11-20 07:03:56','2021-11-22 23:03:03',NULL),(8,'Distence Format','dis_format','km','2021-11-20 07:03:56','2021-11-22 23:03 :03',NULL),(9,'Language','language','English-en','2021-11-20 07:03:57','2021-11-22 23:03:04',NULL),(10,'Currency','currency','₹','2021-11-20 07:03:57','2021-11-22 23:03:04',NULL),( 11,'Tax No','tax_no','ABCD8735XXX','2021-11-20 07:03:57','2021-11-22 23:03:04',NULL),(12,'Invoice Text','invoice_text','Etsy doostang zoodles disqus groupon greplin oooj voxy zoodl es, weebly ning heekya handango imeem plugg dopplr jibjab, movity jajah plickers sifteo edmodo ifttt zimbra.','2021-11-20 07:03:57','2021-11-22 23:03:04',NULL),(13,'Small Logo','ic on_img','logo-40.png','2021-11-20 07:03:57','2021-11-20 07:03:57',NULL),(14,'Main Logo','logo_img','logo.png','2021-11-20 07:03:57','2021-11-20 07:03:57',NULL),(15,'Time Interval', 'time_interval','30','2021-11-20 07:03:57','2021-11-22 23:03:04',NULL),(16,'Tax Charge','tax_charge','null','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL),(17,'Fuel Unit','fuel_ unit','gallon','2021-11-20 07:03:57','2021-11-22 23:03:04',NULL),(18,'Date Format','date_format','d-m-Y','2021-11-20 07:03:57','2021-11-22 23:03:04',NULL),(19,'Website Footer','web _footer',' <p > <span style= "font-size: 16px; " >© Hyvikk Solutions 2025. All Rights Reserved.  <span class= "vertical-spacer d-none d-lg-inline " >| </span > Powered By  </span > <a href= "https://hyvikk.com/ " target= "_blank " class= "link " > <span style= "font-size: 16px; " >Hyvikk </span > </a > </p >','2021-11-20 07:03:57','2021-11-22 23:03:04',NULL),(20,'Fuel enable for Driver','fuel_enable_driver','1','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL),(21,'Income enable for  Driver','income_enable_driver','1','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL),(22,'Expense enable for Driver','expense_enable_driver','1','2021-11-20 07:03:57','2021-11-22 2 3:03:05',NULL),(23,'traccar_enable','traccar_enable','1','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL),(24,'traccar_username','traccar_username','','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL),(25,'traccar_password','traccar_password','','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL),(26,'traccar_server_link','traccar_server_link','','2021-11-20  07:03:57','2021-11-22 23:03:05',NULL),(27,'traccar_map_key','traccar_map_key','','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL),(28,'vehicle_interval','vehicle_interval','45','2 021-11-20 07:03:57','2021-11-22 23:03:05',NULL),(29,'driver_interval','driver_interval','60','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL),(30,'driver_doc_verification','driver _doc_verification','0','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL),(31,'fotter logo img','fotter_logo_img','footer_logo.png','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL) ,(32,'fare mode','fare_mode','type_wise','2021-11-20 07:03:57','2021-11-22 23:03:05',NULL),
(33,'return booking','return_booking',1,'2021-11-20 07:03:57','2021-11-22 23:03:05',NULL );

-- Table structure for table team
CREATE TABLE team (
 id SERIAL PRIMARY KEY,
 name VARCHAR(255) DEFAULT NULL,
 details TEXT DEFAULT NULL,
 designation VARCHAR(255) DEFAULT NULL,
 image VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table team
INSERT INTO team VALUES (1,'Elliot Hirthe','Lorem ipsum dolor, sit amet consectetur adipisicing elit. Temporibus neque est nemo et ipsum fugiat, ab facere adipisci. Aliquam quibusdam molestias qui squam distinctio? Culpa, voluptatem voluptates exercitationem sequi velit quaerat.','Owner',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45',NULL),(2,'Kathlyn Wisoky IV','Lorem ips um dolor, sit amet consectetur adipisicing elit. Temporibus neque est nemo et ipsum fugiat, ab facere adipisci. Aliquam quibusdam molestias quisquam distinctio? Culpa, voluptatem v oluptates exercitationem sequi velit quaerat.','Owner',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45',NULL),(3,'Prof. Juliana Mante','Lorem ipsum dolor, sit amet consectetur adip isicing elit. Temporibus neque est nemo et ipsum fugiat, ab facere adipisci. Aliquam quibusdam molestias quisquam distinctio? Culpa, voluptatem voluptates exercitationem sequi veli t quaerat.','Owner',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45',NULL),(4,'Deron Ortiz','Lorem ipsum dolor, sit amet consectetur adipisicing elit. Temporibus neque est nemo et  ipsum fugiat, ab facere adipisci. Aliquam quibusdam molestias quisquam distinctio? Culpa, voluptatem voluptates exercitationem sequi velit quaerat.','Owner',NULL,'2021-11-20 07:03: 45','2021-11-20 07:03:45',NULL),(5,'Dr. Jailyn Feil','Lorem ipsum dolor, sit amet consectetur adipisicing elit. Temporibus neque est nemo et ipsum fugiat, ab facere adipisci. Aliqu am quibusdam molestias quisquam distinctio? Culpa, voluptatem voluptates exercitationem sequi velit quaerat.','Owner',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45',NULL);

-- Table structure for table testimonials
CREATE TABLE testimonials (
 id SERIAL PRIMARY KEY,
 name VARCHAR(255) DEFAULT NULL,
 details TEXT DEFAULT NULL,
 image VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table testimonials
INSERT INTO testimonials VALUES (1,'Dahlia Goldner','Lorem ipsum dolor sit amet, consectetur adipisicing elit. Amet animi doloribus, repudiandae iusto magnam soluta voluptates, expedita aspernatur consect etur! Ex fugit ducimus itaque, quibusdam nemo in animi quae libero repellendus!',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45',NULL),(2,'Franz Stokes','Lorem ipsum dolor sit ame t, consectetur adipisicing elit. Amet animi doloribus, repudiandae iusto magnam soluta voluptates, expedita aspernatur consectetur! Ex fugit ducimus itaque, quibusdam nemo in animi  quae libero repellendus!',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45',NULL),(3,'Albert Gleason','Lorem ipsum dolor sit amet, consectetur adipisicing elit. Amet animi doloribu s, repudiandae iusto magnam soluta voluptates, expedita aspernatur consectetur! Ex fugit ducimus itaque, quibusdam nemo in animi quae libero repellendus!',NULL,'2021-11-20 07:03:45 ','2021-11-20 07:03:45',NULL),(4,'Vanessa Bechtelar PhD','Lorem ipsum dolor sit amet, consectetur adipisicing elit. Amet animi doloribus, repudiandae iusto magnam soluta voluptates , expedita aspernatur consectetur! Ex fugit ducimus itaque, quibusdam nemo in animi quae libero repellendus!',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45',NULL),(5,'Adah Rau',' Lorem ipsum dolor sit amet, consectetur adipisicing elit. Amet animi doloribus, repudiandae iusto magnam soluta voluptates, expedita aspernatur consectetur! Ex fugit ducimus itaque , quibusdam nemo in animi quae libero repellendus!',NULL,'2021-11-20 07:03:45','2021-11-20 07:03:45',NULL);

-- Table structure for table twilio_settings
CREATE TABLE twilio_settings (
 id BIGSERIAL PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 value TEXT NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 UNIQUE (name)
);

-- Dumping data for table twilio_settings
INSERT INTO twilio_settings VALUES (1,'sid','','2021-11-20 07:04:11','2021-11-20 07:04:11',NULL),(2,'token','','2021-11-20 07:04:11','2021-11-20 07:04:11',NULL),(3,'from','','2021-11-20 07:04:11','2021-11-20 07:04:11',NULL),(4,'customer_message','','2021-11-20 07:04:11','2021-11-20 07:04:11',NULL),(5,'driver_message','','2021-11-20 07:04:11','2021-11-20 07:04:11',NULL);

-- Table structure for table users
CREATE TABLE users (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 name VARCHAR(255) DEFAULT NULL,
 email VARCHAR(95) DEFAULT NULL,
 password VARCHAR(255) DEFAULT NULL,
 user_type VARCHAR(255) DEFAULT NULL,
 group_id INTEGER DEFAULT NULL,
 api_token VARCHAR(60) NOT NULL,
 remember_token VARCHAR(100) DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 UNIQUE (api_token),
 UNIQUE (email)
);
CREATE INDEX users_user_type_index ON users (user_type);

-- Dumping data for table users
INSERT INTO users VALUES (1,1,'Super Administrator','master@admin.com','$2y$10$oRVwGqjS7RT.ae9rLPlbwevOJz88d7mUuDE1vPtWEsHBevanPCq6q','S',NULL,'vNjY40dy2vWTYJqPfsOGRW331lIU8OY2qfUrqL5Oo4RTxnIvsxT9Z VIHlXFv',NULL,'2021-11-20 07:03:48','2021-11-20 07:03:48',NULL),(2,1,'User One','user1@admin.com','$2y$10$0yL5QM7IVdb3B6FUi3m2HugbnC5VK2HncZR0VGr1cvsSEV/Nc/pc.','O',1,'1TxP6fg9WPYm Pse2PaRggJUAyt0De9xOYUivQeiSC0N92GYEFVOviNfQq6Qk',NULL,'2021-11-20 07:03:48','2021-11-20 07:03:48',NULL),(3,1,'User Two','user2@admin.com','$2y$10$JPAnaeoH1aw5NIoomGPHyOi03VVOl0y6/ iU4Po0Q/d8HaKsOpoPK.','O',1,'dLlOOjzxTrYzA2N9IEJeduRXnpLwrARmnaXvwbtLtPCFgpcZgeYIfErCQ6ja',NULL,'2021-11-20 07:03:48','2021-11-20 07:03:48',NULL),(4,1,'Customer One','customer1@gma il.com','$2y$10$bt3dPDa3tHjUkB.IDINUM.1lqfLy.3M.TTd2qVWDqF5P3wCrVlpLq','C',NULL,'TuaPjW443femKIauadpE0VskcpvSwBke0dsS39YeOaiAAkS8rsek1vuXx9F3',NULL,'2021-11-20 07:03:49','2021-11-2 0 07:03:49',NULL),(5,1,'Customer Two','customer2@gmail.com','$2y$10$tMH0pfSWraNZLp1.nGhhDOMPhyxjC.tNykK6eXxg88CEZF0Zm.mdW','C',NULL,'0G1fjlmammOVOA7hxpsXAtw0Wp1oWLPC2xCxrCQoqS14m0U 2d26sGHw15LuX',NULL,'2021-11-20 07:03:49','2021-11-20 07:03:49',NULL),(6,1,'Mariah Bahringer','nbode@example.net','$2y$10$mRsCYSZSMw0lAle/kxMjGODZ6nt/G3FzB75AUWsTKb7jdq9KXL9ny','D' ,NULL,'4vyb77kPNaiMyuPG63WUFctB2G3NPjPx1kgafzjBOWWnhEsVS8rScIg7s98O','5aN4c0pRUd','2021-11-20 07:04:12','2021-11-20 07:04:12',NULL),(7,1,'Leland Schuppe','oabshire@example.org','$2 y$10$8xlqNIYjbsuuTrMho/4AieRd4AO8XFKL0UpO9L1c/4REs40OlSCXS','D',NULL,'rDQOs9u7J4HX9gRG9ba6SHpDfpcpNqxmKVuZmhgGAc9EK1Zbfs60cBepetsr','yX9YRQfvBJ','2021-11-20 07:04:13','2021-11-20 0 7:05:06',NULL),(8,1,'Noelle Stafford','kedim@mailinator.com','$2y$10$3x2u23rUc0eqJNqqPO7yNutR/wUZb9CAk97oI2OWVTrlDWexPfyfm','D',NULL,'pN1iP2z5R3KnjTtk2QiJHES7saG5MvxswgHjCaCu9Ob2CR 32is6dD98c0txL',NULL,'2021-11-22 23:01:58','2021-11-22 23:01:58',NULL);

-- Table structure for table users_meta
CREATE TABLE users_meta (
 id SERIAL PRIMARY KEY,
 user_id INTEGER NOT NULL,
 type VARCHAR(255) NOT NULL DEFAULT 'null',
 "key" VARCHAR(255) NOT NULL,
 value TEXT DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX users_meta_user_id_index ON users_meta (user_id);
CREATE INDEX users_meta_key_index ON users_meta ("key");

-- Dumping data for table users_meta
INSERT INTO users_meta VALUES (1,1,'string','profile_image','no-user.jpg',NULL,'2021-11-20 07:03:48','2021-11-20 07:03:48'),(2,1,'string','module','a:15:{i:0;i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5; i:6;i:6;i:7;i:7;i:8;i:8;i:9;i:9;i:10;i:10;i:11;i:12;i:12;i:13;i:13;i:14;i:14;i:15;}',NULL,'2021-11-20 07:03:48','2021-11-20 07:03:48'),(3,2,'string','module','a:15:{i:0;i:0;i:1;i:1 ;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;i:7;i:8;i:8;i:9;i:9;i:10;i:10;i:11;i:12;i:12;i:13;i:13;i:14;i:14;i:15;}',NULL,'2021-11-20 07:03:48','2021-11-20 07:03:48'),(4,3,'string ','module','a:15:{i:0;i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;i:7;i:8;i:8;i:9;i:9;i:10;i:10;i:11;i:12;i:12;i:13;i:13;i:14;i:14;i:15;}',NULL,'2021-11-20 07:03:48','2 021-11-20 07:03:48'),(5,4,'string','first_name','Customer',NULL,'2021-11-20 07:03:49','2021-11-20 07:03:49'),(6,4,'string','last_name','One',NULL,'2021-11-20 07:03:49','2021-11-20  07:03:49'),(7,4,'string','address','728 Evalyn Knolls Apt. 119 Lake Jaydenville, MD 74979-3406',NULL,'2021-11-20 07:03:49','2021-11-20 07:03:49'),(8,4,'string','mobno','86393799156 69',NULL,'2021-11-20 07:03:49','2021-11-20 07:03:49'),(9,4,'integer','gender','0',NULL,'2021-11-20 07:03:49','2021-11-20 07:03:49'),(10,5,'string','first_name','Customer',NULL,'202 1-11-20 07:03:49','2021-11-20 07:03:49'),(11,5,'string','last_name','Two',NULL,'2021-11-20 07:03:49','2021-11-20 07:03:49'),(12,5,'string','address','91158 Luigi Cliffs Lake Darby,  MA 39627-1727',NULL,'2021-11-20 07:03:49','2021-11-20 07:03:49'),(13,5,'string','mobno','9773607007903',NULL,'2021-11-20 07:03:49','2021-11-20 07:03:49'),(14,5,'integer','gender', '1',NULL,'2021-11-20 07:03:49','2021-11-20 07:03:49'),(15,6,'string','first_name','Mariah',NULL,'2021-11-20 07:04:13','2021-11-20 07:04:13'),(16,6,'string','last_name','Bahringer', NULL,'2021-11-20 07:04:13','2021-11-20 07:04:13'),(17,6,'string','address','842 Dicki Run
Port Lewis, DC 61595-0469',NULL,'2021-11-20 07:04:13','2021-11-20 07:04:13'),(18,6,'strin g','phone','03057119344690',NULL,'2021-11-20 07:04:13','2021-11-20 07:04:13'),(19,6,'string','issue_date','2021-11-20',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(20,6,'stri ng','exp_date','2022-01-20',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(21,6,'string','start_date','2021-11-20',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(22,6,'stri ng','end_date','2021-12-20',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(23,6,'integer','license_number','256612',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(24,6,'int eger','contract_number','5296',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(25,6,'integer','emp_id','5732787',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(26,7,'string' ,'first_name','Leland',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(27,7,'string','last_name','Schuppe',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(28,7,'string','addr ess','798 Ariel Causeway
South Amirbury, GA 81164',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(29,7,'string','phone','04450210557668',NULL,'2021-11-20 07:04:14','2021-11-20  07:04:14'),(30,7,'string','issue_date','2021-11-20',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(31,7,'string','exp_date','2022-01-20',NULL,'2021-11-20 07:04:14','2021-11-20  07:04:14'),(32,7,'string','start_date','2021-11-20',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(33,7,'string','end_date','2021-12-20',NULL,'2021-11-20 07:04:14','2021-11-20  07:04:14'),(34,7,'integer','license_number','812157',NULL,'2021-11-20 07:04:14','2021-11-20 07:04:14'),(35,7,'integer','contract_number','7824',NULL,'2021-11-20 07:04:14','2021-11 -20 07:04:14'),(36,7,'integer','emp_id','4157602',NULL,'2021-11-20 07:04:15','2021-11-20 07:04:15'),(37,6,'integer','vehicle_id','1',NULL,'2021-11-20 07:04:15','2021-11-20 07:04:15 '),(38,8,'string','_token','WmxrHtHiGbjZKGrmU10ipMeLfMppXKbHXTPrJMJl',NULL,'2021-11-22 23:01:58','2021-11-22 23:01:58'),(39,8,'string','is_active','1',NULL,'2021-11-22 23:01:58','2 021-11-22 23:01:58'),(40,8,'string','is_available','0',NULL,'2021-11-22 23:01:58','2021-11-22 23:01:58'),(41,8,'string','first_name','Noelle',NULL,'2021-11-22 23:01:58','2021-11-22  23:01:58'),(42,8,'string','middle_name','Mona Rutledge',NULL,'2021-11-22 23:01:59','2021-11-22 23:01:59'),(43,8,'string','last_name','Stafford',NULL,'2021-11-22 23:01:59','2021-11 -22 23:01:59'),(44,8,'string','vehicle_id','2',NULL,'2021-11-22 23:01:59','2021-11-22 23:01:59'),(45,8,'string','address','Esse neque quos qui',NULL,'2021-11-22 23:01:59','2021-11-22 23:01:59'),(46,8,'string','email','kedim@mailinator.com',NULL,'2021-11-22 23:01:59','2021-11-22 23:01:59'),(47,8,'string','phone_code','+49',NULL,'2021-11-22 23:01:59','2021-11-22 23:01:59'),(48,8,'string','phone','869',NULL,'2021-11-22 23:01:59','2021-11-22 23:01:59'),(49,8,'string','emp_id','Nisi sit ab ea plac',NULL,'2021-11-22 23:01:59','2021-11-22 23 :01:59'),(50,8,'string','contract_number','533',NULL,'2021-11-22 23:02:00','2021-11-22 23:02:00'),(51,8,'string','license_number','417',NULL,'2021-11-22 23:02:00','2021-11-22 23:02 :00'),(52,8,'string','issue_date','2020-02-19',NULL,'2021-11-22 23:02:00','2021-11-22 23:02:00'),(53,8,'string','exp_date','2022-05-15',NULL,'2021-11-22 23:02:00','2021-11-22 23:02 :00'),(54,8,'string','start_date','2006-07-24',NULL,'2021-11-22 23:02:00','2021-11-22 23:02:00'),(55,8,'string','end_date','2022-02-11',NULL,'2021-11-22 23:02:00','2021-11-22 23:02 :00'),(56,8,'string','password','password',NULL,'2021-11-22 23:02:00','2021-11-22 23:02:00'),(57,8,'string','gender','1',NULL,'2021-11-22 23:02:00','2021-11-22 23:02:00'),(58,8,'st ring','econtact','Et placeat excepteu',NULL,'2021-11-22 23:02:00','2021-11-22 23:02:00'),(59,1,'string','language','English-en',NULL,'2021-11-22 23:03:04','2021-11-22 23:03:04');

-- Table structure for table vehicle_group
CREATE TABLE vehicle_group (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 name VARCHAR(50) DEFAULT NULL,
 description TEXT DEFAULT NULL,
 note TEXT DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table vehicle_group
INSERT INTO vehicle_group VALUES (1,1,'Default','Default vehicle group','Default vehicle group',NULL,'2021-11-20 07:03:49','2021-11-20 07:03:49');

-- Table structure for table vehicle_review
CREATE TABLE vehicle_review (
 id SERIAL PRIMARY KEY,
 vehicle_id INTEGER NOT NULL,
 user_id INTEGER DEFAULT NULL,
 reg_no VARCHAR(255) DEFAULT NULL,
 kms_outgoing INTEGER DEFAULT NULL,
 kms_incoming INTEGER DEFAULT NULL,
 fuel_level_out INTEGER DEFAULT NULL,
 fuel_level_in INTEGER DEFAULT NULL,
 datetime_outgoing TIMESTAMP DEFAULT NULL,
 datetime_incoming TIMESTAMP DEFAULT NULL,
 petrol_card TEXT DEFAULT NULL,
 lights TEXT DEFAULT NULL,
 invertor TEXT DEFAULT NULL,
 car_mats TEXT DEFAULT NULL,
 int_damage TEXT DEFAULT NULL,
 int_lights TEXT DEFAULT NULL,
 ext_car TEXT DEFAULT NULL,
 tyre TEXT DEFAULT NULL,
 ladder TEXT DEFAULT NULL,
 leed TEXT DEFAULT NULL,
 power_tool TEXT DEFAULT NULL,
 ac TEXT DEFAULT NULL,
 head_light TEXT DEFAULT NULL,
 lock TEXT DEFAULT NULL,
 windows TEXT DEFAULT NULL,
 condition TEXT DEFAULT NULL,
 oil_chk TEXT DEFAULT NULL,
 suspension TEXT DEFAULT NULL,
 tool_box TEXT DEFAULT NULL,
 image VARCHAR(255) DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 udf VARCHAR(255) DEFAULT NULL
);
CREATE INDEX vehicle_review_vehicle_id_user_id_index ON vehicle_review (vehicle_id, user_id);

-- Dumping data for table vehicle_review
INSERT INTO vehicle_review VALUES (1,2,8,'875',15,65,3,1,'2019-12-09 00:00:00','1977-04-28 00:00:00','a:2:{s:4: "flag ";s:1: "1 ";s:4: "text ";s:20: "Omnis facere aut cil ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:19: "Obcaecati obcaecati ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:20: "Quis doloremque repe ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:19: "Fugit commodi quide ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:20: "Numquam deserunt qui ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:20: "Cupiditate dignissim ";}','a:2:{s:4: "flag ";s:1: "1 ";s:4: "text ";s:19: "Quibusdam dicta qui ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:19: "Necessitatibus fuga ";}','a:2:{s:4: "flag ";s:1: "1 ";s:4: "text ";s:20: "Ut ut ullam omnis ad ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:20: "Adipisci irure offic ";}','a:2:{s:4: "flag ";s:1: "1 ";s:4: "text ";s:19: "Fuga Aperiam quaera ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:20: "Ea ea modi earum ali ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:20: "Nisi dolor officia e ";}','a:2:{s:4: "flag ";s:1: "1 ";s:4: "text ";s:19: "Unde nisi culpa con ";}','a:2:{s:4: "flag ";s:1: "1 ";s:4: "text ";s:20: "Quisquam eiusmod deb ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:20: "Ex in beatae consequ ";}','a:2:{s:4: "flag ";s:1: "1 ";s:4: "text ";s:20: "Do est ad et aliquid ";}','a:2:{s:4: "flag ";s:1: "0 ";s:4: "text ";s:19: "Dolores ut eligendi ";}','a:2:{s:4: "flag ";s:1: "1 ";s:4: "text ";s:20: "Doloremque dolores e ";}',NULL,NULL,'2021-11-22 23:02:36','2021-11-22 23:02:36','N;');

-- Table structure for table vehicle_types
CREATE TABLE vehicle_types (
 id SERIAL PRIMARY KEY,
 vehicletype VARCHAR(255) DEFAULT NULL,
 displayname VARCHAR(255) DEFAULT NULL,
 icon VARCHAR(255) DEFAULT NULL,
 isenable INTEGER DEFAULT NULL,
 seats INTEGER DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table vehicle_types
INSERT INTO vehicle_types VALUES (1,'Hatchback','Hatchback',NULL,1,4,'2021-11-20 07:03:46','2021-11-20 07:03:46',NULL),(2,'Sedan','Sedan',NULL,1,4,'2021-11-20 07:03:47','2021-11-20 07:03:47',NULL),(3,'Mini van','Mini van',NULL,1,7,'2021-11-20 07:03:47','2021-11-20 07:03:47',NULL),(4,'Saloon','Saloon',NULL,1,4,'2021-11-20 07:03:47','2021-11-20 07:03:47',NULL),(5,'SUV','SUV',NULL,1,4,'2021-11-20 07:03:48','2021-11-20 07:03:48',NULL),(6,'Bus','Bus',NULL,1,40,'2021-11-20 07:03:48','2021-11-20 07:03:48',NULL),(7,'Truck','Truck',NULL,1,3,'2021-11-20 07:03:48','2021-11-20 07:03:48',NULL);

-- Table structure for table vehicles
CREATE TABLE vehicles (
 id SERIAL PRIMARY KEY,
 make_name VARCHAR(100) DEFAULT NULL,
 model_name VARCHAR(100) DEFAULT NULL,
 color_name VARCHAR(100) DEFAULT NULL,
 year VARCHAR(255) DEFAULT NULL,
 group_id INTEGER DEFAULT NULL,
 lic_exp_date DATE DEFAULT NULL,
 reg_exp_date DATE DEFAULT NULL,
 vehicle_image VARCHAR(255) DEFAULT NULL,
 engine_type VARCHAR(255) DEFAULT NULL,
 horse_power VARCHAR(255) DEFAULT NULL,
 vin VARCHAR(255) DEFAULT NULL,
 license_plate VARCHAR(255) NOT NULL,
 mileage INTEGER DEFAULT NULL,
 in_service SMALLINT DEFAULT 0,
 user_id INTEGER DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 int_mileage INTEGER DEFAULT NULL,
 type_id INTEGER DEFAULT NULL
);
CREATE INDEX vehicles_group_id_type_id_user_id_in_service_index ON vehicles (group_id, type_id, user_id, in_service);
CREATE INDEX vehicles_lic_exp_date_reg_exp_date_index ON vehicles (lic_exp_date, reg_exp_date);
CREATE INDEX vehicles_license_plate_index ON vehicles (license_plate);

-- Dumping data for table vehicles
INSERT INTO vehicles VALUES (1,'Tata','Punch','Red','2015',1,'2022-07-28','2022-04-19','car1.png','Petrol','190','2342342','9191bh',45464,1,1,'2021-11-20 07:03:50','2021-11-20 07:03:50',NULL,50,3),(2,'Maruti','Suzuki','Blue','2012',1,'2022-11-20','2022-02-18','car1.png','Petrol','150','124578','1245ab',45464,1,1,'2021-11-20 07:03:50','2021-11-20 07:03:50',NULL,40,3);

-- Table structure for table vehicles_meta
CREATE TABLE vehicles_meta (
 id SERIAL PRIMARY KEY,
 vehicle_id INTEGER NOT NULL,
 type VARCHAR(255) NOT NULL DEFAULT 'null',
 "key" VARCHAR(255) NOT NULL,
 value TEXT DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL
);
CREATE INDEX vehicles_meta_vehicle_id_index ON vehicles_meta (vehicle_id);
CREATE INDEX vehicles_meta_key_index ON vehicles_meta ("key");

-- Dumping data for table vehicles_meta
INSERT INTO vehicles_meta VALUES (1,1,'integer','driver_id','6',NULL,'2021-11-20 07:03:50','2021-11-20 07:03:50'),(2,1,'double','average','35.45',NULL,'2021-11-20 07:03:50','2021-11-20 07:03:50'),(3,1,'string','ins_number','70651',NULL,'2021-11-20 07:03:50','2021-11-20 07:03:50'),(4,1,'string','ins_exp_date','2022-05-29',NULL,'2021-11-20 07:03:50','2021-11-20 07:03:50'),(5,2,'double','average','42.5',NULL,'2021-11-20 07:03:50','2021-11-20 07:03:50'),(6,2,'string','ins_number','36945',NULL,'2021-11-20 07:03:50','2021-11-20 07:03:50'),(7,2,'string','ins_exp_date','2022-05-29',NULL,'2021-11-20 07:03:50','2021-11-20 07:03:50'),(8,2,'integer','driver_id','8',NULL,'2021-11-22 23:02:01','2021-11-22 23:02:01');

-- Table structure for table vendors
CREATE TABLE vendors (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 name VARCHAR(255) DEFAULT NULL,
 photo VARCHAR(255) DEFAULT NULL,
 type VARCHAR(255) DEFAULT NULL,
 website VARCHAR(255) DEFAULT NULL,
 custom_type VARCHAR(255) DEFAULT NULL,
 note TEXT DEFAULT NULL,
 phone VARCHAR(255) DEFAULT NULL,
 address1 VARCHAR(255) DEFAULT NULL,
 address2 VARCHAR(255) DEFAULT NULL,
 city VARCHAR(255) DEFAULT NULL,
 province VARCHAR(255) DEFAULT NULL,
 email VARCHAR(255) DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 udf TEXT DEFAULT NULL,
 country VARCHAR(255) DEFAULT NULL,
 postal_code VARCHAR(255) DEFAULT NULL
);
CREATE INDEX vendors_type_index ON vendors (type);

-- Dumping data for table vendors
INSERT INTO vendors VALUES (1,1,'Prof. Hiram Kovacek',NULL,'Fuel','http://www.example.com',NULL,'default vendor','04401670207973','64150 Bonnie Way
West Nolan, MA 17783-5065',NULL,'Satterfieldland',NULL,'eladio99@example.org',NULL,'2021-11-20 07:04:11','2021-11-20 07:04:11',NULL,NULL,NULL),(2,1,'Melyssa Yost DDS',NULL,'Parts','http://www.example.com',NULL,'default vendor','09923748210738','3016 Newell Manors Suite 126
West Jackelineland, CO 52293-3742',NULL,'West Raoul',NULL,'williamson.melany@example.org',NULL,'2021-11-20 07:04:12','2021-11-20 07:04:12',NULL,NULL,NULL);

-- Table structure for table work_order_logs
CREATE TABLE work_order_logs (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 created_on DATE DEFAULT NULL,
 required_by DATE DEFAULT NULL,
 vehicle_id INTEGER DEFAULT NULL,
 vendor_id INTEGER DEFAULT NULL,
 price NUMERIC(8,2) DEFAULT NULL,
 status VARCHAR(255) DEFAULT NULL,
 type VARCHAR(255) DEFAULT NULL,
 description TEXT DEFAULT NULL,
 meter INTEGER DEFAULT NULL,
 note TEXT DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 parts_price DOUBLE PRECISION DEFAULT 0,
 mechanic_id INTEGER DEFAULT NULL
);
CREATE INDEX work_order_logs_vehicle_id_vendor_id_index ON work_order_logs (vehicle_id, vendor_id);
CREATE INDEX work_order_logs_status_index ON work_order_logs (status);

-- Table structure for table work_orders
CREATE TABLE work_orders (
 id SERIAL PRIMARY KEY,
 user_id INTEGER DEFAULT NULL,
 created_on DATE DEFAULT NULL,
 required_by DATE DEFAULT NULL,
 vehicle_id INTEGER DEFAULT NULL,
 vendor_id INTEGER DEFAULT NULL,
 price NUMERIC(10,2) DEFAULT NULL,
 status VARCHAR(255) DEFAULT NULL,
 description TEXT DEFAULT NULL,
 meter INTEGER DEFAULT NULL,
 note TEXT DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 mechanic_id INTEGER DEFAULT NULL
);
CREATE INDEX work_orders_vehicle_id_vendor_id_index ON work_orders (vehicle_id, vendor_id);
CREATE INDEX work_orders_status_index ON work_orders (status);

-- Dumping data for table work_orders
INSERT INTO work_orders VALUES (1,1,'2021-11-20','2021-11-25',2,1,2000.00,'Pending','Sample work order',1398,'sample work order',NULL,'2021-11-20 07:04:12','2021-11-20 07:04:12',1),(2,1,'2021-11-20','2021-11-25',1,2,3000.00,'Pending','Sample work order',2389,'sample work order',NULL,'2021-11-20 07:04:12','2021-11-20 07:04:12',2);

-- Table structure for table cities
CREATE TABLE cities (
 id INTEGER NOT NULL,
 slug VARCHAR(255) DEFAULT NULL,
 city VARCHAR(255) DEFAULT NULL,
 cost DOUBLE PRECISION DEFAULT NULL,
 image VARCHAR(255) DEFAULT NULL,
 other TEXT DEFAULT NULL,
 order_id INTEGER DEFAULT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Dumping data for table cities
INSERT INTO cities (id, slug, city, cost, image, other, order_id, created_at, updated_at, deleted_at) VALUES
(3, 'bhopal', 'Bhopal', 1000, '6ef10993-7dac-4d13-a73e-a9a213771ceb.png', ' <p style= "text-align: justify; " > <b > <u > <span style= "background-color: rgb(255, 255, 0); " > <span style= "font-size: 24px; " >Bhopal </span > </span > <span style= "font-size: 24px; " >  </span > </u > </b >is a city in the central Indian state of  <u > <b >Madhya Pradesh. </b > </u > It''s one of India’s greenest cities. There are two main lakes, the Upper Lake and the Lower Lake. On the banks of the Upper Lake is Van Vihar National Park, home to tigers, lions  and leopards. The State Museum has fossils, paintings and rare  <span style= "font-family:  " courier= " " new "; "= " " >Jain sculptures </span >. Taj-ul-Masjid is one of Asia’s largest mosques,  <a href= "http://mpcab.in " target= "_blank " >with white domes </a >, minarets and a huge courtyard.
             </p >', 0, '2020-02-04 00:49:38', '2021-07-21 05:43:34', NULL),
(4, 'sagar', 'Sagar', 2000, '851b85ed-ce45-45c5-ad9d-314d490bd53d.jpeg', ' <span style= "color: rgb(51, 51, 51); font-family: Poppins, sans-serif; text-align: justify; " >Sagar is a popular district as well as a renowned city. The administrative system in Sagar is well settled and maintained. Like other famous industrial and tourist special cities,  in Sagar also MP Cabs has started its services. The cab service presents several services like an online taxi or cab service, local and outstation cabs service and for tourists, t hey have also have begun their travel agency. All the sectors are very popular in Sagar. The well-trained team is very much expert in the operational system, tracking records of ri des along with their drivers. Though Sagar is a small city, many people live there. So, cab service is very popular in Sagar. </span >', 3, '2020-02-04 00:54:51', '2021-08-06 10:58:11', NULL),
(5, 'indore', 'Indore', 5000, 'c3ad2b86-75bc-4693-97d3-42b2a3a85167.jpg', 'Indore is a city in west-central India. It’s  known for the 7-story Rajwada Palace and the Lal Baag Palace, which date back to Indore’s 19th-century Holkar dynasty. The Holkar rulers are honored by a cluster of tombs and cenot aphs at Chhatri Baag. The night market Sarafa Bazar sells street food. East is the Indo-Gothic Gandhi Hall and clock tower. The Jain temple Kanch Mandir has a mirrored mosaic inter ior.', 1, '2020-03-17 22:59:31', '2021-07-21 05:43:34', NULL),
(6, 'betul', 'Betul', 600, 'a5e34797-128e-42f8-a5f3-309e775856b1.jpg', 'Betul is a district and municipality in south ern Madhya Pradesh, India. It is the administrative center of its eponymous district and forms the southernmost part of the Bhopal Division in the Betul. Bhimpur village, located 4 5 km west of Betul, is the site of proposed 2800 MW Nuclear Power plant.', 4, '2020-03-17 22:59:44', '2021-07-20 12:20:03', NULL),
(7, 'katni', 'Katni', 700, 'cc7030b9-9ad1-4609-a2 f4-d0faedf86d9b.jpg', 'Katni is a town on the banks of the Katni River in Madhya Pradesh, India. It is the administrative headquarters of Katni District. It is in the Mahakoshal re gion of central India. The city is 90 km from the divisional headquarters of the region, Jabalpur.', 2, '2020-03-17 23:00:37', '2021-07-21 05:43:34', NULL),
(8, 'chhindwara', 'Chhi ndwara', 2000, '370eeb4a-1345-406d-b6f0-6df2c45cc497.jpg', 'Chhindwara is a city in India and a Municipal Corporation in Chhindwara district in the Indian state of Madhya Pradesh.  The city is the administrative headquarters of Chhindwara District. Chhindwara is reachable by rail or road from adjacent cities Nagpur and Jabalpur.', 5, '2020-03-17 23:01:20', '2 021-07-20 09:12:01', NULL),
(9, 'ujjain', 'Ujjain', NULL, '044f0c0a-ae63-46ea-83a7-a741e14689d4.jpg', 'An ancient city situated on the eastern bank of the Kshipra River, Ujjain was  the most prominent city on the Malwa plateau of central India for much of its history. It emerged as the political centre of central India around 600 BCE. It was the capital of th e ancient Avanti kingdom, one of the sixteen mahajanapadas. It remained an important political, commercial and cultural centre of central India until the early 19th century, when t he British administrators decided to develop Indore as an alternative to it. Ujjain continues to be an important place of pilgrimage for Shaivites, Vaishnavites and followers of Sh akta', 6, '2020-04-08 11:32:22', '2021-07-20 09:12:01', NULL),
(10, 'rewa', 'Rewa', NULL, NULL, NULL, 7, '2020-05-23 12:27:38', '2021-07-20 09:12:01', NULL),
(11, 'khajuraho', 'Kha juraho', NULL, 'cc872a77-842a-422e-a05f-e64dc90a87f4.jpg', NULL, 8, '2020-05-23 12:28:00', '2021-07-20 09:12:01', NULL),
(12, 'damoh', 'Damoh', NULL, NULL, NULL, 9, '2020-05-23 12: 28:30', '2021-07-20 09:12:01', NULL),
(13, 'jabalpur', 'Jabalpur', NULL, NULL, NULL, 10, '2020-05-23 12:29:03', '2021-07-20 09:12:01', NULL),
(14, 'narsingpur', 'Narsingpur', NULL,  NULL, NULL, 12, '2020-05-23 12:29:55', '2021-07-21 05:43:47', NULL),
(15, 'bhavnagar', 'Bhavnagar', NULL, '668fbcbb-aa17-4344-8b1c-a451396af7fc.png', NULL, 11, '2020-07-24 10:31:5 2', '2021-07-21 05:43:47', NULL),
(16, 'rajkot', 'Rajkot', NULL, NULL, ' <ul > <li > <strong style= "margin: 0px; padding: 0px; font-family:  " open= " " sans ",= " " arial,= " " sans-serif;= " " font-size:= " " 14px;= " " text-align:= " " justify;= " " background-color:= " " rgb(255,= " " 255,= " " 0); "= " " >Lorem Ipsum </strong > <span style= "font-family:  " open= " " sans ",= " " arial,= " " sans-serif;= " " font-size:= " " 14px;= " " text-align:= " " justify; "= " " > <span style= "background-color: rgb(255, 255, 0); " > is simply dummy text of the printing and typesetting industry </span >. Lorem Ipsum has been the industry''s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has  survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s <span style= "color: rgb(255, 156, 0); " >  </span >with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum. </span > </li > </ul > <h3 > <ul > <li > <span style= "font-family:  " open= " " sans ",= " " arial,= " " sans-serif;= " " font-size:= " " 14px;= " " text-align:= " " justify; "= " " >123323232 </span > </li > </ul > </h3 > <ul > <li > <br > </li > </ul >', 13, '2021-06-14 12:44:20', '2021-07-20 09:12:01', NULL);

-- Table structure for table vehicle_breakdown
CREATE TABLE vehicle_breakdown (
 id SERIAL PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Insert records into the vehicle_breakdown table
INSERT INTO vehicle_breakdown (id, name, created_at, updated_at, deleted_at)
VALUES
(1, 'Mechanical Failure', '2024-08-23 12:28:30', '2024-08-23 09:12:01', NULL),
(2, 'Electrical issues', '2024-08-23 12:28:30', '2024-08-23 09:12:01', NULL),
(3, 'Fuel Shortage', '2024-08-23 12:28:30', '2024-08-23 09:12:01', NULL),
(4, 'Tire puncture or flat tire', '2024-08-23 12:28:30', '2024-08-23 09:12:01', NULL),
(5, 'Overheating', '2024-08-23 12:28:30', '2024-08-23 09:12:01', NULL);

-- Table structure for table driver_alert
CREATE TABLE driver_alert (
 id SERIAL PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Insert records into the driver_alert table
INSERT INTO driver_alert (id, name, created_at, updated_at, deleted_at)
VALUES
(1, 'Aggressive behaviour', '2024-08-23 12:28:30', '2024-08-23 09:12:01', NULL),
(2, 'Violation of customer privacy', '2024-08-23 12:28:30', '2024-08-23 09:12:01', NULL),
(3, 'Rude or unprofessional behaviour', '2024-08-23 12:28:30', '2024-08-23 09:12:01', NULL),
(4, 'Ignoring customer request', '2024-08-23 12:28:30', '2024-08-23 09:12:01', NULL),
(5, 'Unsafe driving practices', '2024-08-23 12:28:30', '2024-08-23 09:12:01', NULL);

-- Table structure for table booking_alerts
CREATE TABLE booking_alerts (
 id SERIAL PRIMARY KEY,
 booking_id VARCHAR(255) NOT NULL,
 message VARCHAR(255) NOT NULL,
 status VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NULL DEFAULT NULL,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 deleted_at TIMESTAMP NULL DEFAULT NULL
);