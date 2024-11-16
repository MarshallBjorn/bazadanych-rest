CREATE TABLE "addresses" (
    "address_id" SERIAL PRIMARY KEY,
    "street" varchar(30) NOT NULL,
    "locality" varchar(30) NOT NULL,
    "post_code" varchar(6) NOT NULL,
    "buildingNum" varchar(4) NOT NULL
);

CREATE TABLE "providers" (
    "prod_id" SERIAL PRIMARY KEY,
    "prod_name" varchar(20) NOT NULL,
    "contact" varchar(11) UNIQUE NOT NULL,
    "address" int UNIQUE NOT NULL,
    FOREIGN KEY ("address") REFERENCES "addresses"("address_id") ON DELETE CASCADE
);

CREATE TABLE "staff" (
    "pesel" varchar(11) PRIMARY KEY,
    "firstname" varchar(10) NOT NULL,
    "lastname" varchar(15) NOT NULL,
    "position" varchar(20) NOT NULL,
    "address" int NOT NULL,
    "contact" varchar(11) UNIQUE NOT NULL,
    "gender" boolean NOT NULL,
    "birthday" date NOT NULL,
    "hire_date" date NOT NULL,
    FOREIGN KEY ("address") REFERENCES "addresses"("address_id") ON DELETE CASCADE
);

CREATE TABLE "components" (
    "component_id" SERIAL PRIMARY KEY,
    "component_name" varchar(20) UNIQUE NOT NULL,
    "price" decimal(6,2) NOT NULL,
    "availability" boolean NOT NULL
);

CREATE TABLE "dishes" (
    "dish_id" SERIAL PRIMARY KEY,
    "dish_name" varchar(20) NOT NULL,
    "dish_type" varchar(20) NOT NULL,
    "price" decimal(6,2) NOT NULL,
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

INSERT INTO "order_statuses" ("status") VALUES ('PENDING'), ('PROCESSING'), ('IN DELIVERY'), ('COMPLETED'), ('CANCELED');

CREATE TABLE "orders" (
    "order_id" SERIAL PRIMARY KEY,
    "payment_method" int NOT NULL,
    "delivery_id" varchar(11) NOT NULL,
    "order_status" int NOT NULL,
    "note" text,
    FOREIGN KEY("delivery_id") REFERENCES "staff"("pesel"),
    FOREIGN KEY("payment_method") REFERENCES "payment_methods"("payment_method_id"),
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