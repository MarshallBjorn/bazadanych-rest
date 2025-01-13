CREATE TABLE "addresses" (
    "address_id" SERIAL PRIMARY KEY,
    "street" varchar(30) NOT NULL,
    "locality" varchar(30) NOT NULL,
    "post_code" varchar(6) NOT NULL,
    "building_num" varchar(4) NOT NULL
);

CREATE TABLE "providers" (
    "prod_id" SERIAL PRIMARY KEY,
    "prod_name" varchar(20) NOT NULL,
    "contact" varchar(11) UNIQUE NOT NULL,
    "address" int UNIQUE NOT NULL,
    "is_partner" boolean DEFAULT FALSE,
    FOREIGN KEY ("address") REFERENCES "addresses"("address_id") ON DELETE CASCADE
);

CREATE TABLE "staff" (
    "pesel" varchar(11) PRIMARY KEY,
    "firstname" varchar(30) NOT NULL,
    "lastname" varchar(30) NOT NULL,
    "position" varchar(20) NOT NULL,
    "address" int NOT NULL,
    "contact" varchar(11) UNIQUE NOT NULL,
    "gender" boolean NOT NULL,
    "birthday" date NOT NULL,
    "status" text NOT NULL DEFAULT 'hired' CHECK("status" IN ('hired', 'suspended', 'fired')),
    "hire_date" date NOT NULL DEFAULT NOW(),
    FOREIGN KEY ("address") REFERENCES "addresses"("address_id") ON DELETE CASCADE
);

CREATE SCHEMA auth;

CREATE TABLE auth.users (
    "user_id" SERIAL PRIMARY KEY,
    "username" varchar(50) NOT NULL,
    "password_hash" TEXT NOT NULL,
    "created_at" timestamp DEFAULT NOW()
);

CREATE EXTENSION IF NOT EXISTS pgcrypto;

INSERT INTO auth.users(username, password_hash)
VALUES ('admin', crypt('2137', gen_salt('bf')));
INSERT INTO auth.users(username, password_hash)
VALUES ('staff', crypt('uchiha123', gen_salt('bf')));

CREATE TABLE "components" (
    "component_id" SERIAL PRIMARY KEY,
    "component_name" varchar(20) UNIQUE NOT NULL,
    "prod_id" int NOT NULL,
    "price" decimal(6,2) NOT NULL,
    "availability" boolean NOT NULL,
    FOREIGN KEY ("prod_id") REFERENCES "providers"("prod_id") 
);

CREATE TABLE "dishes" (
    "dish_id" SERIAL PRIMARY KEY,
    "dish_name" varchar(20) NOT NULL,
    "dish_type" varchar(20) NOT NULL,
    "price" decimal(6,2) NOT NULL,
    "is_served" boolean NOT NULL DEFAULT true,
    "description" text
);

CREATE TABLE "dishes_components" (
    "dish_id" int,
    "component_id" int,
    "quantity" int NOT NULL,
    CONSTRAINT "nonnegative_componenets" CHECK("quantity" >= 0),
    PRIMARY KEY("dish_id", "component_id"),
    FOREIGN KEY("dish_id") REFERENCES "dishes"("dish_id"),
    FOREIGN KEY("component_id") REFERENCES "components"("component_id")
);

CREATE TABLE "additions" (
    "addition_id" SERIAL PRIMARY KEY,
    "addition_name" varchar(20) NOT NULL,
    "provider" int NOT NULL,
    "price" decimal(6,2) NOT NULL,
    "availability" boolean NOT NULL,
    FOREIGN KEY ("provider") REFERENCES "providers"("prod_id")
);

CREATE TABLE "dishes_additions" (
    "addition_id" int,
    "dish_id" int,
    PRIMARY KEY("addition_id", "dish_id"),
    FOREIGN KEY("addition_id") REFERENCES "additions"("addition_id"),
    FOREIGN KEY("dish_id") REFERENCES "dishes"("dish_id")
);

CREATE TABLE "payment_methods" (
    "payment_method_id" SERIAL PRIMARY KEY,
    "method" varchar(20) NOT NULL
);

INSERT INTO "payment_methods" ("method") VALUES ('Credit Card'), ('Cash'), ('Online Payment');

CREATE TABLE "order_statuses" (
    "order_status_id" SERIAL PRIMARY KEY,
    "status" varchar(20) NOT NULL
);

INSERT INTO "order_statuses" ("status") VALUES ('PROCESSING'), ('IN DELIVERY'), ('COMPLETED'), ('CANCELED');

CREATE TABLE "deliverers" (
    pesel varchar(11) PRIMARY KEY,
    FOREIGN KEY (pesel) REFERENCES staff(pesel) ON DELETE CASCADE
);

CREATE TABLE "orders" (
    "order_id" SERIAL PRIMARY KEY,
    "payment_method" int NOT NULL,
    "deliverer" varchar(11),
    "order_status" int NOT NULL DEFAULT 1,
    "ordered_at" timestamp NOT NULL DEFAULT NOW(),
    "last_status_update" timestamp NOT NULL DEFAULT NOW(),
    "client_contact" varchar(11) NOT NULL,
    "address" int NOT NULL, 
    "note" text,
    "summary" decimal(6, 2),
    FOREIGN KEY("deliverer") REFERENCES "deliverers"("pesel"),
    FOREIGN KEY("payment_method") REFERENCES "payment_methods"("payment_method_id"),
    FOREIGN KEY("address") REFERENCES "addresses"("address_id"),
    FOREIGN KEY("order_status") REFERENCES "order_statuses"("order_status_id")
);

CREATE TABLE "orders_dishes" (
    "dish_id" int,
    "order_id" int,
    "quantity" int NOT NULL,
    CONSTRAINT "nonnegative_dishes" CHECK("quantity" >= 0),
    PRIMARY KEY("dish_id", "order_id"),
    FOREIGN KEY("dish_id") REFERENCES "dishes"("dish_id"),
    FOREIGN KEY("order_id") REFERENCES "orders"("order_id")
);

CREATE TABLE "orders_additions" (
    "addition_id" int,
    "order_id" int,
    "quantity" int NOT NULL,
    CONSTRAINT "nonnegative_additions" CHECK("quantity" >= 0),
    PRIMARY KEY("addition_id", "order_id"),
    FOREIGN KEY("addition_id") REFERENCES "additions"("addition_id"),
    FOREIGN KEY("order_id") REFERENCES "orders"("order_id")
);

COPY dishes(dish_name, dish_type, price, description) FROM '/docker-entrypoint-initdb.d/data/dishes.csv' DELIMITER ';' CSV HEADER;
COPY addresses(street, locality, post_code, building_num) FROM '/docker-entrypoint-initdb.d/data/addresses.csv' DELIMITER ';' CSV HEADER;
COPY providers(prod_name, contact, address) FROM '/docker-entrypoint-initdb.d/data/providers.csv' DELIMITER ';' CSV HEADER;
COPY components(component_name, prod_id, price, availability) FROM '/docker-entrypoint-initdb.d/data/components.csv' DELIMITER ';' CSV HEADER;
COPY additions(addition_name, provider, price, availability) FROM '/docker-entrypoint-initdb.d/data/additions.csv' DELIMITER ';' CSV HEADER;
COPY dishes_components(dish_id, component_id, quantity) FROM '/docker-entrypoint-initdb.d/data/dishes_components.csv' DELIMITER ';' CSV HEADER;
COPY dishes_additions(addition_id, dish_id) FROM '/docker-entrypoint-initdb.d/data/dishes_additions.csv' DELIMITER ';' CSV HEADER;
COPY staff(pesel, firstname, lastname, position, address, contact, gender, birthday, hire_date) FROM '/docker-entrypoint-initdb.d/data/staff.csv' DELIMITER ',' CSV HEADER;
COPY orders(order_id,payment_method,deliverer,order_status,ordered_at,last_status_update,client_contact,"address",note,summary) FROM '/docker-entrypoint-initdb.d/data/orders.csv' DELIMITER ',' CSV HEADER;
INSERT INTO deliverers(pesel) SELECT pesel FROM staff WHERE position='Deliverer';

CREATE SCHEMA utils;
CREATE SCHEMA tools;
CREATE SCHEMA display;