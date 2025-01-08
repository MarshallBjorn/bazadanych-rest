CREATE OR REPLACE FUNCTION utils.item_exist(p_name varchar, item_type varchar)
RETURNS BOOLEAN AS
$$
DECLARE
    do_exists boolean;
BEGIN
    CASE
        WHEN item_type = 'DISH' THEN
            SELECT EXISTS(SELECT 1 FROM dishes WHERE p_name = dish_name) INTO do_exists;
        WHEN item_type = 'ADDITION' THEN
            SELECT EXISTS(SELECT 1 FROM additions WHERE p_name = addition_name) INTO do_exists;
        WHEN item_type = 'COMPONENT' THEN
            SELECT EXISTS(SELECT 1 FROM components WHERE p_name = addition_name) INTO do_exists;
        WHEN item_type = 'PROVIDER' THEN
            SELECT EXISTS(SELECT 1 FROM providers WHERE p_name = prod_name) INTO do_exists;
        ELSE
            RAISE EXCEPTION 'Unknown item of type:"%" and name:"%"',item_type,p_name;
            RETURN false;
    END CASE;
    
    IF do_exists THEN
        RETURN true;
    END IF;
    RETURN false;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION utils.find_item(p_name varchar, item_type varchar)
RETURNS int AS
$$
DECLARE
    id int;
BEGIN
    CASE
        WHEN item_type = 'DISH' THEN
            SELECT dish_id INTO id FROM dishes WHERE p_name = dish_name;
        WHEN item_type = 'COMPONENT' THEN
            SELECT component_id INTO id FROM components WHERE p_name = component_name;
        WHEN item_type = 'ADDITION' THEN
            SELECT addition_id INTO id FROM additions WHERE p_name = addition_name;
        WHEN item_type = 'PROVIDER' THEN
            SELECT prod_id INTO id FROM providers WHERE p_name = prod_name;
        ELSE
            RAISE EXCEPTION 'Uknown item of type:"%" and name:"%"', item_type, p_name;
    END CASE;

    IF id IS NULL THEN
        RAISE EXCEPTION 'Item "%" of type "%" has not been found.', p_name, item_type;
    END IF;
    RETURN id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION utils.find_item_alt(p_name varchar)
RETURNS varchar AS
$$
DECLARE
    psl varchar;
BEGIN
    SELECT pesel INTO psl FROM staff WHERE pesel = p_name;

    IF psl IS NULL THEN
        RAISE EXCEPTION 'Unknown item of name:"%"', p_name;
    END IF;
    RETURN psl;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION tools.is_servable(p_dish_name varchar)
RETURNS boolean AS
$$
DECLARE
    dish_status boolean;
BEGIN
    SELECT is_served INTO dish_status FROM dishes WHERE dish_name = p_dish_name;
    RETURN dish_status;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_all_dishes()
RETURNS SETOF RECORD AS
$$
DECLARE
    dish_cursor CURSOR FOR SELECT dish_name, dish_type, price, is_served, description FROM dishes;
    result_record RECORD;
BEGIN
    -- Open the cursor
    OPEN dish_cursor;

    -- Loop through the cursor results
    LOOP
        FETCH dish_cursor INTO result_record;
        EXIT WHEN NOT FOUND;  -- Exit loop when no more rows are found

        -- Return each record
        RETURN NEXT result_record;
    END LOOP;

    -- Close the cursor
    CLOSE dish_cursor;

    RETURN;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_all_additions()
RETURNS SETOF RECORD AS
$$
DECLARE
    addition_cursor CURSOR FOR SELECT addition_name, price, availability FROM additions;
    result_record RECORD;
BEGIN
    OPEN addition_cursor;

    LOOP
        FETCH addition_cursor INTO result_record;
        EXIT WHEN NOT FOUND;

        RETURN NEXT result_record;
    END LOOP;
    
    CLOSE addition_cursor;
    RETURN;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_all_components()
RETURNS SETOF RECORD AS
$$
DECLARE
    component_cursor CURSOR FOR SELECT component_name, provider, price FROM components WHERE availability = TRUE;
    result_record RECORD;
BEGIN
    OPEN component_cursor;

    LOOP
        FETCH component_cursor INTO result_record;
        EXIT WHEN NOT FOUND;

        RETURN NEXT result_record;
    END LOOP;
    
    CLOSE component_cursor;
    RETURN;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_client_orders()
RETURNS TABLE (ord_id int, pay_meth int, deliv varchar, ordr_stat int, ord_at timestamp, last_update timestamp, client varchar, address_number int, cust_note text) AS
$$
BEGIN
    RETURN QUERY
    SELECT order_id, payment_method, deliverer, order_status, ordered_at, last_status_update, client_contact, address, note
    FROM orders;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_staff()
RETURNS TABLE (staff_id varchar, fname varchar, lname varchar, fposition varchar, fcontact varchar, fgender boolean, fbirthday date, fhire_date date, fstatus boolean) AS
$$
BEGIN
    RETURN QUERY
    SELECT pesel, firstname, lastname, position, contact, gender, birthday, hire_date, status
    FROM staff;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION tools.new_address(p_address jsonb DEFAULT '[]'::jsonb)
RETURNS int AS
$$
DECLARE
    address_number int;
    in_street varchar;
    in_locality varchar;
    in_post_code varchar;
    in_building_num varchar;
    do_exists boolean;
BEGIN
    IF jsonb_array_length(p_address) = 4 THEN
        in_street := p_address->>0;
        in_locality := p_address->>1;
        in_post_code := p_address->>2;
        in_building_num := p_address->>3;
    ELSE
        RAISE EXCEPTION 'Adress is incorrect.';
    END IF;

    SELECT address_id INTO address_number FROM addresses
    WHERE street = in_street AND locality = in_locality AND post_code = in_post_code AND building_num = in_building_num; 

    IF address_number IS NULL THEN
        INSERT INTO addresses(street, locality, post_code, building_num)
        VALUES (in_street, in_locality, in_post_code, in_building_num)
        RETURNING address_id INTO address_number;
    END IF;

    RETURN address_number;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION utils.assign_deliverer_to_order(p_order_id int)
RETURNS void AS
$$
DECLARE
    selected_deliverer varchar;
BEGIN
    -- Find an available deliverer with fewer than 3 active orders
    SELECT pesel INTO selected_deliverer
    FROM deliverers
    WHERE (SELECT COUNT(*) FROM orders WHERE deliverer = deliverers.pesel AND order_status = 2) < 3
    LIMIT 1;

    -- If no deliverer is available, raise an exception
    IF selected_deliverer IS NULL THEN
        RAISE EXCEPTION 'No available deliverers to assign to order %', p_order_id;
    END IF;

    -- Update the order to assign the selected deliverer
    UPDATE orders
    SET deliverer = selected_deliverer
    WHERE order_id = p_order_id;

    RAISE NOTICE 'Order % has been assigned to deliverer %', p_order_id, selected_deliverer;
END;
$$
LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION tools.update_order_status(p_order_id int)
RETURNS void AS
$$
DECLARE
    current_status_id int;
    next_status_id int;
    next_status_name varchar;
BEGIN
    -- Get the current status of the order
    SELECT order_status INTO current_status_id
    FROM orders
    WHERE order_id = p_order_id;

    -- If the order does not exist, raise an exception
    IF current_status_id IS NULL THEN
        RAISE EXCEPTION 'Order % does not exist.', p_order_id;
    END IF;

    -- Determine the next status in the cycle
    CASE current_status_id
        WHEN 1 THEN next_status_id := 2;  -- PROCESSING -> IN DELIVERY
        WHEN 2 THEN next_status_id := 3;  -- IN DELIVERY -> DELIVERED
        WHEN 3 THEN
            RAISE EXCEPTION 'DELIVERED is the last possible order status.';  -- DELIVERED -> PROCESSING
        ELSE
            RAISE EXCEPTION 'Invalid current status ID % for order %', current_status_id, p_order_id;
    END CASE;

    -- Get the name of the next status (for debugging)
    SELECT status INTO next_status_name
    FROM order_statuses
    WHERE order_status_id = next_status_id;

    -- Update the order's status
    UPDATE orders
    SET order_status = next_status_id,
        last_status_update = NOW()
    WHERE order_id = p_order_id;

    -- If the new status is "IN DELIVERY", call utils.assign_deliverer_to_order
    IF next_status_name = 'IN DELIVERY' THEN
        PERFORM utils.assign_deliverer_to_order(p_order_id);  -- Call the deliverer assignment function
    END IF;

    -- Confirm the status update
    RAISE NOTICE 'Order % status updated to %', p_order_id, next_status_name;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION auth.login(username_input VARCHAR, password_input VARCHAR)
RETURNS BOOLEAN AS $$
DECLARE
    stored_hash TEXT;
BEGIN
    -- Fetch the hashed password for the given username
    SELECT password_hash INTO stored_hash
    FROM auth.users
    WHERE username = username_input;

    -- If no user found, return FALSE
    IF NOT FOUND THEN
        RETURN FALSE;
    END IF;

    -- Verify the provided password against the stored hash
    IF crypt(password_input, stored_hash) = stored_hash THEN
        RETURN TRUE; -- Login successful
    ELSE
        RETURN FALSE; -- Login failed
    END IF;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION tools.order_sum(p_order_id int)
RETURNS numeric AS $$
DECLARE
    total_addition numeric := 0;
    total_dish numeric := 0;
    total_price numeric := 0;
BEGIN
    SELECT COALESCE(SUM(od.quantity * d.price), 0)
    INTO total_dish
    FROM orders_dishes od
    JOIN dishes d ON od.dish_id = d.dish_id
    WHERE od.order_id = p_order_id;

    SELECT COALESCE(SUM(oa.quantity * a.price), 0)
    INTO total_addition
    FROM orders_additions oa
    JOIN additions a ON oa.addition_id = a.addition_id
    WHERE oa.order_id = p_order_id;

    total_price := total_addition + total_dish;
    RETURN total_price;
END;
$$
LANGUAGE plpgsql