CREATE OR REPLACE PROCEDURE tools.add_new_dish(
    p_dish_name varchar,
    p_dish_type varchar,
    p_price decimal(6,2),
    p_description text,
    p_components jsonb DEFAULT '[]'::jsonb,  -- List of components with quantity
    p_additions jsonb DEFAULT '[]'::jsonb     -- List of additions
)
LANGUAGE plpgsql
AS
$$
DECLARE
    new_dish_id int;
    component jsonb;
    addition jsonb;
	current_item int;
    quantity int;
	do_exists boolean;
BEGIN
    SELECT utils.item_exist(p_dish_name,'DISH') INTO do_exists;
    -- SELECT EXISTS(SELECT 1 FROM dishes WHERE dish_name = p_dish_name AND dish_type = p_dish_type) INTO do_exists;

    IF do_exists THEN
        RAISE NOTICE 'Dish "%" already exists.', p_dish_name;
        RETURN;
    END IF;

    -- Insert new dish into dishes table
    INSERT INTO dishes (dish_name, dish_type, price, description)
    VALUES (p_dish_name, p_dish_type, p_price, p_description)
    RETURNING dish_id INTO new_dish_id;
    CALL tools.item_soft_toggle(p_dish_name, 'DISH');

    -- Insert components if any are provided
    IF p_components IS NOT NULL AND jsonb_array_length(p_components) > 0 THEN
        -- Loop through each component and insert it into the dishes_components table
        FOR component IN SELECT * FROM jsonb_array_elements(p_components) LOOP
            -- Extract the component_id and quantity from each JSON element
            current_item := (component->>'component_id')::int;
            quantity := (component->>'quantity')::int;

			SELECT EXISTS (SELECT 1 FROM components WHERE components.component_id = current_item) INTO do_exists;

			IF do_exists THEN
            	-- Insert the component into dishes_components table
            	INSERT INTO dishes_components (dish_id, component_id, quantity)
            	VALUES (new_dish_id, current_item, quantity);
			ELSE
				RAISE EXCEPTION 'Component no:% does not exist.', current_item;
				RETURN;
			END IF;
        END LOOP;
    END IF;

    -- Insert additions if any are provided
    IF p_additions IS NOT NULL AND jsonb_array_length(p_additions) > 0 THEN
        -- Loop through each addition and insert it into the dishes_additions table
        FOR addition IN SELECT * FROM jsonb_array_elements(p_additions) LOOP
			current_item := (addition->>'addition_id')::int;

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
    INSERT INTO orders(payment_method, client_contact, "address", note)
    VALUES(current_payment_method, p_client_contact, address_id, p_note)
    RETURNING order_id INTO new_order_id;

	PERFORM setval(pg_get_serial_sequence('orders', 'order_id'), MAX(order_id) + 1) 
    	FROM orders;

    IF p_dishes IS NOT NULL AND jsonb_array_length(p_dishes) > 0 THEN
        FOR dish_record IN SELECT * FROM jsonb_array_elements(p_dishes) LOOP
            -- Extract details from the JSON object
            dish_id := (dish_record->>'id')::int;
            dish_name := dish_record->>'name';
            dish_price := (dish_record->>'price')::numeric;
            dish_quantity := (dish_record->>'quantity')::int;

            -- Check if the dish exists in the system
            IF utils.item_exist(dish_name, 'DISH') THEN
                -- Insert into orders_dishes
                INSERT INTO orders_dishes(dish_id, order_id, quantity)
                VALUES (dish_id, new_order_id, dish_quantity);
            ELSE
                RAISE EXCEPTION 'Dish "%" does not exist.', dish_name;
            END IF;
        END LOOP;
    END IF;

    IF p_additions IS NOT NULL AND jsonb_array_length(p_additions) > 0 THEN
        FOR i IN 0..(jsonb_array_length(p_additions)-1) LOOP
            current_item := p_additions->i->>'addition_name';
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
    -- Check if the provider already exists based on contact
    SELECT EXISTS(SELECT 1 FROM providers WHERE prod_name = p_prod_name) INTO do_exists;

    IF NOT do_exists THEN
        RAISE NOTICE 'Provider with contact "%" already exists.', p_contact;
        RETURN;
    END IF;

    -- Add or retrieve the address ID
    SELECT tools.new_address(p_address) INTO address_id;

    -- Insert the new provider
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
    
    -- Check if the component already exists
    SELECT EXISTS(SELECT 1 FROM components WHERE component_name = p_component_name) INTO do_exists;
    
    IF do_exists THEN
        RAISE NOTICE 'Component "%" already exists.', p_component_name;
        RETURN;
    END IF;

    -- Insert the new component
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
    -- Retrieve the provider ID based on contact
    SELECT prod_id INTO provider_id FROM providers WHERE prod_name = p_provider_name;

    IF provider_id IS NULL THEN
        RAISE EXCEPTION 'Provider with contact "%" not found.', p_provider_contact;
    END IF;

    -- Check if the addition already exists
    SELECT EXISTS(SELECT 1 FROM additions WHERE addition_name = p_addition_name) INTO do_exists;

    IF do_exists THEN
        RAISE NOTICE 'Addition "%" already exists.', p_addition_name;
        RETURN;
    END IF;

    -- Insert the new addition
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
    -- Check if the staff member already exists based on PESEL
    SELECT EXISTS(SELECT 1 FROM staff WHERE pesel = p_pesel) INTO do_exists;

    IF do_exists THEN
        RAISE NOTICE 'Staff member with PESEL "%" already exists.', p_pesel;
        RETURN;
    END IF;

    -- Check if the contact is unique
    SELECT EXISTS(SELECT 1 FROM staff WHERE contact = p_contact) INTO do_exists;

    IF do_exists THEN
        RAISE NOTICE 'Staff member with contact "%" already exists.', p_contact;
        RETURN;
    END IF;

    -- Add or retrieve the address ID
    SELECT tools.new_address(p_address) INTO address_id;

    -- Insert the new staff member
    INSERT INTO staff (pesel, firstname, lastname, position, address, contact, gender, birthday)
    VALUES (p_pesel, p_firstname, p_lastname, p_position, address_id, p_contact, p_gender, p_birthday);

    RAISE NOTICE 'Staff member "%" added successfully.', p_firstname || ' ' || p_lastname;
END;
$$;

/*
CREATE OR REPLACE PROCEDURE tools.remove_dish_hard (
    p_dish_name varchar,
    p_dish_type varchar
)
LANGUAGE plpgsql
AS
$$
DECLARE
    do_exists boolean;
    dish_number int;
BEGIN
    SELECT EXISTS(SELECT 1 FROM dishes WHERE dish_name = p_dish_name AND dish_type = p_dish_type) INTO do_exists;

    IF NOT do_exists THEN
        RAISE EXCEPTION 'Dish "%" of the "%" type, does not exist.', p_dish_name, p_dish_type;
        RETURN;
    END IF;

    SELECT dish_id INTO dish_number
    FROM dishes
    WHERE dish_name = p_dish_name AND dish_type = p_dish_type;

    DELETE FROM dishes_components WHERE dish_number = dish_id;
    DELETE FROM dishes_additions WHERE dish_number = dish_id;
    DELETE FROM dishes WHERE dish_id = dish_number;

    RAISE NOTICE 'Dish "%" if the "%" type deleted successfuly.', p_dish_name, p_dish_type;
END;
$$;
*/

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
    p_status text
) LANGUAGE plpgsql AS $$
DECLARE
    staff_id varchar;
BEGIN
    staff_id := utils.find_item_alt(p_pesel);
    UPDATE staff SET
    firstname = p_new_firstname,
    lastname = p_new_lastname,
    position = p_position,
    contact = p_contact,
    gender = p_gender,
    birthday = p_birthday,
    "status" = p_status
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
    p_address_id := tools.new_address(p_address);

    UPDATE providers SET
    prod_name = p_name,
    contact = p_contact,
    "address" = p_address_id,
    is_partner = p_status
    WHERE p_provider_id = prod_id;
    RAISE NOTICE 'Provider has been updated.';
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
