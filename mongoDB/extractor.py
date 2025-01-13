import pandas as pd
import json
import os

base_dir = os.path.dirname(os.path.abspath(__file__))
print(base_dir)
file_path = os.path.join(base_dir, 'data', 'dishes.csv')

df = pd.read_csv(file_path, delimiter=';')

json_data = df.to_dict(orient='records')

with open(os.path.join(base_dir, 'dishes.json'), 'w') as f:
    json.dump(json_data, f, indent=4)