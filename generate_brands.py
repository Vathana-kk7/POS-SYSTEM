import pandas as pd

# បង្កើតទិន្នន័យ 1,000,000 Rows (១ លាន rows)
data = {
    'name': [f'Brand_{i}' for i in range(1, 10001)],
    'status': ['active' if i % 2 == 0 else 'inactive' for i in range(1, 10001)]
}

df = pd.DataFrame(data)

# Save ជា CSV File (លឿន និងស្រាលខ្លាំង)
df.to_csv('brands_100000.csv', index=False)
print(" Success: Generated brands_1000000.csv with 1,000,00 rows!")
