import pandas as pd
import json
import os

base_dir = os.path.dirname(os.path.abspath(__file__))
print(base_dir)
dishes_file = os.path.join(base_dir, 'data', 'dishes.csv')
components_file = os.path.join(base_dir, 'data', 'dishes_components.csv')
additions_file = os.path.join(base_dir, 'data', 'dishes_additions.csv')
components_details_file = os.path.join(base_dir, 'data', 'components.csv')
additions_details_file = os.path.join(base_dir, 'data', 'additions.csv')

dishes_df = pd.read_csv(dishes_file, delimiter=',')
dishes_components_df = pd.read_csv(components_file, delimiter=',')
components_details_df = pd.read_csv(components_details_file, delimiter=',')
dishes_additions_df = pd.read_csv(additions_file, delimiter=',')
additions_details_df = pd.read_csv(additions_details_file, delimiter=',')

dishes_components_merged = pd.merge(
    dishes_components_df,
    components_details_df,
    how='left',
    left_on='component_id',
    right_on='component_id'
)

dishes_additions_merged = pd.merge(
    dishes_additions_df,
    additions_details_df,
    how='left',
    left_on='addition_id',
    right_on='addition_id'
)

nested_data = []
for _, dish in dishes_df.iterrows():
    components = dishes_components_merged[dishes_components_merged['dish_id'] == dish['dish_id']]
    components_list = components.apply(lambda row: {
        "component_id": row['component_id'],
        "component_name": row['component_name'],
        "quantity": row['quantity'],
        "price": row['price'],
        "availability": row['availability']
    }, axis=1).tolist()
    
    additions = dishes_additions_merged[dishes_additions_merged['dish_id'] == dish['dish_id']]
    additions_list = additions.apply(lambda row: {
        "addition_id": row['addition_id'],
        "addition_name": row['addition_name'],
        "price": row['price'],
        "availability": row['availability']
    }, axis=1).tolist()
    
    dish_data = {
        "dish_id": dish['dish_id'],
        "dish_name": dish['dish_name'],
        "dish_type": dish['dish_type'],
        "price": dish['price'],
        "description": dish['description'],
        "is_served": dish['is_served'],
        "components": components_list,
        "additions": additions_list
    }
    nested_data.append(dish_data)

output_file_path = os.path.join(base_dir, 'dishes.json')
with open(output_file_path, 'w') as f:
    json.dump(nested_data, f, indent=4)

print(f"JSON file successfully created at: {output_file_path}")
