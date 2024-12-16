CREATE OR REPLACE FUNCTION item_exist(p_name varchar, item_type varchar)
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

CREATE OR REPLACE FUNCTION find_item(p_name varchar, item_type varchar)
RETURNS int AS
$$
DECLARE
    id int;
BEGIN
    CASE
        WHEN item_type = 'DISH' THEN
            SELECT dish_id INTO id FROM dishes WHERE p_name = dish_name;
        WHEN item_Type = 'ADDITION' THEN
            SELECT addition_id INTO id FROM additions WHERE p_name = addition_name;
        ELSE
            RAISE EXCEPTION 'Uknown item of type:"%"', item_type;
    END CASE;

    RETURN id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION is_servable(p_dish_name varchar)
RETURNS boolean AS
$$
DECLARE
    dish_status boolean;
BEGIN
    SELECT is_served INTO dish_status FROM dishes WHERE dish_name = p_dish_name;
    RETURN dish_status;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION list_served_dishes()
RETURNS SETOF RECORD AS
$$
DECLARE
    dish_cursor CURSOR FOR SELECT dish_name, dish_type, price, description FROM dishes WHERE is_served = TRUE;
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

CREATE OR REPLACE FUNCTION new_address(p_address jsonb DEFAULT '[]'::jsonb)
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