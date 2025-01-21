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
            SELECT EXISTS(SELECT 1 FROM components WHERE p_name = component_name) INTO do_exists;
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

CREATE OR REPLACE FUNCTION utils.parse_address(p_address_id int)
RETURNS text AS $$
DECLARE
    string text;
    p_street text;
    p_loc text;
    p_code text;
    p_num text;
    curs CURSOR FOR SELECT street, locality, post_code, building_num FROM addresses WHERE address_id = p_address_id;
BEGIN
    OPEN curs;
    LOOP
        FETCH curs INTO p_street, p_loc, p_code, p_num;
        EXIT WHEN NOT FOUND;

        string := p_street || ' ' || p_num || ', ' || p_code || ' ' || p_loc;
    END LOOP;
    CLOSE curs;
    RETURN TRIM(BOTH '; ' FROM string);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_all_dishes()
RETURNS SETOF RECORD AS
$$
DECLARE
    dish_cursor CURSOR FOR SELECT dish_id, dish_name, dish_type, price, is_served, description FROM dishes;
    result_record RECORD;
BEGIN
    OPEN dish_cursor;
    LOOP
        FETCH dish_cursor INTO result_record;
        EXIT WHEN NOT FOUND;

        RETURN NEXT result_record;
    END LOOP;

    CLOSE dish_cursor;
    RETURN;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_all_additions()
RETURNS SETOF RECORD AS
$$
DECLARE
    addition_cursor CURSOR FOR 
        SELECT addition_id, addition_name, price, providers.prod_name, "availability" 
        FROM additions
        INNER JOIN providers ON
        providers.prod_id=additions.provider;
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
    component_cursor CURSOR FOR 
        SELECT component_id, component_name, price, prod_name, "availability"
        FROM components
        INNER JOIN providers ON
        providers.prod_id=components.prod_id;
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

CREATE OR REPLACE FUNCTION display.list_order_dishes(p_order INT)
RETURNS SETOF RECORD AS
$$
DECLARE
    curs CURSOR FOR
        SELECT order_id, dish_name, quantity
        FROM orders_dishes
        INNER JOIN dishes
        ON dishes.dish_id = orders_dishes.dish_id
        WHERE order_id = p_order;
    result_record RECORD;
BEGIN
    OPEN curs;
    LOOP
        FETCH curs INTO result_record;
        EXIT WHEN NOT FOUND;
        RETURN NEXT (result_record.order_id, result_record.dish_name::text, result_record.quantity);
    END LOOP;
    CLOSE curs;
    RETURN;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_order_additions(p_order INT)
RETURNS SETOF RECORD AS
$$
DECLARE
    curs CURSOR FOR
        SELECT order_id, addition_name, quantity
        FROM orders_additions
        INNER JOIN additions
        ON additions.addition_id = orders_additions.addition_id
        WHERE order_id = p_order;
    result_record RECORD;
BEGIN
    OPEN curs;
    LOOP
        FETCH curs INTO result_record;
        EXIT WHEN NOT FOUND;
        RETURN NEXT (result_record.order_id, result_record.addition_name::text, result_record.quantity);
    END LOOP;
    CLOSE curs;
    RETURN;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_providers()
RETURNS SETOF RECORD AS
$$
DECLARE
    curs CURSOR FOR
        SELECT prod_id, prod_name, contact, utils.parse_address("address") AS addr, addresses.street, addresses.locality, addresses.post_code, addresses.building_num, is_partner
        FROM providers
        INNER JOIN addresses ON
        addresses.address_id = providers.address;
    result_record RECORD;
BEGIN
    OPEN curs;
    LOOP
        FETCH curs INTO result_record;
        EXIT WHEN NOT FOUND;
        RETURN NEXT (result_record.prod_id, result_record.prod_name, result_record.contact, result_record.addr, result_record.street, result_record.locality, result_record.post_code, result_record.building_num, result_record.is_partner);
    END LOOP;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_all_orders()
RETURNS TABLE (ord_id int, pay_meth varchar, summ numeric, deliv varchar, ordr_stat varchar, ord_at timestamp, last_update timestamp, client varchar, address_string text, cust_note text) AS
$$
BEGIN
    RETURN QUERY
    SELECT 
        order_id AS ord_id, 
        payment_methods.method AS pay_meth, 
        summary AS summ,
        deliverer AS deliv, 
        order_statuses.status AS ordr_stat, 
        ordered_at AS ord_at, 
        last_status_update AS last_update, 
        client_contact AS client, 
        utils.parse_address("address") AS address_string, 
        note AS cust_note
    FROM orders
    INNER JOIN payment_methods
    ON orders.payment_method = payment_methods.payment_method_id
    INNER JOIN order_statuses
    ON orders.order_status = order_statuses.order_status_id;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION display.list_staff()
RETURNS TABLE (staff_id varchar, fname varchar, lname varchar, fposition varchar, fcontact varchar, street varchar, locality varchar, post_code varchar, building_num varchar, faddress text, fgender boolean, fbirthday date, fhire_date date, fstatus text) AS
$$
BEGIN
    RETURN QUERY
    SELECT
        pesel AS staff_id,
        firstname AS fname,
        lastname AS lname,
        position AS fposition,
        contact AS fcontact,
        addresses.street AS street,
        addresses.locality AS locality,
        addresses.post_code AS post_code,
        addresses.building_num AS building_num,
        utils.parse_address("address") AS faddress,
        gender AS fgender,
        birthday AS fbirthday,
        hire_date AS fhire_date,
        "status" AS fstatus
    FROM staff
    INNER JOIN addresses ON
    addresses.address_id = staff.address;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION tools.new_address(p_address jsonb DEFAULT '{}'::jsonb)
RETURNS int AS
$$
DECLARE
    address_number int;
    in_street varchar;
    in_locality varchar;
    in_post_code varchar;
    in_building_num varchar;
BEGIN
    in_street := p_address->>'street';
    in_locality := p_address->>'locality';
    in_post_code := p_address->>'post_code';
    in_building_num := p_address->>'building_num'::varchar;

    IF in_street IS NULL OR in_locality IS NULL OR in_post_code IS NULL OR in_building_num IS NULL THEN
        RAISE EXCEPTION 'Adress is incorrect. Missing required fields.';
    END IF;

    SELECT address_id INTO address_number FROM addresses
    WHERE street = in_street 
      AND locality = in_locality 
      AND post_code = in_post_code 
      AND building_num = in_building_num; 

    IF address_number IS NULL THEN
        INSERT INTO addresses(street, locality, post_code, building_num)
        VALUES (in_street, in_locality, in_post_code, in_building_num)
        RETURNING address_id INTO address_number;
    END IF;

    RETURN address_number;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION utils.new_address_alt(p_address jsonb DEFAULT '{}'::jsonb)
RETURNS int AS
$$
DECLARE
    address_number int;
    in_street varchar;
    in_locality varchar;
    in_post_code varchar;
    in_building_num varchar;
BEGIN
    in_street := p_address->>0;
    in_locality := p_address->>1;
    in_post_code := p_address->>2;
    in_building_num := p_address->>3;

    IF in_street IS NULL OR in_locality IS NULL OR in_post_code IS NULL OR in_building_num IS NULL THEN
        RAISE EXCEPTION 'Adress is incorrect. Missing required fields.';
    END IF;

    SELECT address_id INTO address_number FROM addresses
    WHERE street = in_street 
      AND locality = in_locality 
      AND post_code = in_post_code 
      AND building_num = in_building_num; 

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
    SELECT pesel INTO selected_deliverer
    FROM deliverers
    WHERE (SELECT COUNT(*) FROM orders WHERE deliverer = deliverers.pesel AND order_status = 2) < 3
    LIMIT 1;

    IF selected_deliverer IS NULL THEN
        RAISE EXCEPTION 'No available deliverers to assign to order %', p_order_id;
    END IF;

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
    SELECT order_status INTO current_status_id
    FROM orders
    WHERE order_id = p_order_id;

    IF current_status_id IS NULL THEN
        RAISE EXCEPTION 'Order % does not exist.', p_order_id;
    END IF;

    CASE current_status_id
        WHEN 1 THEN next_status_id := 2;
        WHEN 2 THEN next_status_id := 3;
        WHEN 3 THEN
            RAISE EXCEPTION 'DELIVERED is the last possible order status.';
        ELSE
            RAISE EXCEPTION 'Invalid current status ID % for order %', current_status_id, p_order_id;
    END CASE;

    SELECT status INTO next_status_name
    FROM order_statuses
    WHERE order_status_id = next_status_id;

    UPDATE orders
    SET order_status = next_status_id
    WHERE order_id = p_order_id;

    IF next_status_name = 'IN DELIVERY' THEN
        PERFORM utils.assign_deliverer_to_order(p_order_id);
    END IF;

    RAISE NOTICE 'Order % status updated to %', p_order_id, next_status_name;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION auth.login(username_input VARCHAR, password_input VARCHAR)
RETURNS BOOLEAN AS $$
DECLARE
    stored_hash TEXT;
BEGIN
    SELECT password_hash INTO stored_hash
    FROM auth.users
    WHERE username = username_input;

    IF NOT FOUND THEN
        RETURN FALSE;
    END IF;

    IF crypt(password_input, stored_hash) = stored_hash THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION utils.order_sum(p_order_id int)
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
LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION utils.update_last_status_update()
RETURNS TRIGGER AS $$
BEGIN
    NEW.last_status_update = NOW()::timestamp(0);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER utils.trigger_update_last_status_update
BEFORE UPDATE ON orders
FOR EACH ROW
WHEN (OLD.order_status IS DISTINCT FROM NEW.order_status)
EXECUTE FUNCTION update_last_status_update();