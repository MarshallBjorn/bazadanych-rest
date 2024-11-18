CREATE OR REPLACE PROCEDURE add_new_dish(
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
    SELECT EXISTS(SELECT 1 FROM dishes WHERE dish_name = p_dish_name AND dish_type = p_dish_type) INTO do_exists;

    IF do_exists THEN
        RAISE NOTICE 'Dish "%s" of type "%s" already exists.', p_dish_name, p_dish_type;
        RETURN;
    END IF;
    -- Insert new dish into dishes table
    INSERT INTO dishes (dish_name, dish_type, price, description)
    VALUES (p_dish_name, p_dish_type, p_price, p_description)
    RETURNING dish_id INTO new_dish_id;

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

CREATE OR REPLACE PROCEDURE remove_dish_hard (
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

CREATE OR REPLACE PROCEDURE dish_soft_toggle (
    p_dish_name varchar,
    p_dish_type varchar
)
LANGUAGE plpgsql
AS
$$
DECLARE
    dish_number int;
    current_status boolean;
BEGIN
    SELECT is_served INTO current_status FROM dishes WHERE dish_name = p_dish_name AND dish_type = p_dish_type;
    IF current_status IS NULL THEN
        RAISE EXCEPTION 'Dish % of the % type, does not exist.', p_dish_name, p_dish_type;
        RETURN;
    END IF;

    UPDATE dishes SET is_served = NOT current_status WHERE dish_name = p_dish_name AND dish_type = p_dish_type;
    RAISE NOTICE 'Dish "%" has been successfuly toggled, its current status: %', p_dish_name, NOT current_status;
END;
$$;