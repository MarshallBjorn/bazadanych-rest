CREATE OR REPLACE PROCEDURE tools.add_new_dish(
    p_dish_name varchar,
    p_dish_type varchar,
    p_price decimal(6,2),
    p_description text,
    p_components jsonb DEFAULT '[]'::jsonb, 
    p_additions jsonb DEFAULT '[]'::jsonb
)
LANGUAGE plpgsql
AS
$$
DECLARE
    new_dish_id int;
    component jsonb;
    addition jsonb;
	current_item varchar;
    quantity int;
	do_exists boolean;
BEGIN
    SELECT utils.item_exist(p_dish_name,'DISH') INTO do_exists;

    IF do_exists THEN
        RAISE NOTICE 'Dish "%" already exists.', p_dish_name;
        RETURN;
    END IF;

    INSERT INTO dishes (dish_name, dish_type, price, description)
    VALUES (p_dish_name, p_dish_type, p_price, p_description)
    RETURNING dish_id INTO new_dish_id;
    CALL tools.item_soft_toggle(p_dish_name, 'DISH');

    IF p_components IS NOT NULL AND jsonb_array_length(p_components) > 0 THEN
        FOR component IN SELECT * FROM jsonb_array_elements(p_components) LOOP
            current_item := component->>'name';
            quantity := (component->>'quantity')::int;

            IF utils.item_exist(current_item,'COMPONENT') THEN
                INSERT INTO dishes_components(dish_id, component_id, quantity)
                VALUES (new_dish_id, utils.find_item(current_item, 'COMPONENT'), quantity);
            ELSE
                RAISE EXCEPTION 'Component "%" do not exists.', current_item;
                RETURN;
            END IF;
        END LOOP;
    END IF;

    IF p_additions IS NOT NULL AND jsonb_array_length(p_additions) > 0 THEN
        FOR addition IN SELECT * FROM jsonb_array_elements(p_additions) LOOP
			current_item := (addition->>'id')::int;

			SELECT EXISTS(SELECT 1 FROM additions WHERE addition_id = current_item) INTO do_exists;

			IF do_exists THEN 
            	INSERT INTO dishes_additions (dish_id, addition_id)
            	VALUES (new_dish_id, current_item);
			ELSE
				RAISE EXCEPTION 'Addition no:% does not exist.', current_item;
				RETURN;
			END IF;
        END LOOP;
    END IF;
END;
$$;

CREATE OR REPLACE PROCEDURE tools.create_new_order (
    p_payment_method_name varchar,
    p_client_contact varchar,
    p_note text,
    p_address jsonb DEFAULT '[]'::jsonb,
    p_dishes jsonb DEFAULT '[]'::jsonb,
    p_additions jsonb DEFAULT '[]'::jsonb
)
LANGUAGE plpgsql
AS
$$
DECLARE
    current_payment_method int;
    address_id int;
    new_order_id int;
    current_item varchar;
    quantity int;
    summ numeric;
    do_exists boolean;
BEGIN
    SELECT payment_method_id INTO current_payment_method FROM payment_methods WHERE p_payment_method_name = method;

    IF current_payment_method IS NULL THEN
        RAISE EXCEPTION 'Payment method "%" not found.', current_payment_method;
    END IF;

    SELECT tools.new_address(p_address) INTO address_id;
    INSERT INTO orders (payment_method, client_contact, "address", note)
    VALUES(current_payment_method, p_client_contact, address_id, p_note)
    RETURNING order_id INTO new_order_id;

    IF p_dishes IS NOT NULL AND jsonb_array_length(p_dishes) > 0 THEN
        FOR i IN 0..(jsonb_array_length(p_dishes)-1) LOOP
            current_item := p_dishes->i->>'name';
            quantity := (p_dishes->i->>'quantity')::int;

            IF utils.item_exist(current_item,'DISH') THEN
                INSERT INTO orders_dishes(dish_id, order_id, quantity)
                VALUES (utils.find_item(current_item, 'DISH'), new_order_id, quantity);
            ELSE
                RAISE EXCEPTION 'Dish "%" do not exists.', current_item;
                RETURN;
            END IF;
        END LOOP;
    END IF;

    IF p_additions IS NOT NULL AND jsonb_array_length(p_additions) > 0 THEN
        FOR i IN 0..(jsonb_array_length(p_additions)-1) LOOP
            current_item := p_additions->i->>'name';
            quantity := (p_additions->i->>'quantity')::int;

            IF utils.item_exist(current_item,'ADDITION') THEN
                INSERT INTO orders_additions(addition_id, order_id, quantity)
                VALUES (utils.find_item(current_item, 'ADDITION'), new_order_id, quantity);
            ELSE
                RAISE EXCEPTION 'Addition "%" does not exist.', current_item;
                RETURN;
            END IF;
        END LOOP;
    END IF;

    summ := utils.order_sum(new_order_id);
    UPDATE orders SET summary = summ WHERE new_order_id = order_id;
END;
$$;

CREATE OR REPLACE PROCEDURE tools.add_provider(
    p_prod_name varchar,
    p_contact varchar,
    p_address jsonb
)
LANGUAGE plpgsql
AS
$$
DECLARE
    address_id int;
    do_exists boolean;
BEGIN
    SELECT EXISTS(SELECT 1 FROM providers WHERE prod_name = p_prod_name) INTO do_exists;

    IF NOT do_exists THEN
        RAISE NOTICE 'Provider with contact "%" already exists.', p_contact;
        RETURN;
    END IF;

    SELECT tools.new_address(p_address) INTO address_id;

    INSERT INTO providers (prod_name, contact, address)
    VALUES (p_prod_name, p_contact, address_id);

    RAISE NOTICE 'Provider "%" added successfully.', p_prod_name;
END;
$$;

CREATE OR REPLACE PROCEDURE tools.add_component(
    p_component_name varchar,
    p_provider_name varchar,
    p_price decimal(6,2),
    p_availability boolean
)
LANGUAGE plpgsql
AS
$$
DECLARE
    do_exists boolean;
    current_prod_id int;
BEGIN
    IF utils.item_exist(p_provider_name, 'PROVIDER') IS NULL THEN
        RAISE EXCEPTION 'Provider % does not exist', p_provider_name;
    END IF;

    current_prod_id := utils.find_item(p_provider_name, 'PROVIDER');
    
    SELECT EXISTS(SELECT 1 FROM components WHERE component_name = p_component_name) INTO do_exists;
    
    IF do_exists THEN
        RAISE NOTICE 'Component "%" already exists.', p_component_name;
        RETURN;
    END IF;

    INSERT INTO components (component_name, prod_id, price, availability)
    VALUES (p_component_name, current_prod_id, p_price, p_availability);

    RAISE NOTICE 'Component "%" added successfully.', p_component_name;
END;
$$;

CREATE OR REPLACE PROCEDURE tools.add_addition(
    p_addition_name varchar,
    p_provider_name varchar,
    p_price decimal(6,2),
    p_availability boolean
)
LANGUAGE plpgsql
AS
$$
DECLARE
    provider_id int;
    do_exists boolean;
BEGIN
    SELECT prod_id INTO provider_id FROM providers WHERE prod_name = p_provider_name;

    IF provider_id IS NULL THEN
        RAISE EXCEPTION 'Provider with contact "%" not found.', p_provider_contact;
    END IF;

    SELECT EXISTS(SELECT 1 FROM additions WHERE addition_name = p_addition_name) INTO do_exists;

    IF do_exists THEN
        RAISE NOTICE 'Addition "%" already exists.', p_addition_name;
        RETURN;
    END IF;

    INSERT INTO additions (addition_name, provider, price, availability)
    VALUES (p_addition_name, provider_id, p_price, p_availability);

    RAISE NOTICE 'Addition "%" added successfully.', p_addition_name;
END;
$$;

CREATE OR REPLACE PROCEDURE tools.add_staff(
    p_pesel varchar,
    p_firstname varchar,
    p_lastname varchar,
    p_position varchar,
    p_address jsonb,
    p_contact varchar,
    p_gender boolean,
    p_birthday date
)
LANGUAGE plpgsql
AS
$$
DECLARE
    address_id int;
    do_exists boolean;
BEGIN
    SELECT EXISTS(SELECT 1 FROM staff WHERE pesel = p_pesel) INTO do_exists;

    IF do_exists THEN
        RAISE NOTICE 'Staff member with PESEL "%" already exists.', p_pesel;
        RETURN;
    END IF;

    SELECT EXISTS(SELECT 1 FROM staff WHERE contact = p_contact) INTO do_exists;

    IF do_exists THEN
        RAISE NOTICE 'Staff member with contact "%" already exists.', p_contact;
        RETURN;
    END IF;

    SELECT utils.new_address_alt(p_address) INTO address_id;

    INSERT INTO staff (pesel, firstname, lastname, position, address, contact, gender, birthday)
    VALUES (p_pesel, p_firstname, p_lastname, p_position, address_id, p_contact, p_gender, p_birthday);

    RAISE NOTICE 'Staff member "%" added successfully.', p_firstname || ' ' || p_lastname;
END;
$$;

CREATE OR REPLACE PROCEDURE tools.item_soft_toggle (
    p_name varchar,
    p_type varchar
)
LANGUAGE plpgsql
AS
$$
DECLARE
    item_number int;
    current_status boolean;
BEGIN
    CASE
        WHEN p_type = 'DISH' THEN
            item_number := utils.find_item(p_name, p_type);
            UPDATE dishes SET is_served = NOT is_served WHERE dish_id = item_number;
        WHEN p_type = 'COMPONENT' THEN
            item_number := utils.find_item(p_name, p_type);
            UPDATE components SET availability = NOT availability WHERE component_id = item_number;
        WHEN p_type = 'ADDITION' THEN
            item_number := utils.find_item(p_name, p_type);
            UPDATE additions SET availability = NOT availability WHERE addition_id = item_number;
        WHEN p_type = 'PROVIDER' THEN
            item_number := utils.find_item(p_name, p_type);
            UPDATE providers SET is_partner = NOT is_partner WHERE provider_id = item_number;
        ELSE
            RAISE EXCEPTION 'Unknown type: %', p_type;
    END CASE;
    RAISE NOTICE 'Item % of the % type has been successfuly toggled.', p_name, p_type;
END;
$$;

CREATE OR REPLACE PROCEDURE tools.update_dish(
    p_dish_id INT,
    p_new_name varchar,
    p_type varchar,
    p_price numeric,
    p_description text,
    p_is_served boolean
) LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE dishes SET 
    dish_name = p_new_name,
    dish_type = p_type,
    price = p_price,
    "description" = p_description,
    is_served = p_is_served
    WHERE dish_id = p_dish_id;
    
    RAISE NOTICE 'Item has been updated.';
END;
$$;

CREATE OR REPLACE PROCEDURE tools.update_staff(
    p_pesel varchar,
    p_new_firstname varchar,
    p_new_lastname varchar,
    p_position varchar,
    p_contact varchar,
    p_gender boolean,
    p_birthday date,
    p_status text,
    p_address jsonb
) LANGUAGE plpgsql AS $$
DECLARE
    address_number int;
    staff_id varchar;
BEGIN
    staff_id := utils.find_item_alt(p_pesel);
    address_number := utils.new_address_alt(p_address);
    UPDATE staff SET
    firstname = p_new_firstname,
    lastname = p_new_lastname,
    position = p_position,
    contact = p_contact,
    gender = p_gender,
    birthday = p_birthday,
    "status" = p_status,
    "address" = address_number
    WHERE pesel = staff_id;
    RAISE NOTICE 'Person info has been updated.';
END;
$$;

CREATE OR REPLACE PROCEDURE tools.update_addition(
    p_addition_id int,
    p_name varchar,
    p_price numeric,
    p_provider_name varchar,
    p_status boolean
)
LANGUAGE plpgsql AS $$
DECLARE
    p_provider_id int;
BEGIN
    p_provider_id := utils.find_item(p_provider_name, 'PROVIDER');

    UPDATE additions SET
    addition_name = p_name,
    price = p_price,
    "provider" = p_provider_id,
    "availability" = p_status
    WHERE addition_id = p_addition_id;
    RAISE NOTICE 'Addition has been updated.';
END;
$$;

CREATE OR REPLACE PROCEDURE tools.update_component(
    p_component_id int,
    p_name varchar,
    p_price numeric,
    p_provider_name varchar,
    p_status boolean
)
LANGUAGE plpgsql AS $$
DECLARE
    p_provider_id int;
BEGIN
    p_provider_id := utils.find_item(p_provider_name, 'PROVIDER');

    UPDATE components SET
    component_name = p_name,
    price = p_price,
    prod_id = p_provider_id,
    "availability" = p_status
    WHERE component_id = p_component_id;
    RAISE NOTICE 'Component has been updated.';
END;
$$;

CREATE OR REPLACE PROCEDURE tools.update_provider(
    p_provider_id int,
    p_name varchar,
    p_contact varchar,
    p_address jsonb,
    p_status boolean
)
LANGUAGE plpgsql AS $$
DECLARE
    p_address_id int;
BEGIN 
    p_address_id := utils.new_address_alt(p_address);

    UPDATE providers SET
    prod_name = p_name,
    contact = p_contact,
    "address" = p_address_id,
    is_partner = p_status
    WHERE p_provider_id = prod_id;
    RAISE NOTICE 'Provider has been updated.';
END;
$$;

CREATE OR REPLACE PROCEDURE tools.update_dishes_components(dish_id_input INT, components_json JSONB)
LANGUAGE plpgsql
AS $$
BEGIN
    DELETE FROM dishes_components
    WHERE dish_id = dish_id_input;

    INSERT INTO dishes_components (dish_id, component_id, quantity)
    SELECT 
        dish_id_input AS dish_id,
        (component->>'component_id')::INT AS component_id,
        (component->>'quantity')::INT AS quantity
    FROM 
        JSONB_ARRAY_ELEMENTS(components_json) AS component;

    IF NOT EXISTS (SELECT 1 FROM dishes WHERE dish_id = dish_id_input) THEN
        RAISE EXCEPTION 'Dish with id % does not exist', dish_id_input;
    END IF;

    IF EXISTS (SELECT 1 FROM dishes_components WHERE quantity < 0) THEN
        RAISE EXCEPTION 'Negative quantities are not allowed';
    END IF;
END;
$$;

CREATE OR REPLACE PROCEDURE tools.cancel_order(
    p_order_id int
)
LANGUAGE plpgsql AS $$
BEGIN
    UPDATE orders
    SET order_status = 4
    WHERE p_order_id = order_id;
    RAISE NOTICE 'Order % canceled', p_order_id;
END;
$$;
